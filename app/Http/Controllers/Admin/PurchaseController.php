<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PurchaseHeader;
use App\Models\PurchaseItem;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        $batches = MedicineBatch::with(['medicine', 'purchaseItem.purchase'])
            ->where('restaurant_id', $restaurantId)
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.purchases.index', compact('batches'));
    }

    public function create()
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();
        $medicines = Medicine::where(function ($q) use ($restaurantId) {
            $q->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurantId);
        })->orderBy('name')->get();

        return view('admin.purchases.create', compact('medicines'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        $data = $request->validate([
            'supplier_name' => 'nullable|string|max:150',
            'invoice_no' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'medicine_id' => 'required|integer|exists:medicines,id',
            'batch_number' => 'nullable|string|max:100',
            'mfg_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'rack_number' => 'nullable|string|max:50',
        ]);

        $header = PurchaseHeader::create([
            'restaurant_id' => $restaurantId,
            'supplier_name' => $data['supplier_name'] ?? null,
            'invoice_no' => $data['invoice_no'] ?? null,
            'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
            'total' => $data['purchase_price'] * $data['quantity'],
            'status' => 'received',
            'created_by' => Auth::id(),
            'notes' => 'Purchase recorded via pharmacy stock entry',
        ]);

        $item = PurchaseItem::create([
            'purchase_id' => $header->id,
            'medicine_id' => $data['medicine_id'],
            'batch_no' => $data['batch_number'] ?? null,
            'manufacture_date' => $data['mfg_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'purchase_price' => $data['purchase_price'],
            'selling_price' => $data['selling_price'],
            'qty' => $data['quantity'],
            'free_qty' => 0,
            'tax' => 0,
            'discount' => 0,
            'rack' => $data['rack_number'] ?? null,
            'line_total' => $data['purchase_price'] * $data['quantity'],
        ]);

        $batch = MedicineBatch::create([
            'medicine_id' => $data['medicine_id'],
            'restaurant_id' => $restaurantId,
            'batch_number' => $data['batch_number'] ?? null,
            'mfg_date' => $data['mfg_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'purchase_price' => $data['purchase_price'],
            'selling_price' => $data['selling_price'],
            'quantity' => $data['quantity'],
            'rack_number' => $data['rack_number'] ?? null,
            'purchase_item_id' => $item->id,
        ]);

        StockAdjustment::create([
            'restaurant_id' => $restaurantId,
            'product_variant_id' => null,
            'user_id' => Auth::id(),
            'quantity_before' => 0,
            'quantity_after' => $batch->quantity,
            'change_quantity' => $batch->quantity,
            'reason' => 'purchase',
            'reference_id' => $header->id,
            'notes' => 'Purchase received — batch ' . ($batch->batch_number ?? $batch->id),
        ]);

        return redirect()->route('manager.purchases.create')->with('success', 'Batch recorded and stock updated.');
    }
}
