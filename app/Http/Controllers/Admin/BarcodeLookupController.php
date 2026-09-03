<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BarcodeLookupController extends Controller
{
    /**
     * Resolve product name (and price when known locally) from a barcode.
     *
     * Order:
     *  1. Local catalog (MenuItem / Medicine) → name + price
     *  2. Open Food Facts (public API) → name only (price is store-specific)
     */
    public function lookup(Request $request)
    {
        $barcode = trim((string) $request->query('barcode', ''));
        $barcode = preg_replace('/\s+/', '', $barcode);

        if ($barcode === '' || strlen($barcode) < 4) {
            return response()->json([
                'found' => false,
                'message' => 'Enter or scan a valid barcode.',
            ], 422);
        }

        $restaurant = Auth::user()?->effectiveRestaurant();

        // 1) Local menu items
        $menuQuery = MenuItem::query()->where(function ($q) use ($barcode) {
            $q->where('barcode', $barcode)->orWhere('sku', $barcode);
        });
        if ($restaurant) {
            $menuQuery->where(function ($q) use ($restaurant) {
                $q->where('restaurant_id', $restaurant->id)->orWhereNull('restaurant_id');
            });
        }
        $menuItem = $menuQuery->first();
        if ($menuItem) {
            return response()->json([
                'found' => true,
                'source' => 'local',
                'type' => 'menu_item',
                'name' => $menuItem->name,
                'price' => $menuItem->price !== null ? (float) $menuItem->price : null,
                'cost_price' => $menuItem->cost_price !== null ? (float) $menuItem->cost_price : null,
                'sku' => $menuItem->sku,
                'barcode' => $menuItem->barcode ?: $barcode,
                'description' => $menuItem->description,
                'message' => 'Matched an existing product in your catalog.',
            ]);
        }

        // 2) Local medicines
        $medQuery = Medicine::query()->where(function ($q) use ($barcode) {
            $q->where('barcode', $barcode)->orWhere('sku', $barcode);
        });
        if ($restaurant) {
            $medQuery->where(function ($q) use ($restaurant) {
                $q->where('restaurant_id', $restaurant->id)->orWhereNull('restaurant_id');
            });
        }
        $medicine = $medQuery->first();
        if ($medicine) {
            $batch = $medicine->batches()->orderByDesc('id')->first();
            $price = $batch?->selling_price;

            return response()->json([
                'found' => true,
                'source' => 'local',
                'type' => 'medicine',
                'name' => $medicine->name,
                'generic_name' => $medicine->generic_name,
                'price' => $price !== null ? (float) $price : null,
                'sku' => $medicine->sku,
                'barcode' => $medicine->barcode ?: $barcode,
                'description' => $medicine->description,
                'message' => 'Matched an existing medicine in your catalog.',
            ]);
        }

        // 3) External: Open Food Facts (name only — no reliable retail price)
        try {
            $response = Http::timeout(4)
                ->withHeaders(['User-Agent' => 'CodeIbex-POS/1.0 (barcode-lookup)'])
                ->get('https://world.openfoodfacts.org/api/v2/product/' . urlencode($barcode) . '.json');

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? 0) == 1 && ! empty($data['product'])) {
                    $product = $data['product'];
                    $name = $product['product_name']
                        ?? $product['product_name_en']
                        ?? $product['generic_name']
                        ?? null;

                    if ($name) {
                        return response()->json([
                            'found' => true,
                            'source' => 'openfoodfacts',
                            'type' => 'external',
                            'name' => trim($name),
                            'price' => null,
                            'sku' => $barcode,
                            'barcode' => $barcode,
                            'description' => $product['generic_name'] ?? null,
                            'message' => 'Name found from barcode database. Selling price is not included — enter your price.',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore network errors; fall through
        }

        // 4) Open Beauty Facts (cosmetics / personal care)
        try {
            $response = Http::timeout(4)
                ->withHeaders(['User-Agent' => 'CodeIbex-POS/1.0 (barcode-lookup)'])
                ->get('https://world.openbeautyfacts.org/api/v2/product/' . urlencode($barcode) . '.json');

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? 0) == 1 && ! empty($data['product'])) {
                    $product = $data['product'];
                    $name = $product['product_name']
                        ?? $product['product_name_en']
                        ?? null;
                    if ($name) {
                        return response()->json([
                            'found' => true,
                            'source' => 'openbeautyfacts',
                            'type' => 'external',
                            'name' => trim($name),
                            'price' => null,
                            'sku' => $barcode,
                            'barcode' => $barcode,
                            'description' => $product['generic_name'] ?? null,
                            'message' => 'Name found from barcode database. Enter your selling price.',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return response()->json([
            'found' => false,
            'source' => null,
            'message' => 'No product found for this barcode. Enter name and price manually.',
        ]);
    }

    /**
     * POS quick-register: create product from unknown barcode, return cart line data.
     */
    public function quickStore(Request $request)
    {
        $user = Auth::user();
        $restaurant = $user?->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $data = $request->validate([
            'barcode' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type' => 'nullable|in:menu_item,medicine',
        ]);

        $barcode = preg_replace('/\s+/', '', $data['barcode']);
        $name = trim($data['name']);
        $price = round((float) $data['price'], 2);
        $posMode = $restaurant->getPosConfig()['mode'] ?? 'retail';
        $wantMedicine = ($data['type'] ?? null) === 'medicine' || $posMode === 'medical';

        // Already exists?
        $existingMenu = MenuItem::where(function ($q) use ($barcode) {
            $q->where('barcode', $barcode)->orWhere('sku', $barcode);
        })->first();
        if ($existingMenu) {
            return response()->json([
                'ok' => true,
                'created' => false,
                'line' => [
                    'type' => 'menu_item',
                    'id' => $existingMenu->id,
                    'name' => $existingMenu->name,
                    'price' => (float) $existingMenu->price,
                    'stock' => $existingMenu->track_stock ? (int) $existingMenu->stock_quantity : null,
                ],
                'message' => 'Product already exists — added to bill.',
            ]);
        }

        if ($wantMedicine && class_exists(Medicine::class)) {
            $existingMed = Medicine::where(function ($q) use ($barcode) {
                $q->where('barcode', $barcode)->orWhere('sku', $barcode);
            })->first();
            if ($existingMed) {
                $batch = $existingMed->batches()->orderByDesc('id')->first();
                return response()->json([
                    'ok' => true,
                    'created' => false,
                    'line' => [
                        'type' => $batch ? 'medicine_batch' : 'menu_item',
                        'id' => $batch ? $batch->id : $existingMed->id,
                        'name' => $existingMed->name . ($batch ? ' — Batch ' . $batch->batch_number : ''),
                        'price' => $batch ? (float) $batch->selling_price : $price,
                        'stock' => $batch ? (int) $batch->quantity : null,
                    ],
                    'message' => 'Medicine already exists — added to bill.',
                ]);
            }

            $medicine = Medicine::create([
                'restaurant_id' => $restaurant->id,
                'name' => $name,
                'sku' => $barcode,
                'barcode' => $barcode,
                'track_stock' => true,
                'min_stock' => 0,
            ]);

            // Create an opening batch so POS can sell it
            $batch = null;
            if (class_exists(\App\Models\MedicineBatch::class)) {
                $batch = \App\Models\MedicineBatch::create([
                    'medicine_id' => $medicine->id,
                    'restaurant_id' => $restaurant->id,
                    'batch_number' => 'QUICK-' . now()->format('YmdHis'),
                    'selling_price' => $price,
                    'purchase_price' => $price,
                    'quantity' => 1000,
                    'expiry_date' => now()->addYears(2)->toDateString(),
                ]);
            }

            return response()->json([
                'ok' => true,
                'created' => true,
                'line' => [
                    'type' => $batch ? 'medicine_batch' : 'menu_item',
                    'id' => $batch ? $batch->id : $medicine->id,
                    'name' => $medicine->name . ($batch ? ' — Batch ' . $batch->batch_number : ''),
                    'price' => $price,
                    'stock' => $batch ? (int) $batch->quantity : null,
                ],
                'message' => 'New medicine registered and added to bill.',
            ]);
        }

        // Retail / restaurant menu item
        $category = \App\Models\Category::query()
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn((new \App\Models\Category)->getTable(), 'restaurant_id'),
                fn ($q) => $q->where('restaurant_id', $restaurant->id)
            )
            ->orderBy('id')
            ->first();

        if (! $category) {
            $category = \App\Models\Category::create(array_filter([
                'restaurant_id' => \Illuminate\Support\Facades\Schema::hasColumn((new \App\Models\Category)->getTable(), 'restaurant_id') ? $restaurant->id : null,
                'name' => 'General',
                'slug' => 'general-' . $restaurant->id,
                'is_active' => true,
                'sort_order' => 0,
            ], fn ($v) => $v !== null));
        }

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => $name,
            'barcode' => $barcode,
            'sku' => $barcode,
            'price' => $price,
            'is_available' => true,
            'track_stock' => false,
            'stock_quantity' => 0,
            'has_sizes' => false,
            'has_variants' => false,
            'allows_toppings' => false,
        ]);

        return response()->json([
            'ok' => true,
            'created' => true,
            'line' => [
                'type' => 'menu_item',
                'id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'stock' => null,
            ],
            'message' => 'New product registered and added to bill.',
        ]);
    }

}
