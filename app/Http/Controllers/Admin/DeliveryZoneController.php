<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeliveryZoneController extends Controller
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
        $zones = DeliveryZone::where('restaurant_id', $this->restaurantId())->latest()->paginate(20);

        return view('manager.delivery-zones.index', compact('zones'));
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('delivery_zones', 'name')->where(fn($q) => $q->where('restaurant_id', $restaurantId))],
            'area_pattern' => 'required|string|max:180',
            'fee' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
        ]);
        DeliveryZone::create([...$validated, 'restaurant_id' => $restaurantId, 'is_active' => true]);

        return back()->with('success', 'Delivery zone created.');
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        abort_unless($deliveryZone->restaurant_id === $this->restaurantId(), 404);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('delivery_zones', 'name')->ignore($deliveryZone->id)->where(fn($q) => $q->where('restaurant_id', $deliveryZone->restaurant_id))],
            'area_pattern' => 'required|string|max:180',
            'fee' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $deliveryZone->update($validated);

        return back()->with('success', 'Delivery zone updated.');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        abort_unless($deliveryZone->restaurant_id === $this->restaurantId(), 404);
        $deliveryZone->delete();

        return back()->with('success', 'Delivery zone removed.');
    }
}
