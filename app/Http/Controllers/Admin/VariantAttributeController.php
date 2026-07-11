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
    public function index(MenuItem $item)
    {
        $attributes = $item->variantAttributes()->orderBy('sort_order')->paginate(20);
        return view('admin.variant-attributes.index', compact('item', 'attributes'));
    }

    /**
     * Show create attribute form.
     */
    public function create(MenuItem $item)
    {
        return view('admin.variant-attributes.create', compact('item'));
    }

    /**
     * Store new attribute.
     */
    public function store(Request $request, MenuItem $item)
    {
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
    public function edit(MenuItem $item, VariantAttribute $attribute)
    {
        return view('admin.variant-attributes.edit', compact('item', 'attribute'));
    }

    /**
     * Update attribute.
     */
    public function update(Request $request, MenuItem $item, VariantAttribute $attribute)
    {
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
    public function destroy(MenuItem $item, VariantAttribute $attribute)
    {
        $attribute->delete();
        return redirect()->route('manager.menu-items.attributes.index', $item)
            ->with('success', 'Attribute deleted successfully.');
    }
}
