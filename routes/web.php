<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\VariantAttributeController;
use App\Http\Controllers\Admin\BusinessTypeController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\RestaurantSubscriptionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Customer\FeedbackController as CustomerFeedbackController;
use App\Http\Controllers\Admin\CashbookController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\RestaurantProfileController;
use App\Http\Controllers\Admin\ManagerAuthController;
use App\Http\Controllers\Admin\ManagerDashboardController;
use App\Http\Middleware\EnsureRestaurantManager;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderTrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home — redirects to Taste Hut menu or shows first restaurant
|--------------------------------------------------------------------------
*/
Route::get('/', [MenuController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Customer order tracking
|--------------------------------------------------------------------------
*/
Route::get('/track/{order:tracking_token}', [OrderTrackingController::class, 'show'])->name('orders.track');
Route::get('/track', fn () => view('customer.lookup'))->name('orders.lookup.form');
Route::post('/track/lookup', [OrderTrackingController::class, 'lookup'])->name('orders.lookup');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

/*
|--------------------------------------------------------------------------
| Customer accounts (optional — guest checkout always still works)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:customer')->group(function () {
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.attempt');
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.login.attempt');
});

Route::middleware('auth:customer')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
    Route::get('/account', [CustomerDashboardController::class, 'index'])->name('account.dashboard');

    // Customer Feedback
    Route::get('/feedback', [CustomerFeedbackController::class, 'index'])->name('customer.feedback.index');
    Route::get('/feedback/create', [CustomerFeedbackController::class, 'create'])->name('customer.feedback.create');
    Route::post('/feedback', [CustomerFeedbackController::class, 'store'])->name('customer.feedback.store');
    Route::get('/feedback/{feedback}', [CustomerFeedbackController::class, 'show'])->name('customer.feedback.show');
});

/*
|--------------------------------------------------------------------------
| Super Admin panel
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware(['auth', EnsureSuperAdmin::class])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Platform feedback
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
        Route::post('/feedback/{feedback}/reply', [AdminFeedbackController::class, 'reply'])->name('feedback.reply');
        Route::patch('/feedback/{feedback}/status', [AdminFeedbackController::class, 'updateStatus'])->name('feedback.update-status');
        Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');

        // Business Types (super admin only)
        Route::resource('/business-types', BusinessTypeController::class)->except(['show']);

        // Modules (super admin only)
        Route::resource('/modules', ModuleController::class)->except(['show']);

        // Subscription Plans (super admin only)
        Route::resource('/subscription-plans', SubscriptionPlanController::class)->except(['show']);

        // Restaurants CRUD (super admin only)
        Route::resource('/restaurants', RestaurantController::class)->except(['show']);
    });
});

/*
|--------------------------------------------------------------------------
| Restaurant Manager panel (separate login at /manager/login)
|--------------------------------------------------------------------------
*/
Route::prefix('manager')->name('manager.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', [ManagerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [ManagerAuthController::class, 'login'])->name('login.attempt');
    });

    Route::get('/subscription-expired', [ManagerDashboardController::class, 'subscriptionExpired'])->name('subscription.expired');

    Route::middleware(['auth', EnsureRestaurantManager::class, EnsureSubscriptionActive::class])->group(function () {
        Route::post('/logout', [ManagerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

        // Manager sees their own restaurant's data via admin routes scoped to their restaurant_id
        Route::get('/restaurant/profile', [RestaurantProfileController::class, 'edit'])->name('restaurant.profile.edit');
        Route::patch('/restaurant/profile', [RestaurantProfileController::class, 'update'])->name('restaurant.profile.update');

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        // Categories
        Route::resource('/categories', CategoryController::class)->except(['show']);

        // Menu Items
        Route::resource('/menu-items', MenuItemController::class)->except(['show'])
            ->parameters(['menu-items' => 'item']);

        // Product Variants
        Route::get('/menu-items/{item}/variants', [ProductVariantController::class, 'index'])->name('menu-items.variants.index');
        Route::get('/menu-items/{item}/variants/create', [ProductVariantController::class, 'create'])->name('menu-items.variants.create');
        Route::post('/menu-items/{item}/variants', [ProductVariantController::class, 'store'])->name('menu-items.variants.store');
        Route::get('/menu-items/{item}/variants/{variant}/edit', [ProductVariantController::class, 'edit'])->name('menu-items.variants.edit');
        Route::patch('/menu-items/{item}/variants/{variant}', [ProductVariantController::class, 'update'])->name('menu-items.variants.update');
        Route::delete('/menu-items/{item}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('menu-items.variants.destroy');

        // Variant Attributes
        Route::get('/menu-items/{item}/attributes', [VariantAttributeController::class, 'index'])->name('menu-items.attributes.index');
        Route::get('/menu-items/{item}/attributes/create', [VariantAttributeController::class, 'create'])->name('menu-items.attributes.create');
        Route::post('/menu-items/{item}/attributes', [VariantAttributeController::class, 'store'])->name('menu-items.attributes.store');
        Route::get('/menu-items/{item}/attributes/{attribute}/edit', [VariantAttributeController::class, 'edit'])->name('menu-items.attributes.edit');
        Route::patch('/menu-items/{item}/attributes/{attribute}', [VariantAttributeController::class, 'update'])->name('menu-items.attributes.update');
        Route::delete('/menu-items/{item}/attributes/{attribute}', [VariantAttributeController::class, 'destroy'])->name('menu-items.attributes.destroy');

        // Deals
        Route::resource('/deals', DealController::class)->except(['show']);

        // Cashbook
        Route::get('/cashbook', [CashbookController::class, 'index'])->name('cashbook.index');
        Route::get('/cashbook/create', [CashbookController::class, 'create'])->name('cashbook.create');
        Route::post('/cashbook', [CashbookController::class, 'store'])->name('cashbook.store');
        Route::delete('/cashbook/{cashbook}', [CashbookController::class, 'destroy'])->name('cashbook.destroy');

        // Expenses
        Route::resource('/expenses', ExpenseController::class)->except(['show']);

        // Staff
        Route::resource('/staff', StaffController::class)->except(['show'])
            ->parameters(['staff' => 'staff']);

        // Attendance
        Route::resource('/attendance', AttendanceController::class)->except(['show']);

        // Salary
        Route::resource('/salary', SalaryController::class)->except(['show']);

        // Reports
        Route::resource('/reports', ReportController::class)->except(['edit', 'update']);
        Route::get('/reports/{report}/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/{report}/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');

        // Feedback
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
        Route::post('/feedback/{feedback}/reply', [AdminFeedbackController::class, 'reply'])->name('feedback.reply');
        Route::patch('/feedback/{feedback}/status', [AdminFeedbackController::class, 'updateStatus'])->name('feedback.update-status');
        Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');



        // Restaurant Subscriptions (for restaurant admins to view their subscription)
        Route::get('/subscription', [RestaurantSubscriptionController::class, 'show'])->name('subscription.show');
        Route::post('/subscription/cancel', [RestaurantSubscriptionController::class, 'cancel'])->name('subscription.cancel');
        Route::post('/subscription/reactivate', [RestaurantSubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
    });
});

/*
|--------------------------------------------------------------------------
| Per-restaurant public menu pages  /{slug}
| MUST be last so it doesn't swallow /admin, /manager, /track etc.
|--------------------------------------------------------------------------
*/
Route::get('/{slug}', [MenuController::class, 'showBySlug'])
    ->name('menu.restaurant')
    ->where('slug', '^(?!admin|manager|track|checkout|register|login|logout|account|_debugbar)[a-z0-9\-]+$');
