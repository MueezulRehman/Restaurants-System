<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FittingRoomSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FittingRoomController extends Controller
{
    private function restaurantId(): int
    {
        $id = auth()->user()?->effectiveRestaurantId();
        abort_unless($id, 403);
        return (int) $id;
    }

    public function index()
    {
        $id = $this->restaurantId();
        $sessions = FittingRoomSession::with(['customer', 'staff', 'items.item'])->where('restaurant_id', $id)->latest()->paginate(20);
        $customers = Customer::where('restaurant_id', $id)->orderBy('name')->get();
        $staff = User::where('restaurant_id', $id)->whereIn('role', ['admin', 'manager', 'staff'])->where('is_active', true)->orderBy('name')->get();
        return view('manager.fitting-room.index', compact('sessions', 'customers', 'staff'));
    }

    public function store(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn($q) => $q->where('restaurant_id', $id))], 'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn($q) => $q->where('restaurant_id', $id))], 'room_label' => 'nullable|string|max:80', 'notes' => 'nullable|string|max:1000']);
        FittingRoomSession::create([...$data, 'restaurant_id' => $id, 'started_at' => now()]);
        return back()->with('success', 'Fitting-room session opened.');
    }

    public function updateStatus(Request $request, FittingRoomSession $session)
    {
        abort_unless($session->restaurant_id === $this->restaurantId(), 404);
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'checkout', 'closed', 'cancelled'])]]);
        $session->update([...$data, 'ended_at' => in_array($data['status'], ['closed', 'cancelled'], true) ? now() : null]);
        return back()->with('success', 'Fitting-room session updated.');
    }
}
