<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ManagerFeedbackController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StockAnalysisController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');

    Route::get('feedback', [ManagerFeedbackController::class, 'index'])->name('feedback.index');
    Route::post('feedback', [ManagerFeedbackController::class, 'store'])->name('feedback.store');
    Route::get('feedback/{feedback}', [ManagerFeedbackController::class, 'show'])->name('feedback.show');

    Route::get('stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock.adjustments.index');
    Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock.adjustments.store');

    // Stock Analysis Routes for Manager
    Route::get('stock-analysis', [StockAnalysisController::class, 'managerIndex'])->name('stock-analysis.index');
});

// Admin Stock Analysis Routes (Super Admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('stock-analysis', [StockAnalysisController::class, 'adminIndex'])->name('stock-analysis.index');
    Route::get('stock-analysis/export', [StockAnalysisController::class, 'adminExport'])->name('stock-analysis.export');
});
