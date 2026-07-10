<?php

namespace App\Providers;

use App\Models\Feedback;
use App\Policies\FeedbackPolicy;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(Feedback::class, FeedbackPolicy::class);
    }
}
