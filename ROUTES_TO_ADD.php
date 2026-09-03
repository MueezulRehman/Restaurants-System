<?php
/**
 * Add these routes inside your super-admin / admin route group in routes/web.php
 * (same middleware as admin.restaurants).
 *
 * use App\Http\Controllers\Admin\PlatformSettingsController;
 * use App\Http\Controllers\Admin\BusinessReportController;
 * use App\Http\Controllers\Admin\PaymentReminderController;
 */

// Platform bank settings
Route::get('/platform/settings', [PlatformSettingsController::class, 'edit'])->name('platform.settings');
Route::put('/platform/settings', [PlatformSettingsController::class, 'update'])->name('platform.settings.update');

// Per-business reports
Route::get('/reports/businesses', [BusinessReportController::class, 'index'])->name('reports.businesses');
Route::get('/reports/businesses/{restaurant}', [BusinessReportController::class, 'show'])->name('reports.businesses.show');

// Payment reminder to a business
Route::post('/restaurants/{restaurant}/payment-reminder', [PaymentReminderController::class, 'store'])->name('restaurants.payment-reminder');
