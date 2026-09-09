<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use App\Models\MenuItemSize;
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
    public function index(string $item)
    {
        $item = $this->resolveMenuItem($item);
        $variants = $item->variants()->orderBy('sort_order')->paginate(20);
        $sizes = $item->sizes()->get();
        return view('admin.variants.index', compact('item', 'variants', 'sizes'));
    }

    /**
     * Show create variant form.
     */
    public function create(string $item)
    {
        $item = $this->resolveMenuItem($item);
        $attributes = $item->variantAttributes()->orderBy('sort_order')->get();
        return view('admin.variants.create', compact('item', 'attributes'));
    }

    /**
     * Store new variant.
     */
    public function store(Request $request, string $item)
    {
        $item = $this->resolveMenuItem($item);
        $validated = $request->validate([
            'sku' => 'required|string|max:100|unique:product_variants,sku',
            'variant_name' => 'required|string|max:255',
            'price_override' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
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

        return redirect()->route('manager.menu-items.variants.index', $item)
            ->with('success', 'Variant created successfully.');
    }

    /**
     * Show edit variant form.
     */
    public function edit(string $item, string $variant)
    {
        $item = $this->resolveMenuItem($item);
        $variant = $this->resolveVariant($item, $variant);
        $attributes = $item->variantAttributes()->orderBy('sort_order')->get();
        $variantValues = $variant->attributeValues()->get();
        return view('admin.variants.edit', compact('item', 'variant', 'attributes', 'variantValues'));
    }

    /**
     * Update variant.
     */
    public function update(Request $request, string $item, string $variant)
    {
        $item = $this->resolveMenuItem($item);
        $variant = $this->resolveVariant($item, $variant);
        $validated = $request->validate([
            'sku' => 'required|string|max:100|unique:product_variants,sku,' . $variant->id,
            'variant_name' => 'required|string|max:255',
            'price_override' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
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

        return redirect()->route('manager.menu-items.variants.index', $item)
            ->with('success', 'Variant updated successfully.');
    }

    /**
     * Delete variant.
     */
    public function destroy(string $item, string $variant)
    {
        $item = $this->resolveMenuItem($item);
        $variant = $this->resolveVariant($item, $variant);
        $variant->delete();
        return redirect()->route('manager.menu-items.variants.index', $item)
            ->with('success', 'Variant deleted successfully.');
    }

    public function updateSize(Request $request, string $item, string $size)
    {
        $item = $this->resolveMenuItem($item);
        $size = $this->resolveSize($item, $size);

        $validated = $request->validate([
            'size_label' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
        ]);

        $size->update($validated);

        return redirect()->route('manager.menu-items.variants.index', $item)
            ->with('success', 'Size option updated successfully.');
    }

    public function destroySize(string $item, string $size)
    {
        $item = $this->resolveMenuItem($item);
        $size = $this->resolveSize($item, $size);
        $size->delete();

        return redirect()->route('manager.menu-items.variants.index', $item)
            ->with('success', 'Size option deleted successfully.');
    }

    private function resolveMenuItem(string $item): MenuItem
    {
        return MenuItem::query()->findOrFail($item);
    }

    private function resolveVariant(MenuItem $item, string $variant): ProductVariant
    {
        return $item->variants()->findOrFail($variant);
    }

    private function resolveSize(MenuItem $item, string $size): MenuItemSize
    {
        return $item->sizes()->findOrFail($size);
    }
}
