<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $transfers = StockTransfer::where('restaurant_id', $restaurantId)
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $items = MenuItem::where('restaurant_id', $restaurantId)->orderBy('name')->get();

        return view('admin.stock-transfers.index', compact('transfers', 'items'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate([
            'from_location' => 'required|string|max:100',
            'to_location' => ['required', 'string', 'max:100', 'different:from_location'],
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.001',
        ]);

        StockTransfer::create(array_merge($data, [
            'restaurant_id' => $restaurantId,
            'status' => 'completed',
            'created_by' => Auth::id(),
        ]));

        return back()->with('success', 'Stock transfer recorded.');
    }

    public function update(Request $request, StockTransfer $stockTransfer)
    {
        abort_unless($stockTransfer->restaurant_id === Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_transit', 'completed', 'cancelled'])],
        ]);
        $stockTransfer->update($data);

        return back()->with('success', 'Stock transfer status updated.');
    }
}
