<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InstallmentPlan;
use App\Models\LoyaltyAccount;
use App\Models\RetailBrand;
use App\Models\RetailCollection;
use App\Models\StockTransfer;
use App\Models\TradeIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetailOperationsController extends Controller
{
    public function index()
    {
        $id = Auth::user()->effectiveRestaurantId();
        return view('manager.retail-operations.index', ['brands' => RetailBrand::where('restaurant_id', $id)->latest()->get(), 'collections' => RetailCollection::where('restaurant_id', $id)->latest()->get(), 'transfers' => StockTransfer::where('restaurant_id', $id)->latest()->get(), 'tradeIns' => TradeIn::with('customer')->where('restaurant_id', $id)->latest()->get(), 'plans' => InstallmentPlan::with('customer')->where('restaurant_id', $id)->latest()->get(), 'customers' => Customer::where('restaurant_id', $id)->orderBy('name')->get()]);
    }
    public function store(Request $request)
    {
        $id = Auth::user()->effectiveRestaurantId();
        $type = $request->input('type');
        $common = ['restaurant_id' => $id];
        if ($type === 'brand') RetailBrand::create(array_merge($common, $request->validate(['name' => 'required|string|max:150', 'description' => 'nullable|string|max:255'])));
        elseif ($type === 'collection') RetailCollection::create(array_merge($common, $request->validate(['name' => 'required|string|max:150', 'season' => 'nullable|string|max:30', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date'])));
        elseif ($type === 'transfer') StockTransfer::create(array_merge($common, $request->validate(['from_location' => 'required|string|max:100', 'to_location' => 'required|string|max:100', 'item_name' => 'required|string|max:255', 'quantity' => 'required|numeric|min:0.001']), ['created_by' => Auth::id()]));
        elseif ($type === 'trade_in') TradeIn::create(array_merge($common, $request->validate(['customer_id' => 'nullable|integer', 'item_name' => 'required|string|max:255', 'serial_number' => 'nullable|string|max:100', 'estimated_value' => 'required|numeric|min:0', 'condition' => 'nullable|string|max:100'])));
        elseif ($type === 'installment') InstallmentPlan::create(array_merge($common, $request->validate(['customer_id' => 'nullable|integer', 'item_name' => 'required|string|max:255', 'total_amount' => 'required|numeric|min:0', 'deposit' => 'nullable|numeric|min:0', 'months' => 'required|integer|min:1', 'next_due_at' => 'nullable|date'])));
        elseif ($type === 'loyalty') LoyaltyAccount::updateOrCreate(['restaurant_id' => $id, 'customer_id' => $request->validate(['customer_id' => 'required|integer'])['customer_id']], ['points' => $request->integer('points', 0)]);
        return back()->with('success', 'Retail record saved.');
    }
}
