<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\CashbookController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\RestaurantProfileController;
use App\Http\Controllers\Admin\ManagerAuthController;
use App\Http\Controllers\Admin\ManagerDashboardController;
use App\Http\Controllers\CheckoutController;
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
| Super Admin panel
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        // Categories
        Route::resource('/categories', CategoryController::class)->except(['show']);

        // Menu Items
        Route::resource('/menu-items', MenuItemController::class)->except(['show'])
            ->parameters(['menu-items' => 'item']);

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

        // Restaurant profile (for restaurant admins)
        Route::get('/restaurant/profile', [RestaurantProfileController::class, 'edit'])->name('restaurant.profile.edit');
        Route::patch('/restaurant/profile', [RestaurantProfileController::class, 'update'])->name('restaurant.profile.update');

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

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [ManagerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

        // Manager sees their own restaurant's data via admin routes scoped to their restaurant_id
        Route::get('/restaurant/profile', [RestaurantProfileController::class, 'edit'])->name('restaurant.profile.edit');
        Route::patch('/restaurant/profile', [RestaurantProfileController::class, 'update'])->name('restaurant.profile.update');
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
    ->where('slug', '^(?!admin|manager|track|checkout|_debugbar)[a-z0-9\-]+$');
