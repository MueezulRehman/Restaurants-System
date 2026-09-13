<?php

namespace App\Providers;

use App\Models\Feedback;
use App\Models\Report;
use App\Contracts\NotificationProvider;
use App\Policies\FeedbackPolicy;
use App\Policies\ReportPolicy;
use App\Services\ModuleService;
use App\Services\LogNotificationProvider;
use App\View\Composers\CeoLayoutComposer;
use App\View\Composers\CustomerLayoutComposer;
use App\View\Composers\DashboardLayoutComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The only bundled driver is deliberately log-only until a real provider is approved.
        $this->app->singleton(NotificationProvider::class, LogNotificationProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // view()->composer('*', function ($view) {
        //     $view->with('currentRestaurant', app('restaurant'));
        // });
        view()->composer('*', function ($view) {
            $view->with('currentRestaurant', app()->bound('restaurant') ? app('restaurant') : null);
        });
        view()->composer([
            'manager.layout.master',
            'super-admin.layout.master',
        ], DashboardLayoutComposer::class);
        view()->composer('customer.layout.master', CustomerLayoutComposer::class);
        view()->composer('ceo.layout.master', CeoLayoutComposer::class);

        if (Schema::hasTable('modules') && Schema::hasTable('business_types')) {
            ModuleService::ensureDefaults();
        }

        Gate::policy(Feedback::class, FeedbackPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
    }
}