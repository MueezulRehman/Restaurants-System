<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetailToolsController extends Controller
{
    public function barcodeLabels(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $items = MenuItem::with('variants')->where('restaurant_id', $restaurantId)
            ->when($request->filled('q'), fn($q) => $q->where(function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%")->orWhere('barcode', 'like', "%{$term}%");
            }))->orderBy('name')->get();

        $labels = collect();
        foreach ($items as $item) {
            if ($item->variants->isEmpty()) {
                $labels->push(['name' => $item->name, 'code' => $item->barcode ?: $item->sku ?: 'NO-CODE', 'price' => $item->price]);
            } else {
                foreach ($item->variants as $variant) {
                    $labels->push(['name' => $item->name . ' - ' . $variant->variant_name, 'code' => $variant->sku ?: 'NO-CODE', 'price' => $variant->getEffectivePrice()]);
                }
            }
        }
        return view('manager.retail-tools.barcode-labels', compact('labels'));
    }

    public function profitMargins()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $items = MenuItem::with('variants')->where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $rows = collect();
        foreach ($items as $item) {
            if ($item->variants->isEmpty()) {
                $rows->push(['name' => $item->name, 'sku' => $item->sku ?: $item->barcode, 'cost' => (float) $item->cost_price, 'price' => (float) $item->price, 'stock' => (float) $item->stock_quantity]);
            } else {
                foreach ($item->variants as $variant) {
                    $rows->push(['name' => $item->name . ' - ' . $variant->variant_name, 'sku' => $variant->sku, 'cost' => (float) $variant->cost_price, 'price' => (float) $variant->getEffectivePrice(), 'stock' => (float) $variant->quantity_available]);
                }
            }
        }
        return view('manager.retail-tools.profit-margins', compact('rows'));
    }
}
