<?php

namespace App\Http\Controllers\Admin;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockAdjustment;
use App\Models\PurchaseHeader;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\InventoryAuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class MedicalReportController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurant;

        return view('admin.medical.reports.index', [
            'restaurant' => $restaurant,
        ]);
    }

    public function topMedicines(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $days = $request->get('days', 30);

        $topMedicines = StockAdjustment::where('restaurant_id', $restaurant->id)
            ->where('reason', 'sale')
            ->whereBetween('created_at', [now()->subDays($days), now()])
            ->groupBy('notes')
            ->selectRaw('notes as medicine_name, SUM(ABS(change_quantity)) as total_sold')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return view('admin.medical.reports.top-medicines', [
            'topMedicines' => $topMedicines,
            'days' => $days,
            'restaurant' => $restaurant,
        ]);
    }

    public function expiryAnalysis(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $batches = MedicineBatch::where('restaurant_id', $restaurant->id)
            ->with('medicine')
            ->get()
            ->groupBy(function ($batch) {
                $expiryDate = $batch->expiry_date ? Carbon::parse($batch->expiry_date) : null;

                if (!$expiryDate) {
                    return 'good';
                }

                if ($expiryDate->isPast()) {
                    return 'expired';
                } elseif ($expiryDate->diffInDays(now()) <= 30) {
                    return 'expiring_soon';
                } elseif ($expiryDate->diffInDays(now()) <= 90) {
                    return 'expiring_within_90';
                } else {
                    return 'good';
                }
            });

        return view('admin.medical.reports.expiry-analysis', [
            'batches' => $batches,
            'restaurant' => $restaurant,
        ]);
    }

    public function supplierPerformance(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $days = $request->get('days', 90);

        $suppliers = Supplier::where('restaurant_id', $restaurant->id)
            ->with(['purchaseHeaders' => function ($query) use ($days) {
                $query->whereBetween('created_at', [now()->subDays($days), now()]);
            }])
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'orders' => $supplier->purchaseHeaders->count(),
                    'total_value' => $supplier->purchaseHeaders->sum('total'),
                    'avg_delivery_days' => $supplier->average_delivery_days,
                    'payment_terms' => $supplier->payment_terms,
                ];
            });

        return view('admin.medical.reports.supplier-performance', [
            'suppliers' => $suppliers,
            'days' => $days,
            'restaurant' => $restaurant,
        ]);
    }

    public function marginAnalysis(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $days = $request->get('days', 30);

        // Get medicines sold in last X days with profit margins
        $medicines = Medicine::where('restaurant_id', $restaurant->id)
            ->with(['batches' => function ($q) use ($days) {
                $q->whereBetween('created_at', [now()->subDays($days), now()]);
            }])
            ->get()
            ->map(function ($medicine) {
                $totalCost = $medicine->batches->sum(function ($batch) {
                    return $batch->quantity * $batch->purchase_price;
                });

                $totalRevenue = $medicine->batches->sum(function ($batch) {
                    return $batch->quantity * $batch->selling_price;
                });

                $margin = $totalRevenue - $totalCost;
                $marginPercent = $totalRevenue > 0 ? ($margin / $totalRevenue) * 100 : 0;

                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'total_cost' => $totalCost,
                    'total_revenue' => $totalRevenue,
                    'margin' => $margin,
                    'margin_percent' => $marginPercent,
                ];
            })
            ->filter(fn($m) => $m['total_revenue'] > 0)
            ->sortByDesc('margin');

        return view('admin.medical.reports.margin-analysis', [
            'medicines' => $medicines,
            'days' => $days,
            'restaurant' => $restaurant,
        ]);
    }

    public function revenueTrends(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $days = $request->get('days', 30);

        // Daily revenue for last X days
        $dailyRevenue = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [now()->subDays($days), now()])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as order_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.medical.reports.revenue-trends', [
            'dailyRevenue' => $dailyRevenue,
            'days' => $days,
            'restaurant' => $restaurant,
        ]);
    }

    public function inventoryAuditTrail(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $itemType = $request->get('item_type');
        
        $query = InventoryAuditLog::where('restaurant_id', $restaurant->id)
            ->with('user');

        if ($itemType) {
            $query->where('item_type', $itemType);
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('admin.medical.reports.inventory-audit-trail', [
            'auditLogs' => $auditLogs,
            'itemType' => $itemType,
            'restaurant' => $restaurant,
        ]);
    }
}

