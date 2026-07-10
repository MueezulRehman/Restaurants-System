<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Customer;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $r = Restaurant::firstOrCreate(
            ['slug' => 'test-restaurant'],
            ['name' => 'Test Restaurant', 'email' => 'test@local', 'phone' => '000', 'status' => 'active']
        );

        $plan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Starter plan for test restaurants',
                'price_monthly' => 15,
                'price_yearly' => 150,
                'trial_days' => 14,
                'max_staff' => 5,
                'max_menu_items' => 100,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $r->forceFill(['plan' => $plan->slug])->save();

        RestaurantSubscription::updateOrCreate(
            ['restaurant_id' => $r->id],
            [
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => 'monthly',
                'status' => 'active',
                'auto_renew' => true,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]
        );

        Restaurant::whereDoesntHave('subscription')->get()->each(function (Restaurant $restaurant) use ($plan): void {
            RestaurantSubscription::create([
                'restaurant_id' => $restaurant->id,
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => 'monthly',
                'status' => 'active',
                'auto_renew' => true,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);
        });

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['phone' => '10000000001', 'name' => 'Admin User', 'role' => 'super_admin', 'password' => bcrypt('password'), 'restaurant_id' => $r->id]
        );

        User::updateOrCreate(
            ['email' => 'manager@example.com'],
            ['phone' => '10000000002', 'name' => 'Manager User', 'role' => 'manager', 'password' => bcrypt('password'), 'restaurant_id' => $r->id]
        );

        Customer::updateOrCreate(
            ['email' => 'customer@example.com'],
            ['phone' => '20000000001', 'name' => 'Customer User', 'password' => bcrypt('password')]
        );
    }
}
