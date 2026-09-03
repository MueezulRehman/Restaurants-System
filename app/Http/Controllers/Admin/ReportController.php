<?php

namespace App\Http\Controllers\Admin;

use App\Models\Report;
use App\Services\ReportService;
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
        // On tenant DB, users table may not contain the central auth user id.
        // Avoid FK violation: only set user_id when that user exists on current connection.
        $userId = auth()->id();
        try {
            $exists = \App\Models\User::query()->whereKey($userId)->exists();
            if (! $exists) {
                $userId = null;
            }
        } catch (\Throwable $e) {
            $userId = null;
        }

        $report = Report::create([
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
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

        $data = is_array($report->data_snapshot) ? $report->data_snapshot : [];
        $html = view('admin.reports.pdf', compact('report', 'data'))->render();

        // Real PDF if Dompdf is installed
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            return $pdf->download('report-'.$report->id.'.pdf');
        }

        // Fallback: print-ready HTML (browser → Save as PDF)
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="report-'.$report->id.'.html"',
        ]);
    }

    /**
     * Export report as CSV (opens in Excel without extra packages).
     */
    public function exportExcel(Report $report)
    {
        abort_unless(Gate::forUser(Auth::user())->check('view', $report), 403);

        $data = is_array($report->data_snapshot) ? $report->data_snapshot : [];
        $filename = 'report-'.$report->id.'.csv';
        $callback = function () use ($report, $data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Report', $report->name]);
            fputcsv($out, ['Type', $report->type]);
            fputcsv($out, ['From', $report->filters['date_from'] ?? '']);
            fputcsv($out, ['To', $report->filters['date_to'] ?? '']);
            fputcsv($out, []);

            if (($report->type ?? '') === 'sales') {
                fputcsv($out, ['Total sales', $data['total_sales'] ?? 0]);
                fputcsv($out, ['Orders', $data['order_count'] ?? '']);
                fputcsv($out, ['Total qty', $data['total_quantity'] ?? 0]);
                fputcsv($out, []);
                fputcsv($out, ['Item', 'Quantity', 'Revenue']);
                foreach (($data['items_sold'] ?? []) as $key => $row) {
                    $name = is_array($row) ? ($row['name'] ?? $key) : $key;
                    $qty = is_array($row) ? ($row['quantity'] ?? 0) : 0;
                    $rev = is_array($row) ? ($row['revenue'] ?? 0) : 0;
                    fputcsv($out, [$name, $qty, $rev]);
                }
            } else {
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        fputcsv($out, [$k, json_encode($v)]);
                    } else {
                        fputcsv($out, [$k, $v]);
                    }
                }
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
