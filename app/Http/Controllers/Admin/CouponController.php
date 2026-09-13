<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CouponController extends Controller
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
        $coupons = Coupon::where('restaurant_id', $this->restaurantId())->latest()->paginate(20);
        return view('manager.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('coupons', 'code')->where(fn($q) => $q->where('restaurant_id', $restaurantId))],
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'minimum_order' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
        ]);
        if ($validated['type'] === 'percent' && (float) $validated['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percent cannot exceed 100.']);
        }
        Coupon::create([...$validated, 'restaurant_id' => $restaurantId, 'code' => strtoupper($validated['code'])]);
        return back()->with('success', 'Coupon created.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        abort_unless($coupon->restaurant_id === $this->restaurantId(), 404);
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('coupons', 'code')->ignore($coupon->id)->where(fn($q) => $q->where('restaurant_id', $coupon->restaurant_id))],
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'minimum_order' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        if ($validated['type'] === 'percent' && (float) $validated['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percent cannot exceed 100.']);
        }
        $validated['code'] = strtoupper($validated['code']);
        $coupon->update($validated);
        return back()->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon)
    {
        abort_unless($coupon->restaurant_id === $this->restaurantId(), 404);
        $coupon->delete();
        return back()->with('success', 'Coupon removed.');
    }
}
