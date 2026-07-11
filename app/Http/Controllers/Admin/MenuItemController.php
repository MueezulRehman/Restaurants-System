<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index()
    {
        $items = MenuItem::with('category')
            ->withCount(['variants', 'variantAttributes'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('admin.menu-items.index', compact('items'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.menu-items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required_unless:has_sizes,1|nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'available' => 'boolean',
            'has_sizes' => 'boolean',
            'allows_toppings' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $dir = public_path('images/menu');
            if (! is_dir($dir)) mkdir($dir, 0755, true);
            $file = $request->file('image');
            $filename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['image'] = 'menu/'.$filename;
        }

        $validated['is_available'] = $validated['available'] ?? false;
        $validated['has_sizes'] = $validated['has_sizes'] ?? false;
        $validated['allows_toppings'] = $validated['allows_toppings'] ?? false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['track_stock'] = $validated['track_stock'] ?? false;
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 5;
        unset($validated['available']);

        MenuItem::create($validated);

        return redirect()->route('manager.menu-items.index')
            ->with('success', 'Menu item created successfully.');
    }

    public function edit(MenuItem $item)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.menu-items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, MenuItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required_unless:has_sizes,1|nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'available' => 'boolean',
            'has_sizes' => 'boolean',
            'allows_toppings' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image && file_exists(public_path('images/'.$item->image))) {
                @unlink(public_path('images/'.$item->image));
            }
            $dir = public_path('images/menu');
            if (! is_dir($dir)) mkdir($dir, 0755, true);
            $file = $request->file('image');
            $filename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['image'] = 'menu/'.$filename;
        }

        $validated['is_available'] = $validated['available'] ?? false;
        $validated['has_sizes'] = $validated['has_sizes'] ?? false;
        $validated['allows_toppings'] = $validated['allows_toppings'] ?? false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['track_stock'] = $validated['track_stock'] ?? false;
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 5;
        unset($validated['available']);

        $item->update($validated);

        return redirect()->route('manager.menu-items.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $item)
    {
        $item->delete();
        return redirect()->route('manager.menu-items.index')
            ->with('success', 'Menu item deleted successfully.');
    }
}
