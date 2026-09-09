<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Support\Tenancy;

class CategoryController extends Controller
{
    public function index()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403, 'No restaurant is linked to this account.');
        Tenancy::configureTenantConnection($restaurant);
        $categories = Category::withCount('menuItems')->orderBy('created_at', 'desc')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $this->configureTenant();
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $this->configureTenant();
        $restaurantId = Auth::user()->effectiveRestaurantId();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(fn($q) => $q->where('restaurant_id', $restaurantId)),
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'pos_show_line_edit' => 'boolean',
        ]);

        $validated['pos_show_line_edit'] = $validated['pos_show_line_edit'] ?? false;
        $validated['restaurant_id'] = $restaurantId;
        $category = Category::create($validated);

        return redirect()->route('manager.menu-items.create', ['category_id' => $category->id])
            ->with('success', 'Category created. Add the first item to it.');
    }

    public function edit(string $category)
    {
        $category = $this->resolveCategory($category);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, string $category)
    {
        $category = $this->resolveCategory($category);
        $restaurantId = Auth::user()->effectiveRestaurantId();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->where(fn($q) => $q->where('restaurant_id', $restaurantId))
                    ->ignore($category->id),
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'pos_show_line_edit' => 'boolean',
        ]);

        $validated['pos_show_line_edit'] = $validated['pos_show_line_edit'] ?? false;
        $category->update($validated);
        $category->menuItems()->update(['pos_show_line_edit' => $category->pos_show_line_edit]);

        return redirect()->route('manager.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(string $category)
    {
        $category = $this->resolveCategory($category);
        $category->delete();
        return redirect()->route('manager.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    private function configureTenant(): void
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403, 'No restaurant is linked to this account.');
        Tenancy::configureTenantConnection($restaurant);
    }

    private function resolveCategory(string $category): Category
    {
        $this->configureTenant();

        return Category::where('restaurant_id', Auth::user()->effectiveRestaurantId())
            ->findOrFail($category);
    }
}
