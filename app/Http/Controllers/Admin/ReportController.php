<?php

namespace App\Http\Controllers\Admin;

use App\Models\Report;
use App\Services\ReportGenerator;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show reports dashboard.
     */
    public function index()
    {
        $restaurantId = auth()->user()->restaurant_id;
        $reports = Report::where('restaurant_id', $restaurantId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Show report generation form.
     */
    public function create()
    {
        $types = ['orders' => 'Orders', 'sales' => 'Sales', 'inventory' => 'Inventory', 'financial' => 'Financial', 'staff' => 'Staff', 'delivery' => 'Delivery'];
        return view('admin.reports.create', compact('types'));
    }

    /**
     * Generate and store a report.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:orders,sales,inventory,financial,staff,delivery',
            'name' => 'required|string|max:255',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
        ]);

        $restaurantId = auth()->user()->restaurant_id;
        $dateFrom = $validated['date_from'] ? Carbon::parse($validated['date_from'])->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $dateTo = $validated['date_to'] ? Carbon::parse($validated['date_to'])->endOfDay() : Carbon::now()->endOfDay();

        // Generate report data based on type
        $data = match ($validated['type']) {
            'orders' => ReportGenerator::generateOrdersReport($restaurantId, $dateFrom, $dateTo),
            'sales' => ReportGenerator::generateSalesReport($restaurantId, $dateFrom, $dateTo),
            'inventory' => ReportGenerator::generateInventoryReport($restaurantId),
            'financial' => ReportGenerator::generateFinancialReport($restaurantId, $dateFrom, $dateTo),
            default => [],
        };

        // Store report
        $report = Report::create([
            'restaurant_id' => $restaurantId,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'name' => $validated['name'],
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
            'data_snapshot' => $data,
            'generated_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.reports.show', $report)
            ->with('success', 'Report generated successfully.');
    }

    /**
     * Show a single report.
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Delete a report.
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        $report->delete();

        return redirect()->route('admin.reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    /**
     * Export report as PDF.
     */
    public function exportPdf(Report $report)
    {
        $this->authorize('view', $report);

        // TODO: Integrate PDF export using barryvdh/laravel-dompdf or spatie/laravel-pdf
        // For now, return a placeholder response
        return response()->download(storage_path('app/reports/' . $report->id . '.pdf'));
    }

    /**
     * Export report as Excel.
     */
    public function exportExcel(Report $report)
    {
        $this->authorize('view', $report);

        // TODO: Integrate Excel export using maatwebsite/excel
        // For now, return a placeholder response
        return response()->download(storage_path('app/reports/' . $report->id . '.xlsx'));
    }
}
