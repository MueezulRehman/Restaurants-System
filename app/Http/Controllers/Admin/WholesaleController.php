<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Customer;
use App\Models\SalesRepresentative;
use App\Models\WholesalePriceList;
use App\Models\WholesalePriceListItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WholesaleController extends Controller
{
    private function restaurantId(): int
    {
        $id = auth()->user()?->effectiveRestaurantId();
        abort_unless($id, 403);
        return (int) $id;
    }

    public function index()
    {
        $id = $this->restaurantId();
        $priceLists = WholesalePriceList::withCount('items')->where('restaurant_id', $id)->latest()->get();
        $representatives = SalesRepresentative::where('restaurant_id', $id)->latest()->get();
        $customers = Customer::with('salesRepresentative')->where('restaurant_id', $id)->orderBy('name')->get();
        $products = MenuItem::where('restaurant_id', $id)->where('is_available', true)->orderBy('name')->get();
        return view('manager.wholesale.index', compact('priceLists', 'representatives', 'customers', 'products'));
    }

    public function storePriceList(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('wholesale_price_lists', 'name')->where(fn($q) => $q->where('restaurant_id', $id))],
            'customer_group' => 'nullable|string|max:80',
            'is_active' => 'nullable|boolean',
        ]);
        WholesalePriceList::create([...$data, 'restaurant_id' => $id, 'is_active' => $request->boolean('is_active', true)]);
        return back()->with('success', 'Wholesale price list created.');
    }

    public function storeRepresentative(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'phone' => 'nullable|string|max:40', 'commission_rate' => 'nullable|numeric|min:0|max:100']);
        SalesRepresentative::create([...$data, 'restaurant_id' => $this->restaurantId(), 'commission_rate' => $data['commission_rate'] ?? 0, 'is_active' => true]);
        return back()->with('success', 'Sales representative added.');
    }

    public function storePriceListItem(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate([
            'wholesale_price_list_id' => ['required', 'integer', Rule::exists('wholesale_price_lists', 'id')->where(fn($q) => $q->where('restaurant_id', $id))],
            'menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')->where(fn($q) => $q->where('restaurant_id', $id))],
            'price' => 'required|numeric|min:0',
        ]);
        WholesalePriceListItem::updateOrCreate(
            ['wholesale_price_list_id' => $data['wholesale_price_list_id'], 'menu_item_id' => $data['menu_item_id'], 'product_variant_id' => null],
            ['price' => $data['price']]
        );
        return back()->with('success', 'Wholesale product price saved.');
    }

    public function assignRepresentative(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(fn($q) => $q->where('restaurant_id', $id))],
            'sales_representative_id' => ['nullable', 'integer', Rule::exists('sales_representatives', 'id')->where(fn($q) => $q->where('restaurant_id', $id))],
        ]);
        \App\Models\Customer::where('restaurant_id', $id)->whereKey($data['customer_id'])->update(['sales_representative_id' => $data['sales_representative_id'] ?? null]);
        return back()->with('success', 'Sales representative assignment updated.');
    }

    public function commissionReport(Request $request)
    {
        $id = $this->restaurantId();
        $orders = Order::with('salesRepresentative')
            ->where('restaurant_id', $id)
            ->whereNotNull('sales_representative_id')
            ->whereBetween('created_at', [
                $request->date('from', now()->startOfMonth()),
                $request->date('to', now())->endOfDay(),
            ])
            ->whereIn('status', ['confirmed', 'preparing', 'ready', 'delivered'])
            ->get();

        $report = $orders->groupBy('sales_representative_id')->map(function ($sales) {
            $representative = $sales->first()->salesRepresentative;
            $gross = (float) $sales->sum('total');
            return [
                'representative' => $representative,
                'orders' => $sales->count(),
                'gross' => $gross,
                'commission' => round($gross * ((float) ($representative?->commission_rate ?? 0)) / 100, 2),
            ];
        })->values();

        return view('manager.wholesale.commissions', compact('report'));
    }
}
