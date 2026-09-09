<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemPromotion;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Tenancy;

/**
 * Manager: create per-item sales (percent or fixed Rs).
 *
 * @author Mueez Ul Rehman
 */
class ItemSaleController extends Controller
{
    public function index()
    {
        $restaurant = $this->configureTenant();

        $promotions = ItemPromotion::with('menuItem')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.item-sales.index', compact('promotions', 'restaurant'));
    }

    public function create()
    {
        $restaurant = $this->configureTenant();

        $items = MenuItem::orderBy('name')->get(['id', 'name', 'price']);

        return view('admin.item-sales.create', compact('items', 'restaurant'));
    }

    public function store(Request $request)
    {
        $restaurant = $this->configureTenant();

        $validated = $request->validate([
            'menu_item_ids' => 'required|array|min:1',
            'menu_item_ids.*' => 'integer|exists:menu_items,id',
            'label' => 'nullable|string|max:100',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validated['type'] === 'percent' && (float) $validated['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percent cannot exceed 100.']);
        }

        $active = $request->boolean('is_active', true);

        foreach ($validated['menu_item_ids'] as $itemId) {
            ItemPromotion::create([
                'restaurant_id' => $restaurant->id,
                'menu_item_id' => $itemId,
                'label' => $validated['label'] ?? 'Sale',
                'type' => $validated['type'],
                'value' => $validated['value'],
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'is_active' => $active,
            ]);
        }

        return redirect()->route('manager.item-sales.index')
            ->with('success', 'Sale applied to selected items.');
    }

    public function edit(string $item_sale)
    {
        $this->configureTenant();
        $item_sale = $this->resolvePromotion($item_sale);
        $items = MenuItem::orderBy('name')->get(['id', 'name', 'price']);

        return view('admin.item-sales.edit', [
            'promotion' => $item_sale,
            'items' => $items,
        ]);
    }

    public function update(Request $request, string $item_sale)
    {
        $this->configureTenant();
        $item_sale = $this->resolvePromotion($item_sale);
        $validated = $request->validate([
            'menu_item_id' => 'required|integer|exists:menu_items,id',
            'label' => 'nullable|string|max:100',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validated['type'] === 'percent' && (float) $validated['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percent cannot exceed 100.']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $item_sale->update($validated);

        return redirect()->route('manager.item-sales.index')
            ->with('success', 'Sale updated.');
    }

    public function destroy(string $item_sale)
    {
        $this->configureTenant();
        $item_sale = $this->resolvePromotion($item_sale);
        $item_sale->delete();

        return back()->with('success', 'Sale removed.');
    }

    private function configureTenant()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403, 'No restaurant is linked to this account.');
        Tenancy::configureTenantConnection($restaurant);

        return $restaurant;
    }

    private function resolvePromotion(string $promotion): ItemPromotion
    {
        return ItemPromotion::where('restaurant_id', Auth::user()->effectiveRestaurantId())
            ->findOrFail($promotion);
    }
}
