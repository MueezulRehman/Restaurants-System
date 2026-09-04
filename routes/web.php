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
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ToppingController;
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
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\ManagerFeedbackController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\PlatformSettingsController;
use App\Http\Controllers\Admin\StockAnalysisController;
use App\Http\Middleware\EnsureRestaurantManager;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\AuthenticateManager;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderTrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CodeIbex platform homepage; business storefronts use /{slug}
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Customer order tracking
|--------------------------------------------------------------------------
*/
Route::get('/track/{order:tracking_token}', [OrderTrackingController::class, 'show'])->name('orders.track');
Route::get('/track', fn() => view('customer.lookup'))->name('orders.lookup.form');
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

    Route::middleware([AuthenticateAdmin::class, EnsureSuperAdmin::class])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Stock analysis
        Route::get('/stock-analysis', [StockAnalysisController::class, 'adminIndex'])->name('stock-analysis.index');
        Route::get('/stock-analysis/export', [StockAnalysisController::class, 'adminExport'])->name('stock-analysis.export');

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
        Route::post('/restaurants/{restaurant}/enter', [RestaurantController::class, 'enter'])->name('restaurants.enter');
        Route::post('/restaurants/exit', [RestaurantController::class, 'exit'])->name('restaurants.exit');

        // My Account — super admin's own login name/email/phone/password
        Route::get('/account', [App\Http\Controllers\Admin\AccountController::class, 'edit'])->name('account.edit');
        Route::patch('/account', [App\Http\Controllers\Admin\AccountController::class, 'update'])->name('account.update');
        Route::patch('/account/password', [App\Http\Controllers\Admin\AccountController::class, 'updatePassword'])->name('account.password');
        Route::get('/platform-settings', [PlatformSettingsController::class, 'edit'])->name('platform.settings');
        Route::put('/platform-settings', [PlatformSettingsController::class, 'update'])->name('platform.settings.update');
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

    Route::middleware([AuthenticateManager::class, EnsureRestaurantManager::class, EnsureSubscriptionActive::class])->group(function () {
        Route::post('/logout', [ManagerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

        // Stock analysis
        Route::get('/stock-analysis', [StockAnalysisController::class, 'managerIndex'])->name('stock-analysis.index');

        // Manager sees their own restaurant's data via admin routes scoped to their restaurant_id
        Route::get('/restaurant/profile', [RestaurantProfileController::class, 'edit'])->name('restaurant.profile.edit');
        Route::patch('/restaurant/profile', [RestaurantProfileController::class, 'update'])->name('restaurant.profile.update');

        // My Account — manager's own login name/email/phone/password
        Route::get('/account', [App\Http\Controllers\Admin\AccountController::class, 'edit'])->name('account.edit');
        Route::patch('/account', [App\Http\Controllers\Admin\AccountController::class, 'update'])->name('account.update');
        Route::patch('/account/password', [App\Http\Controllers\Admin\AccountController::class, 'updatePassword'])->name('account.password');

        // Orders
        Route::middleware('module:orders')->group(function () {
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        });

        Route::middleware('module:delivery')->group(function () {
            Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
            Route::patch('/deliveries/{delivery}', [DeliveryController::class, 'update'])->name('deliveries.update');
        });

        // POS — Restaurant / Retail / Medical Store, view + logic switch on
        // business type automatically (see Restaurant::getPosMode()).
        Route::middleware('module:pos')->group(function () {
            Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
            Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
            Route::get('/pos/lookup', [PosController::class, 'lookup'])->name('pos.lookup');
            Route::get('/barcode-lookup', [\App\Http\Controllers\Admin\BarcodeLookupController::class, 'lookup'])->name('barcode.lookup');
            Route::post('/barcode-quick-store', [\App\Http\Controllers\Admin\BarcodeLookupController::class, 'quickStore'])->name('barcode.quick');
            Route::get('/pos/receipt/{order}', [PosController::class, 'receipt'])->name('pos.receipt');
            Route::get('/sales', [PosController::class, 'sales'])->name('sales.index');
        });

        // Categories — a manager needs the "categories" module grant to do
        // any category CRUD.
        Route::middleware('module:categories')->group(function () {
            Route::resource('/categories', CategoryController::class)->except(['show']);
        });

        // Menu Items + their variants/attributes — a manager needs the
        // "menu" module grant to do full CRUD here (create, edit, delete
        // menu items, sizes, variants and attributes).
        Route::middleware('module:menu')->group(function () {
            Route::resource('/menu-items', MenuItemController::class)->except(['show'])
                ->parameters(['menu-items' => 'item']);

            Route::resource('/toppings', ToppingController::class)->except(['show']);

            // Tables management for dine-in/table orders
            Route::middleware('module:tables')->group(function () {
                Route::resource('/tables', App\Http\Controllers\Admin\TableController::class)->except(['show']);
            });

            Route::middleware('module:variants')->group(function () {
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
            });
        });

        // Deals
        Route::middleware('module:deals')->group(function () {
            Route::resource('/deals', DealController::class)->except(['show']);
        });

        // Customers
        Route::middleware('module:customers')->group(function () {
            Route::resource('/customers', CustomerController::class)->only(['index', 'show', 'store']);
            Route::post('/customers/{customer}/remind', [CustomerController::class, 'remind'])->name('customers.remind');
            Route::post('/customers/{customer}/payment', [CustomerController::class, 'recordPayment'])->name('customers.payment');
            Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
            Route::post('/customers/{customer}/statement/email', [CustomerController::class, 'emailStatement'])->name('customers.statement.email');
            Route::post('/customers/{customer}/orders/{order}/email-receipt', [CustomerController::class, 'emailReceipt'])->name('customers.receipt.email');
        });

        // Medicines (medical module)
        Route::middleware('module:medical')->group(function () {
            Route::resource('/medicines', App\Http\Controllers\Admin\MedicineController::class)->except(['show']);
            Route::get('/purchases', [App\Http\Controllers\Admin\PurchaseController::class, 'index'])->name('purchases.index');
            Route::get('/purchases/create', [App\Http\Controllers\Admin\PurchaseController::class, 'create'])->name('purchases.create');
            Route::post('/purchases', [App\Http\Controllers\Admin\PurchaseController::class, 'store'])->name('purchases.store');
            Route::resource('/suppliers', App\Http\Controllers\Admin\SupplierController::class)->except(['show']);
            Route::resource('/prescriptions', App\Http\Controllers\Admin\PrescriptionController::class)->except(['edit', 'update', 'delete']);
            Route::resource('/batch-recalls', App\Http\Controllers\Admin\BatchRecallController::class)->except(['edit', 'update']);
            Route::resource('/customer-allergies', App\Http\Controllers\Admin\CustomerAllergyController::class)->except(['show']);
            Route::resource('/medicine-interactions', App\Http\Controllers\Admin\MedicineInteractionController::class)->except(['show']);
            Route::get('/medical-records', [App\Http\Controllers\Admin\MedicalRecordController::class, 'index'])->name('medical-records.index');
            Route::post('/medical-records', [App\Http\Controllers\Admin\MedicalRecordController::class, 'store'])->name('medical-records.store');
            Route::get('/medical-reports', [App\Http\Controllers\Admin\MedicalReportController::class, 'index'])->name('medical-reports.index');
            Route::get('/medical-reports/top-medicines', [App\Http\Controllers\Admin\MedicalReportController::class, 'topMedicines'])->name('medical-reports.top-medicines');
            Route::get('/medical-reports/expiry-analysis', [App\Http\Controllers\Admin\MedicalReportController::class, 'expiryAnalysis'])->name('medical-reports.expiry-analysis');
            Route::get('/medical-reports/supplier-performance', [App\Http\Controllers\Admin\MedicalReportController::class, 'supplierPerformance'])->name('medical-reports.supplier-performance');
            Route::get('/medical-reports/margin-analysis', [App\Http\Controllers\Admin\MedicalReportController::class, 'marginAnalysis'])->name('medical-reports.margin-analysis');
            Route::get('/medical-reports/revenue-trends', [App\Http\Controllers\Admin\MedicalReportController::class, 'revenueTrends'])->name('medical-reports.revenue-trends');
            Route::get('/medical-reports/inventory-audit-trail', [App\Http\Controllers\Admin\MedicalReportController::class, 'inventoryAuditTrail'])->name('medical-reports.inventory-audit-trail');
        });

        // Cashbook
        Route::middleware('module:cashbook')->group(function () {
            Route::get('/cashbook', [CashbookController::class, 'index'])->name('cashbook.index');
            Route::get('/cashbook/create', [CashbookController::class, 'create'])->name('cashbook.create');
            Route::post('/cashbook', [CashbookController::class, 'store'])->name('cashbook.store');
            Route::delete('/cashbook/{cashbook}', [CashbookController::class, 'destroy'])->name('cashbook.destroy');
        });

        // Expenses
        Route::middleware('module:expenses')->group(function () {
            Route::resource('/expenses', ExpenseController::class)->except(['show']);
        });

        // HR module: staff, attendance and payroll management.
        Route::middleware('module:hr')->group(function () {
            // Staff — admin/owner only. Managing staff accounts (and granting
            // them module access below) is deliberately NOT something a
            // manager can do to themselves or to each other.
            Route::middleware('restaurant.admin')->group(function () {
                Route::resource('/staff', StaffController::class)->except(['show'])
                    ->parameters(['staff' => 'staff']);
            });

            // Attendance
            Route::middleware('module:attendance')->group(function () {
                Route::resource('/attendance', AttendanceController::class)->except(['show']);
            });

            // Salary
            Route::middleware('module:salary')->group(function () {
                Route::resource('/salary', SalaryController::class)->except(['show']);
            });
        });

        // Reports
        Route::middleware('module:reports')->group(function () {
            Route::resource('/reports', ReportController::class)->except(['edit', 'update']);
            Route::get('/reports/{report}/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
            Route::get('/reports/{report}/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        });

        // Stock Management (available in all modes: restaurant, retail, medical)
        Route::middleware('module:stock')->group(function () {
            Route::get('/stock', [App\Http\Controllers\Admin\StockController::class, 'index'])->name('stock.index');
            Route::post('/stock/adjust', [App\Http\Controllers\Admin\StockController::class, 'adjust'])->name('stock.adjust');
            Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock.adjustments.index');
            Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock.adjustments.store');
        });

        // Feedback
        Route::middleware('module:feedback')->group(function () {
            Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
            Route::get('/feedback/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
            Route::post('/feedback/{feedback}/reply', [AdminFeedbackController::class, 'reply'])->name('feedback.reply');
            Route::patch('/feedback/{feedback}/status', [AdminFeedbackController::class, 'updateStatus'])->name('feedback.update-status');
            Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');
            Route::get('/manager-feedback', [ManagerFeedbackController::class, 'index'])->name('manager.feedback.index');
            Route::post('/manager-feedback', [ManagerFeedbackController::class, 'store'])->name('manager.feedback.store');
            Route::get('/manager-feedback/{feedback}', [ManagerFeedbackController::class, 'show'])->name('manager.feedback.show');
        });



        // Restaurant Subscriptions (for restaurant admins to view and pay their subscription)
        Route::get('/subscription', [RestaurantSubscriptionController::class, 'show'])->name('subscription.show');
        Route::post('/subscription/pay', [RestaurantSubscriptionController::class, 'pay'])->name('subscription.pay');
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
