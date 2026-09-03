<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = Auth::user()->restaurant_id;

        $query = Delivery::whereHas('order', function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        })->with(['order', 'rider'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deliveries = $query->paginate(15)->withQueryString();
        $riders = User::where('restaurant_id', $restaurantId)
            ->whereIn('role', ['admin', 'manager'])
            ->orderBy('name')
            ->get();

        return view('admin.deliveries.index', compact('deliveries', 'riders'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $this->authorizeRestaurant($delivery);

        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,on_the_way,delivered',
            'rider_id' => 'nullable|exists:users,id',
            'delivery_notes' => 'nullable|string|max:1000',
        ]);

        $delivery->fill($validated);

        if ($validated['status'] === 'delivered' && ! $delivery->delivered_at) {
            $delivery->delivered_at = Carbon::now();
        }

        if ($validated['status'] !== 'delivered') {
            $delivery->delivered_at = null;
        }

        $delivery->save();

        if ($delivery->order) {
            $orderStatus = $validated['status'] === 'delivered' ? 'delivered' : 'out_for_delivery';
            $delivery->order->update([
                'status' => $orderStatus,
            ]);

            try {
                broadcast(new OrderStatusUpdated($delivery->order->fresh()))->toOthers();
            } catch (\Throwable $e) {
                logger()->warning('Broadcast failed for delivery status update: ' . $e->getMessage());
            }
        }

        return redirect()->route('manager.deliveries.index')
            ->with('success', 'Delivery status updated successfully.');
    }

    protected function authorizeRestaurant(Delivery $delivery): void
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin' && $delivery->order?->restaurant_id !== $user->restaurant_id) {
            abort(403, 'You do not have access to this delivery.');
        }
    }
}
