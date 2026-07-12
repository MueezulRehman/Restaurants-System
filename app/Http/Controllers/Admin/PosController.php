<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerAllergy;
use App\Models\Deal;
use App\Models\MenuItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\MedicineInteraction;
use App\Models\Order;
use App\Models\OrderItemTopping;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\Topping;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Show the POS screen. The view rendered and the data loaded both
     * depend on the restaurant's POS mode (restaurant / retail / medical).
     */
    public function index()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403, 'No restaurant is linked to this account.');

        $posConfig = $restaurant->getPosConfig();

        if ($posConfig['mode'] === 'restaurant') {
            $categories = Category::with(['availableMenuItems' => function ($q) {
                $q->with(['sizes', 'category']);
            }])->where('is_active', true)->orderBy('sort_order')->get();

            $toppings = Topping::all();
            $deals = Deal::active()->get();
            $tables = Table::where('restaurant_id', $restaurant->id)->orderBy('number')->get();

            return view($posConfig['view'], compact('posConfig', 'categories', 'toppings', 'deals', 'tables'));
        }

        // Retail / medical: flat, searchable product list. For medical mode,
        // or whenever this restaurant has seeded medicines with batches,
        // we expose medicines and their batches; retail uses MenuItem.
        $showMedicalItems = ($posConfig['mode'] ?? 'retail') === 'medical'
            || Medicine::where(function ($q) use ($restaurant) {
                $q->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
            })->whereHas('batches', function ($q) use ($restaurant) {
                $q->where(function ($sub) use ($restaurant) {
                    $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                });
            })->exists();

        if ($showMedicalItems) {
            $items = Medicine::with(['category', 'batches' => function ($q) use ($restaurant) {
                $q->where(function ($sub) use ($restaurant) {
                    $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                })->orderBy('expiry_date');
            }])
                ->where(function ($q) use ($restaurant) {
                    $q->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                })
                ->orderBy('name')
                ->get();

            $medicineCategories = MedicineCategory::with(['medicines' => function ($q) use ($restaurant) {
                $q->where(function ($sub) use ($restaurant) {
                    $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                })->with(['category', 'batches' => function ($batchQuery) use ($restaurant) {
                    $batchQuery->where(function ($sub) use ($restaurant) {
                        $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                    })->orderBy('expiry_date');
                }])->orderBy('name');
            }])->orderBy('name')->get();

            $uncategorized = Medicine::with(['category', 'batches' => function ($q) use ($restaurant) {
                $q->where(function ($sub) use ($restaurant) {
                    $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                })->orderBy('expiry_date');
            }])
                ->where(function ($q) use ($restaurant) {
                    $q->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                })
                ->whereNull('category_id')
                ->orderBy('name')
                ->get();

            return view($posConfig['view'], compact('posConfig', 'items', 'showMedicalItems', 'medicineCategories', 'uncategorized'));
        }

        // Retail: MenuItem list
        $items = MenuItem::with('variants')
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view($posConfig['view'], compact('posConfig', 'items'));
    }

    /**
     * AJAX lookup used by the retail/medical POS search box and barcode
     * scanner input. Matches on item name or SKU/code, and on variant SKU.
     */
    public function lookup(Request $request)
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $term = trim((string) $request->query('q', ''));
        $posConfig = $restaurant->getPosConfig();

        if ($term === '') {
            return response()->json(['items' => []]);
        }

        $showMedicalItems = ($posConfig['mode'] ?? 'retail') === 'medical'
            || Medicine::where(function ($q) use ($restaurant) {
                $q->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
            })->whereHas('batches', function ($q) use ($restaurant) {
                $q->where(function ($sub) use ($restaurant) {
                    $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                });
            })->exists();

        if ($showMedicalItems) {
            $items = Medicine::with(['category', 'batches' => function ($q) use ($restaurant) {
                $q->where(function ($sub) use ($restaurant) {
                    $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                });
            }])
                ->where(function ($q) use ($term, $restaurant) {
                    $q->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%")
                            ->orWhere('generic_name', 'like', "%{$term}%")
                            ->orWhere('sku', 'like', "%{$term}%")
                            ->orWhere('barcode', 'like', "%{$term}%");
                    })
                    ->where(function ($sub) use ($restaurant) {
                        $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
                    });
                })
                ->limit(40)
                ->get();

            return response()->json([
                'items' => $items->map(fn ($item) => $this->serializeItem($item)),
            ]);
        }

        $items = MenuItem::with('variants')
            ->where('is_available', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%")
                    ->orWhereHas('variants', function ($vq) use ($term) {
                        $vq->where('sku', 'like', "%{$term}%")
                           ->orWhere('variant_name', 'like', "%{$term}%");
                    });
            })
            ->limit(20)
            ->get();

        return response()->json([
            'items' => $items->map(fn ($item) => $this->serializeItem($item)),
        ]);
    }

    protected function serializeItem($item): array
    {
        // If a Medicine model is passed, serialize its batch data instead
        if ($item instanceof Medicine) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'generic_name' => $item->generic_name,
                'sku' => $item->sku,
                'barcode' => $item->barcode,
                'requires_prescription' => (bool) $item->requires_prescription,
                'track_stock' => (bool) $item->track_stock,
                'batches' => $item->batches->map(fn ($b) => [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'expiry_date' => $b->expiry_date?->toDateString(),
                    'mfg_date' => $b->mfg_date?->toDateString(),
                    'price' => (float) $b->selling_price,
                    'purchase_price' => (float) $b->purchase_price,
                    'quantity' => (int) $b->quantity,
                ])->values()->toArray(),
            ];
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'sku' => $item->sku,
            'barcode' => $item->barcode,
            'price' => (float) $item->price,
            'track_stock' => $item->track_stock,
            'stock_quantity' => $item->stock_quantity,
            'has_sizes' => $item->has_sizes,
            'sizes' => $item->has_sizes ? $item->sizes->map(fn ($s) => [
                'label' => $s->size_label,
                'price' => (float) $s->price,
            ]) : [],
            'variants' => $item->variants->map(fn ($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'name' => $v->variant_name,
                'price' => (float) $v->getEffectivePrice(),
                'quantity_available' => $v->quantity_available,
            ]),
        ];
    }

    /**
     * Complete a sale. Always re-prices from the database (never trusts
                $order = Order::create([
     * "delivered" since a POS sale is instant, deducts tracked stock, and
     * logs the cash sale into the cashbook — same auto-log behaviour as a
                    'table_number' => $validated['table_number'] ?? null,
     * normal order being marked delivered.
     */
    public function checkout(Request $request)
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        // The POS screen posts the cart as a JSON string (built client-side
        // as items are scanned/tapped); decode it into an array before the
        // normal Laravel array-validation rules run against it.
        if ($request->has('cart') && is_string($request->input('cart'))) {
            $decoded = json_decode($request->input('cart'), true);
            $request->merge(['cart' => is_array($decoded) ? $decoded : []]);
        }

        $validated = $request->validate([
            'order_type' => 'nullable|in:dine_in,takeaway,delivery,online,table',
                'table_number' => 'nullable|string|max:50|required_if:order_type,table',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,online',
            'notes' => 'nullable|string|max:500',
            'prescription' => 'nullable|boolean',
            'prescription_doctor_id' => 'nullable|integer',
            'cart' => 'required|array|min:1',
            'cart.*.type' => 'required|in:menu_item,variant,deal,medicine_batch',
            'cart.*.id' => 'required|integer',
            'cart.*.quantity' => 'required|integer|min:1|max:999',
            'cart.*.size_label' => 'nullable|string',
            'cart.*.topping_ids' => 'nullable|array',
        ]);

        $customer = null;
        if (!empty($validated['customer_phone'])) {
            $customer = Customer::where('restaurant_id', $restaurant->id)
                ->where('phone', $validated['customer_phone'])
                ->first();
        }

        $order = DB::transaction(function () use ($validated, $restaurant, $customer) {
            $subtotal = 0;
            $lineItems = [];
            $stockMoves = []; // ['menu_item'|'variant', model, qtySold]

            foreach ($validated['cart'] as $line) {
                if ($line['type'] === 'menu_item') {
                    $menuItem = MenuItem::with('sizes')->where('restaurant_id', $restaurant->id)->findOrFail($line['id']);

                    if (! $menuItem->is_available) {
                        abort(422, "{$menuItem->name} is currently unavailable.");
                    }

                    $unitPrice = $menuItem->has_sizes
                        ? optional($menuItem->sizes->firstWhere('size_label', $line['size_label'] ?? null))->price
                        : $menuItem->price;

                    if ($unitPrice === null) {
                        abort(422, "Invalid size for {$menuItem->name}.");
                    }

                    $toppingTotal = 0;
                    $toppings = [];
                    if (! empty($line['topping_ids'])) {
                        foreach (Topping::whereIn('id', $line['topping_ids'])->get() as $t) {
                            $toppingTotal += $t->price;
                            $toppings[] = $t;
                        }
                    }

                    $lineTotal = ($unitPrice + $toppingTotal) * $line['quantity'];
                    $subtotal += $lineTotal;

                    if ($menuItem->track_stock) {
                        if ($menuItem->stock_quantity < $line['quantity']) {
                            abort(422, "Not enough stock for {$menuItem->name} (only {$menuItem->stock_quantity} left).");
                        }
                        $stockMoves[] = ['menu_item', $menuItem, $line['quantity']];
                    }

                    $lineItems[] = [
                        'item_type' => 'menu_item',
                        'menu_item_id' => $menuItem->id,
                        'product_variant_id' => null,
                        'deal_id' => null,
                        'item_name' => $menuItem->name,
                        'size_label' => $line['size_label'] ?? null,
                        'quantity' => $line['quantity'],
                        'unit_price' => $unitPrice + $toppingTotal,
                        'total_price' => $lineTotal,
                        'special_request' => null,
                        'toppings' => $toppings,
                    ];
                } elseif ($line['type'] === 'variant') {
                    $variant = ProductVariant::with('menuItem')->where('restaurant_id', $restaurant->id)->findOrFail($line['id']);

                    if (! $variant->is_available) {
                        abort(422, "{$variant->variant_name} is currently unavailable.");
                    }

                    if ($variant->quantity_available < $line['quantity']) {
                        abort(422, "Not enough stock for {$variant->variant_name} (only {$variant->quantity_available} left).");
                    }

                    $unitPrice = $variant->getEffectivePrice();
                    $lineTotal = $unitPrice * $line['quantity'];
                    $subtotal += $lineTotal;

                    $stockMoves[] = ['variant', $variant, $line['quantity']];

                    $lineItems[] = [
                        'item_type' => 'menu_item',
                        'menu_item_id' => $variant->menu_item_id,
                        'product_variant_id' => $variant->id,
                        'deal_id' => null,
                        'item_name' => $variant->menuItem->name . ' — ' . $variant->variant_name,
                        'size_label' => null,
                        'quantity' => $line['quantity'],
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                        'special_request' => null,
                        'toppings' => [],
                    ];
                } elseif ($line['type'] === 'deal') {
                    $deal = Deal::where('restaurant_id', $restaurant->id)->findOrFail($line['id']);

                    if (! $deal->is_active) {
                        abort(422, "{$deal->name} is currently unavailable.");
                    }

                    $lineTotal = $deal->price * $line['quantity'];
                    $subtotal += $lineTotal;

                    $lineItems[] = [
                        'item_type' => 'deal',
                        'menu_item_id' => null,
                        'product_variant_id' => null,
                        'deal_id' => $deal->id,
                        'item_name' => $deal->name,
                        'size_label' => null,
                        'quantity' => $line['quantity'],
                        'unit_price' => $deal->price,
                        'total_price' => $lineTotal,
                        'special_request' => null,
                        'toppings' => [],
                    ];
                } elseif ($line['type'] === 'medicine_batch') {
                    $batch = MedicineBatch::with('medicine')->where('restaurant_id', $restaurant->id)->findOrFail($line['id']);

                    if ($batch->expiry_date && $batch->expiry_date->isPast()) {
                        abort(422, "Batch {$batch->batch_number} of {$batch->medicine->name} is expired and cannot be sold.");
                    }

                    $medicine = $batch->medicine;
                    if ($medicine->requires_prescription && empty($validated['prescription'])) {
                        abort(422, "{$medicine->name} requires a prescription to sell.");
                    }

                    if ($customer) {
                        $allergyWarnings = $customer->getAllergyWarningsForMedicine($medicine->id);
                        if ($allergyWarnings->isNotEmpty()) {
                            $names = $allergyWarnings->pluck('allergy_name')->implode(', ');
                            abort(422, "Customer allergy warning: {$names}. Please verify before completing the sale.");
                        }
                    }

                    if ($batch->quantity < $line['quantity']) {
                        abort(422, "Not enough stock for {$medicine->name} (batch {$batch->batch_number}) — only {$batch->quantity} left.");
                    }

                    $unitPrice = $batch->selling_price;
                    $lineTotal = $unitPrice * $line['quantity'];
                    $subtotal += $lineTotal;

                    $stockMoves[] = ['medicine_batch', $batch, $line['quantity']];

                    $lineItems[] = [
                        'item_type' => 'medicine',
                        'menu_item_id' => null,
                        'product_variant_id' => null,
                        'deal_id' => null,
                        'medicine_batch_id' => $batch->id,
                        'item_name' => $medicine->name . ' — Batch ' . $batch->batch_number,
                        'size_label' => null,
                        'quantity' => $line['quantity'],
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                        'special_request' => null,
                        'toppings' => [],
                    ];
                }
            }

            $medicineIds = collect($lineItems)->filter(fn ($item) => ($item['item_type'] ?? null) === 'medicine')->pluck('medicine_id')->filter()->values();
            $medicineWarnings = [];
            $medicineIds->each(function ($medicineId) use (&$medicineWarnings) {
                $interactions = MedicineInteraction::getInteractionsFor($medicineId);
                foreach ($interactions as $interaction) {
                    $other = $interaction->medicine_id_1 === $medicineId ? $interaction->medicineSecond : $interaction->medicineFirst;
                    $medicineWarnings[] = [
                        'medicine' => $other?->name ?? 'Unknown medicine',
                        'type' => $interaction->interaction_type,
                        'description' => $interaction->interaction_description,
                        'action' => $interaction->recommended_action,
                    ];
                }
            });

            if (!empty($medicineWarnings)) {
                $warningSummary = collect($medicineWarnings)->map(fn ($warning) => "{$warning['medicine']} ({$warning['type']})")->implode(', ');
                abort(422, "Drug interaction warning detected: {$warningSummary}. Please verify before completing the sale.");
            }

            $order = Order::create([
                'restaurant_id' => $restaurant->id,
                'order_type' => $validated['order_type'] ?? 'takeaway',
                'table_number' => $validated['table_number'] ?? null,
                'status' => 'delivered',
                'customer_name' => $validated['customer_name'] ?: 'Walk-in Customer',
                'customer_phone' => $validated['customer_phone'] ?: '0000000000',
                'address' => null,
                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'total' => $subtotal,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'estimated_minutes' => 0,
                'confirmed_at' => now(),
                'ready_at' => now(),
                'delivered_at' => now(),
            ]);

            foreach ($lineItems as $line) {
                $toppingsToAttach = $line['toppings'];
                unset($line['toppings']);
                $orderItem = $order->items()->create($line);

                foreach ($toppingsToAttach as $t) {
                    OrderItemTopping::create([
                        'order_item_id' => $orderItem->id,
                        'topping_id' => $t->id,
                        'topping_name' => $t->name,
                        'price' => $t->price,
                    ]);
                }
            }

            foreach ($stockMoves as [$kind, $model, $qty]) {
                if ($kind === 'menu_item') {
                    $before = $model->stock_quantity;
                    $after = $before - $qty;
                    $model->update(['stock_quantity' => $after]);
                    $variantId = null;
                } elseif ($kind === 'variant') {
                    $before = $model->quantity_available;
                    $after = $before - $qty;
                    $model->update(['quantity_available' => $after]);
                    $variantId = $model->id;
                } elseif ($kind === 'medicine_batch') {
                    $before = $model->quantity;
                    $after = $before - $qty;
                    $model->update(['quantity' => $after]);
                    $variantId = null;
                } else {
                    continue;
                }

                StockAdjustment::create([
                    'restaurant_id' => $restaurant->id,
                    'product_variant_id' => $variantId,
                    'user_id' => Auth::id(),
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'change_quantity' => -$qty,
                    'reason' => 'sale',
                    'reference_id' => $order->id,
                    'notes' => isset($model->batch_number) ? "POS sale — order {$order->order_number} — batch {$model->batch_number}" : "POS sale — order {$order->order_number}",
                ]);
            }

            // Same auto-cashbook logging the regular order flow does when a
            // cash order is marked delivered — a POS sale is delivered
            // instantly so we log it right here instead.
            if ($order->payment_method === 'cash') {
                Cashbook::create([
                    'restaurant_id' => $restaurant->id,
                    'type' => 'in',
                    'amount' => $order->total,
                    'description' => "POS sale {$order->order_number} ({$order->customer_name})",
                    'source' => 'order',
                    'order_id' => $order->id,
                    'date' => now()->toDateString(),
                    'created_by' => Auth::id(),
                ]);
            }

            return $order;
        });

        return redirect()->route('manager.pos.receipt', $order)->with('success', "Sale complete — {$order->order_number}.");
    }

    /**
     * Printable receipt/invoice for a completed POS sale.
     */
    public function receipt(Order $order)
    {
        $user = Auth::user();
        if (! $user->isSuperAdmin() && $order->restaurant_id !== $user->effectiveRestaurantId()) {
            abort(403);
        }

        $order->load(['items.toppings', 'items.variant']);
        $restaurant = $user->effectiveRestaurant() ?: $order->restaurant;

        return view('admin.pos.receipt', compact('order', 'restaurant'));
    }
}
