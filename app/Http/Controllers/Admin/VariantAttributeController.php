<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use App\Models\VariantAttribute;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VariantAttributeController extends Controller
{
    /**
     * Show attributes for a menu item.
     */
    public function index(string $item)
    {
        $item = $this->resolveMenuItem($item);
        $attributes = $item->variantAttributes()->orderBy('sort_order')->paginate(20);
        $sizes = $item->sizes()->get();
        return view('admin.variant-attributes.index', compact('item', 'attributes', 'sizes'));
    }

    /**
     * Show create attribute form.
     */
    public function create(string $item)
    {
        $item = $this->resolveMenuItem($item);
        return view('admin.variant-attributes.create', compact('item'));
    }

    /**
     * Store new attribute.
     */
    public function store(Request $request, string $item)
    {
        $item = $this->resolveMenuItem($item);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $validated['restaurant_id'] = auth()->user()->restaurant_id;
        $validated['menu_item_id'] = $item->id;

        VariantAttribute::create($validated);

        return redirect()->route('manager.menu-items.attributes.index', $item)
            ->with('success', 'Attribute created successfully.');
    }

    /**
     * Show edit attribute form.
     */
    public function edit(string $item, string $attribute)
    {
        $item = $this->resolveMenuItem($item);
        $attribute = $this->resolveAttribute($item, $attribute);
        return view('admin.variant-attributes.edit', compact('item', 'attribute'));
    }

    /**
     * Update attribute.
     */
    public function update(Request $request, string $item, string $attribute)
    {
        $item = $this->resolveMenuItem($item);
        $attribute = $this->resolveAttribute($item, $attribute);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $attribute->update($validated);

        return redirect()->route('manager.menu-items.attributes.index', $item)
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Delete attribute.
     */
    public function destroy(string $item, string $attribute)
    {
        $item = $this->resolveMenuItem($item);
        $attribute = $this->resolveAttribute($item, $attribute);
        $attribute->delete();
        return redirect()->route('manager.menu-items.attributes.index', $item)
            ->with('success', 'Attribute deleted successfully.');
    }

    private function resolveMenuItem(string $item): MenuItem
    {
        return MenuItem::query()->findOrFail($item);
    }

    private function resolveAttribute(MenuItem $item, string $attribute): VariantAttribute
    {
        return $item->variantAttributes()->findOrFail($attribute);
    }
}
