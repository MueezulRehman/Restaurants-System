<?php

namespace App\Providers;

use App\Models\Feedback;
use App\Models\Report;
use App\Policies\FeedbackPolicy;
use App\Policies\ReportPolicy;
use App\Services\ModuleService;
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
        //
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

        if (Schema::hasTable('modules') && Schema::hasTable('business_types')) {
            ModuleService::ensureDefaults();
        }

        Gate::policy(Feedback::class, FeedbackPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
    }
}
