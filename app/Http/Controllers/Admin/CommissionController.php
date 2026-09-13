<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionEarning;
use App\Models\CommissionRule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommissionController extends Controller
{
    private function restaurantId(): int
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $restaurantId = $user->effectiveRestaurantId();
        abort_unless($restaurantId !== null, 403);
        return (int) $restaurantId;
    }

    public function index()
    {
        $restaurantId = $this->restaurantId();
        $staff = User::where('restaurant_id', $restaurantId)->whereIn('role', ['admin', 'manager', 'staff'])->where('is_active', true)->orderBy('name')->get();
        $rules = CommissionRule::with('staff')->where('restaurant_id', $restaurantId)->get();
        $earnings = CommissionEarning::with(['staff', 'appointment.customer'])->where('restaurant_id', $restaurantId)->latest()->paginate(20);
        return view('manager.commissions.index', compact('staff', 'rules', 'earnings'));
    }

    public function storeRule(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $validated = $request->validate([
            'staff_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId))],
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
        ]);
        if ($validated['type'] === 'percent' && (float) $validated['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percent cannot exceed 100.']);
        }
        CommissionRule::updateOrCreate(
            ['restaurant_id' => $restaurantId, 'staff_id' => $validated['staff_id']],
            ['type' => $validated['type'], 'value' => $validated['value'], 'is_active' => true]
        );
        return back()->with('success', 'Commission rule saved.');
    }

    public function pay(CommissionEarning $earning)
    {
        abort_unless($earning->restaurant_id === $this->restaurantId(), 404);
        $earning->update(['status' => 'paid', 'paid_at' => now()]);
        return back()->with('success', 'Commission marked as paid.');
    }
}
