<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use App\Models\ProductVariant;
use App\Models\VariantAttribute;
use App\Models\VariantAttributeValue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Show variants for a specific menu item.
     */
    public function index(MenuItem $item)
    {
        $variants = $item->variants()->orderBy('sort_order')->paginate(20);
        return view('admin.variants.index', compact('item', 'variants'));
    }

    /**
     * Show create variant form.
     */
    public function create(MenuItem $item)
    {
        $attributes = $item->variantAttributes()->orderBy('sort_order')->get();
        return view('admin.variants.create', compact('item', 'attributes'));
    }

    /**
     * Store new variant.
     */
    public function store(Request $request, MenuItem $item)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100|unique:product_variants,sku',
            'variant_name' => 'required|string|max:255',
            'price_override' => 'nullable|numeric|min:0',
            'quantity_available' => 'nullable|integer|min:0',
            'is_available' => 'boolean',
            'attribute_values' => 'nullable|array',
        ]);

        $validated['restaurant_id'] = auth()->user()->restaurant_id;
        $validated['menu_item_id'] = $item->id;

        $variant = ProductVariant::create($validated);

        // Store attribute values if provided
        if (!empty($request->input('attribute_values'))) {
            foreach ($request->input('attribute_values') as $attributeId => $value) {
                VariantAttributeValue::create([
                    'variant_attribute_id' => $attributeId,
                    'product_variant_id' => $variant->id,
                    'value' => $value,
                ]);
            }
        }

        return redirect()->route('admin.menu-items.variants.index', $item)
            ->with('success', 'Variant created successfully.');
    }

    /**
     * Show edit variant form.
     */
    public function edit(MenuItem $item, ProductVariant $variant)
    {
        $attributes = $item->variantAttributes()->orderBy('sort_order')->get();
        $variantValues = $variant->attributeValues()->get();
        return view('admin.variants.edit', compact('item', 'variant', 'attributes', 'variantValues'));
    }

    /**
     * Update variant.
     */
    public function update(Request $request, MenuItem $item, ProductVariant $variant)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100|unique:product_variants,sku,' . $variant->id,
            'variant_name' => 'required|string|max:255',
            'price_override' => 'nullable|numeric|min:0',
            'quantity_available' => 'nullable|integer|min:0',
            'is_available' => 'boolean',
            'attribute_values' => 'nullable|array',
        ]);

        $variant->update($validated);

        // Update attribute values
        if (!empty($request->input('attribute_values'))) {
            $variant->attributeValues()->delete();
            foreach ($request->input('attribute_values') as $attributeId => $value) {
                VariantAttributeValue::create([
                    'variant_attribute_id' => $attributeId,
                    'product_variant_id' => $variant->id,
                    'value' => $value,
                ]);
            }
        }

        return redirect()->route('admin.menu-items.variants.index', $item)
            ->with('success', 'Variant updated successfully.');
    }

    /**
     * Delete variant.
     */
    public function destroy(MenuItem $item, ProductVariant $variant)
    {
        $variant->delete();
        return redirect()->route('admin.menu-items.variants.index', $item)
            ->with('success', 'Variant deleted successfully.');
    }
}
