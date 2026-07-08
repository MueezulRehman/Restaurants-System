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
            ->orderBy('created_at', 'desc')
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
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'available' => 'boolean',
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
        unset($validated['available']);

        MenuItem::create($validated);

        return redirect()->route('admin.menu-items.index')
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
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'available' => 'boolean',
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
        unset($validated['available']);

        $item->update($validated);

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $item)
    {
        $item->delete();
        return redirect()->route('admin.menu-items.index')
            ->with('success', 'Menu item deleted successfully.');
    }
}
