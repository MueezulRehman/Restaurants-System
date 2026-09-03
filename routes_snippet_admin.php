<?php
/**
 * PASTE inside: Route::prefix('admin')->name('admin.')->group(function () { ... })
 * Right after the restaurants routes is ideal.
 *
 * Also add these use statements at the top of routes/web.php:
 *
 * use App\Http\Controllers\Admin\PlatformSettingsController;
 * use App\Http\Controllers\Admin\BusinessReportController;
 * use App\Http\Controllers\Admin\PaymentReminderController;
 */

        // Codeibex SaaS — platform bank, reports, payment reminders
        Route::get('/platform/settings', [PlatformSettingsController::class, 'edit'])->name('platform.settings');
        Route::put('/platform/settings', [PlatformSettingsController::class, 'update'])->name('platform.settings.update');

        Route::get('/reports/businesses', [BusinessReportController::class, 'index'])->name('reports.businesses');
        Route::get('/reports/businesses/{restaurant}', [BusinessReportController::class, 'show'])->name('reports.businesses.show');

        Route::post('/restaurants/{restaurant}/payment-reminder', [PaymentReminderController::class, 'store'])->name('restaurants.payment-reminder');
