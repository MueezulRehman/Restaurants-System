<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\StockTransfer;
use App\Models\Branch;
use App\Models\BranchInventory;
use Illuminate\Support\Facades\DB;
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
        $branches = Branch::where('restaurant_id', $restaurantId)->where('is_active', true)->orderBy('name')->get();

        return view('manager.stock-transfers.index', compact('transfers', 'items', 'branches'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $fromLocation = trim((string) ($request->input('from_location') ?? ''));
        $toLocation = trim((string) ($request->input('to_location') ?? ''));

        if ($fromLocation !== '' && $toLocation !== '' && strtolower($fromLocation) === strtolower($toLocation)) {
            return back()->withInput()->withErrors(['to_location' => 'The destination must be different from the source.']);
        }

        $data = $request->validate([
            'from_branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId)->where('is_active', true))],
            'to_branch_id' => ['nullable', 'integer', 'different:from_branch_id', Rule::exists('branches', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId)->where('is_active', true))],
            'from_location' => ['nullable', 'string', 'max:255'],
            'to_location' => ['nullable', 'string', 'max:255', 'different:from_location'],
            'item_type' => ['nullable', Rule::in(['menu_item', 'variant'])],
            'item_id' => ['nullable', 'integer'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'quantity' => 'required|numeric|min:0.001',
        ]);

        if (empty($data['from_branch_id']) && empty($data['from_location'])) {
            return back()->withInput()->withErrors(['from_branch_id' => 'The from branch id field is required.']);
        }

        if (empty($data['to_branch_id']) && empty($data['to_location'])) {
            return back()->withInput()->withErrors(['to_branch_id' => 'The to branch id field is required.']);
        }

        $fromBranchId = $data['from_branch_id'] ?? null;
        $toBranchId = $data['to_branch_id'] ?? null;
        $fromBranch = $fromBranchId ? Branch::where('restaurant_id', $restaurantId)->findOrFail($fromBranchId) : null;
        $toBranch = $toBranchId ? Branch::where('restaurant_id', $restaurantId)->findOrFail($toBranchId) : null;

        if (($data['item_type'] ?? null) || ($data['item_id'] ?? null)) {
            $item = ($data['item_type'] ?? 'menu_item') === 'variant'
                ? \App\Models\ProductVariant::where('restaurant_id', $restaurantId)->findOrFail($data['item_id'])
                : MenuItem::where('restaurant_id', $restaurantId)->findOrFail($data['item_id']);
        }

        DB::transaction(function () use ($data, $restaurantId, $fromBranch, $toBranch, $fromBranchId, $toBranchId): void {
            $sourceBranchId = $fromBranchId ?: null;
            $destinationBranchId = $toBranchId ?: null;
            $quantity = (float) $data['quantity'];

            if ($sourceBranchId && $destinationBranchId && ! empty($data['item_type']) && ! empty($data['item_id'])) {
                $source = BranchInventory::where('restaurant_id', $restaurantId)->where('branch_id', $sourceBranchId)->where('item_type', $data['item_type'])->where('item_id', $data['item_id'])->lockForUpdate()->first();
                abort_unless($source && (float) $source->quantity >= $quantity, 422, 'The source branch does not have enough branch stock.');
                $source->decrement('quantity', $quantity);
                $destination = BranchInventory::firstOrCreate(['restaurant_id' => $restaurantId, 'branch_id' => $destinationBranchId, 'item_type' => $data['item_type'], 'item_id' => $data['item_id']], ['quantity' => 0]);
                $destination->increment('quantity', $quantity);
            }

            StockTransfer::create(array_merge($data, [
                'restaurant_id' => $restaurantId,
                'from_branch_id' => $sourceBranchId,
                'to_branch_id' => $destinationBranchId,
                'from_location' => $fromBranch?->name ?? ($data['from_location'] ?? null),
                'to_location' => $toBranch?->name ?? ($data['to_location'] ?? null),
                'status' => 'completed',
                'created_by' => Auth::id(),
            ]));
        });

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
