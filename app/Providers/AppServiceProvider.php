<?php

namespace App\Providers;

use App\Models\Feedback;
use App\Models\Report;
use App\Contracts\NotificationProvider;
use App\Policies\FeedbackPolicy;
use App\Policies\ReportPolicy;
use App\Services\ModuleService;
use App\Services\LogNotificationProvider;
use App\Services\TwilioNotificationProvider;
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
        $this->app->singleton(NotificationProvider::class, function () {
            return match (config('services.medical_queue_notifications.driver', 'log')) {
                'twilio' => app(TwilioNotificationProvider::class),
                'log' => app(LogNotificationProvider::class),
                default => throw new \RuntimeException('Unsupported medical queue notification driver.'),
            };
        });
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