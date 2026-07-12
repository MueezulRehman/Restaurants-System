<?php

namespace App\Http\Controllers\Admin;

use App\Models\Report;
use App\Services\ReportGenerator;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Show reports dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        abort_unless($user instanceof \App\Models\User, 403);

        $restaurantId = $user->restaurant_id ?? $user->effectiveRestaurantId();
        $availableTypes = array_keys($user->getAvailableReportTypes());

        $reports = Report::where('restaurant_id', $restaurantId)
            ->when(! empty($availableTypes), function ($query) use ($availableTypes) {
                $query->whereIn('type', $availableTypes);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Show report generation form.
     */
    public function create()
    {
        $user = auth()->user();
        abort_unless($user instanceof \App\Models\User, 403);

        $types = $user->getAvailableReportTypes();

        return view('admin.reports.create', compact('types'));
    }

    /**
     * Generate and store a report.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user instanceof \App\Models\User, 403);

        $validated = $request->validate([
            'type' => 'required|in:orders,sales,inventory,financial,staff,delivery',
            'name' => 'required|string|max:255',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
        ]);

        abort_unless($user->canGenerateReportType($validated['type']), 403);

        $restaurantId = $user->restaurant_id ?? $user->effectiveRestaurantId();
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

        return redirect()->route('manager.reports.show', $report)
            ->with('success', 'Report generated successfully.');
    }

    /**
     * Show a single report.
     */
    public function show(Report $report)
    {
        abort_unless(Gate::forUser(Auth::user())->check('view', $report), 403);

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Delete a report.
     */
    public function destroy(Report $report)
    {
        abort_unless(Gate::forUser(Auth::user())->check('delete', $report), 403);

        $report->delete();

        return redirect()->route('manager.reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    /**
     * Export report as PDF.
     */
    public function exportPdf(Report $report)
    {
        abort_unless(Gate::forUser(Auth::user())->check('view', $report), 403);

        // TODO: Integrate PDF export using barryvdh/laravel-dompdf or spatie/laravel-pdf
        // For now, return a placeholder response
        return response()->download(storage_path('app/reports/' . $report->id . '.pdf'));
    }

    /**
     * Export report as Excel.
     */
    public function exportExcel(Report $report)
    {
        abort_unless(Gate::forUser(Auth::user())->check('view', $report), 403);

        // TODO: Integrate Excel export using maatwebsite/excel
        // For now, return a placeholder response
        return response()->download(storage_path('app/reports/' . $report->id . '.xlsx'));
    }
}
