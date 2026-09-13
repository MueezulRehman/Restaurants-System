<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\MenuItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchInventoryController extends Controller
{
    private function restaurantId(): int
    {
        $id = auth()->user()?->effectiveRestaurantId();
        abort_unless($id, 403);
        return (int) $id;
    }

    public function index(Request $request)
    {
        $id = $this->restaurantId();
        $branches = Branch::where('restaurant_id', $id)->where('is_active', true)->orderBy('name')->get();
        $branchId = (int) $request->input('branch_id', $branches->first()?->id);
        $inventory = BranchInventory::where('restaurant_id', $id)->where('branch_id', $branchId)->orderBy('item_type')->orderBy('item_id')->get();
        $items = MenuItem::where('restaurant_id', $id)->orderBy('name')->get();
        return view('manager.branch-inventory.index', compact('branches', 'branchId', 'inventory', 'items'));
    }

    public function adjust(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where(fn($q) => $q->where('restaurant_id', $id)->where('is_active', true))], 'item_type' => ['required', Rule::in(['menu_item', 'variant'])], 'item_id' => 'required|integer', 'quantity' => 'required|numeric|min:0']);
        $itemModel = $data['item_type'] === 'variant' ? ProductVariant::where('restaurant_id', $id)->findOrFail($data['item_id']) : MenuItem::where('restaurant_id', $id)->findOrFail($data['item_id']);
        BranchInventory::updateOrCreate(['restaurant_id' => $id, 'branch_id' => $data['branch_id'], 'item_type' => $data['item_type'], 'item_id' => $data['item_id']], ['quantity' => $data['quantity']]);
        return back()->with('success', 'Branch inventory balance saved.');
    }
}
