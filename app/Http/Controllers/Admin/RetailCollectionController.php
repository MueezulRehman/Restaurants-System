<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\RetailCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RetailCollectionController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $collections = RetailCollection::withCount('menuItems')->where('restaurant_id', $restaurantId)->latest()->paginate(20);
        $items = MenuItem::where('restaurant_id', $restaurantId)->orderBy('name')->get();

        return view('admin.collections.index', compact('collections', 'items'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate(['name' => 'required|string|max:150', 'season' => 'nullable|string|max:30', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date|after_or_equal:starts_at']);
        RetailCollection::create(array_merge($data, ['restaurant_id' => $restaurantId]));

        return back()->with('success', 'Collection created.');
    }

    public function update(Request $request, RetailCollection $collection)
    {
        abort_unless((int) $collection->restaurant_id === (int) Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate(['name' => 'required|string|max:150', 'season' => 'nullable|string|max:30', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date|after_or_equal:starts_at']);
        $collection->update($data);

        return back()->with('success', 'Collection updated.');
    }

    public function assignItem(Request $request, RetailCollection $collection)
    {
        abort_unless((int) $collection->restaurant_id === (int) Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate(['menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')->where(fn($query) => $query->where('restaurant_id', $collection->restaurant_id))]]);
        MenuItem::where('restaurant_id', $collection->restaurant_id)->findOrFail($data['menu_item_id'])->update(['collection_id' => $collection->id]);

        return back()->with('success', 'Product assigned to collection.');
    }
}
