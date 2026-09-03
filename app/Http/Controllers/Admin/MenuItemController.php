<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('category')->orderBy('created_at', 'desc');

        if ($q = trim((string) $request->input('q', ''))) {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $items = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.menu-items.index', compact('items', 'categories'));
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
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'unit_type' => 'nullable|string|max:30',
            'price_per_unit' => 'nullable|numeric|min:0',
            'allow_fractional_qty' => 'boolean',
            'pos_show_line_edit' => 'boolean',
            'category_id' => 'required|exists:categories,id',
            'available' => 'boolean',
            'has_variants' => 'boolean',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $dir = public_path('images/menu');
            if (! is_dir($dir)) mkdir($dir, 0755, true);
            $file = $request->file('image');
            $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['image'] = 'menu/' . $filename;
        }

        $validated['is_available'] = $validated['available'] ?? false;
        $validated['has_variants'] = $validated['has_variants'] ?? false;
        $validated['track_stock'] = $validated['track_stock'] ?? false;
        $validated['allow_fractional_qty'] = $validated['allow_fractional_qty'] ?? false;
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 5;
        $validated['unit_type'] = $validated['unit_type'] ?? ($validated['unit'] ?? 'piece');
        if (empty($validated['price_per_unit'])) {
            $validated['price_per_unit'] = $validated['price'];
        }
        unset($validated['available']);
        $validated['pos_show_line_edit'] = $validated['pos_show_line_edit'] ?? false;

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
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'unit_type' => 'nullable|string|max:30',
            'price_per_unit' => 'nullable|numeric|min:0',
            'allow_fractional_qty' => 'boolean',
            'pos_show_line_edit' => 'boolean',
            'category_id' => 'required|exists:categories,id',
            'available' => 'boolean',
            'has_variants' => 'boolean',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image && file_exists(public_path('images/' . $item->image))) {
                @unlink(public_path('images/' . $item->image));
            }
            $dir = public_path('images/menu');
            if (! is_dir($dir)) mkdir($dir, 0755, true);
            $file = $request->file('image');
            $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['image'] = 'menu/' . $filename;
        }

        $validated['is_available'] = $validated['available'] ?? false;
        $validated['has_variants'] = $validated['has_variants'] ?? false;
        $validated['track_stock'] = $validated['track_stock'] ?? false;
        $validated['allow_fractional_qty'] = $validated['allow_fractional_qty'] ?? false;
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 5;
        $validated['unit_type'] = $validated['unit_type'] ?? ($validated['unit'] ?? 'piece');
        if (empty($validated['price_per_unit'])) {
            $validated['price_per_unit'] = $validated['price'];
        }
        unset($validated['available']);
        $validated['pos_show_line_edit'] = $validated['pos_show_line_edit'] ?? false;

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
