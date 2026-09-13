<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\TradeIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TradeInController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $tradeIns = TradeIn::with('customer')
            ->where('restaurant_id', $restaurantId)
            ->latest()
            ->paginate(20);
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();

        return view('manager.trade-ins.index', compact('tradeIns', 'customers'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'item_name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:150',
            'estimated_value' => 'required|numeric|min:0',
            'condition' => 'nullable|string|max:100',
        ]);

        TradeIn::create(array_merge($data, [
            'restaurant_id' => $restaurantId,
            'status' => 'received',
        ]));

        return back()->with('success', 'Trade-in recorded.');
    }

    public function update(Request $request, TradeIn $tradeIn)
    {
        abort_unless($tradeIn->restaurant_id === Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate([
            'status' => ['required', Rule::in(['received', 'inspected', 'accepted', 'rejected', 'credited'])],
        ]);
        $tradeIn->update($data);

        return back()->with('success', 'Trade-in status updated.');
    }
}
