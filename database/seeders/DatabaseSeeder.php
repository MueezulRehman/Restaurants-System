<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Deal;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Seeders\PakistaniGeneralStoreSeeder;
use Database\Seeders\PakistaniMedicineSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MedicineCategorySeeder::class,
            MedicineUnitSeeder::class,
            ManufacturerSeeder::class,
            MedicineSeeder::class,
            MedicineBatchSeeder::class,
        ]);
        // Modules + business types (Restaurant, Retail/Shop, Cafe/Bakery,
        // Medical Store, General Business) must exist before any restaurant
        // can be assigned one.
        ModuleService::seedDefaultModules();
        ModuleService::seedDefaultBusinessTypes();

        // Create a login-ready super admin user for the app.
        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'phone' => '1234567890',
            'name' => 'Super Admin',
            'role' => 'super_admin',
            'password' => bcrypt('password'),
        ]);

        $restaurantType = \App\Models\BusinessType::where('name', 'Restaurant')->first();
        $medicalStoreType = \App\Models\BusinessType::where('name', 'Medical Store')->first();
        $generalBusinessType = \App\Models\BusinessType::where('name', 'General Business')->first();

        $restaurant = Restaurant::firstOrCreate([
            'slug' => 'tastehut',
        ], [
            'name' => 'Taste Hut',
            'email' => 'tastehut@example.com',
            'phone' => '03123456789',
            'address' => '123 Taste Hut Lane, Food City',
            'custom_domain' => null,
            'plan' => 'basic',
            'status' => 'active',
            'business_type_id' => $restaurantType?->id,
        ]);

        $restaurant->forceFill([
            'business_type_id' => $restaurantType?->id,
        ])->save();

        // Two more demo businesses — a medicine catalog and a general
        // store catalog don't belong inside Taste Hut's restaurant menu;
        // each business type gets its own dedicated business so the
        // module system (medical/medical-records vs inventory/stock)
        // actually reflects what a real deployment would look like.
        $pharmacy = Restaurant::firstOrCreate([
            'slug' => 'city-pharmacy',
        ], [
            'name' => 'City Pharmacy',
            'email' => 'citypharmacy@example.com',
            'phone' => '03211234567',
            'address' => 'Main Boulevard, Lahore',
            'plan' => 'basic',
            'status' => 'active',
            'business_type_id' => $medicalStoreType?->id,
        ]);
        $pharmacy->forceFill(['business_type_id' => $medicalStoreType?->id])->save();

        $generalStore = Restaurant::firstOrCreate([
            'slug' => 'al-barkat-general-store',
        ], [
            'name' => 'Al-Barkat General Store',
            'email' => 'albarkat@example.com',
            'phone' => '03331234567',
            'address' => 'Model Town, Lahore',
            'plan' => 'basic',
            'status' => 'active',
            'business_type_id' => $generalBusinessType?->id,
        ]);
        $generalStore->forceFill(['business_type_id' => $generalBusinessType?->id])->save();

        User::updateOrCreate([
            'email' => 'manager@citypharmacy.test',
        ], [
            'phone' => '03211112222',
            'name' => 'City Pharmacy Manager',
            'role' => 'admin',
            'restaurant_id' => $pharmacy->id,
            'password' => bcrypt('password'),
        ]);

        User::updateOrCreate([
            'email' => 'manager@albarkat.test',
        ], [
            'phone' => '03331112222',
            'name' => 'Al-Barkat Manager',
            'role' => 'admin',
            'restaurant_id' => $generalStore->id,
            'password' => bcrypt('password'),
        ]);

        $plan = SubscriptionPlan::firstOrCreate([
            'slug' => 'starter',
        ], [
            'name' => 'Starter',
            'description' => 'Core POS, menu/inventory, customers, and reports',
            'price_monthly' => 15,
            'price_yearly' => 150,
            'trial_days' => 14,
            'max_staff' => 5,
            'max_menu_items' => 100,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SubscriptionPlan::firstOrCreate([
            'slug' => 'pro',
        ], [
            'name' => 'Pro',
            'description' => 'Everything in Starter plus HR, variants, deals, medical modules, and more',
            'price_monthly' => 39,
            'price_yearly' => 390,
            'trial_days' => 14,
            'max_staff' => 25,
            'max_menu_items' => 500,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        SubscriptionPlan::firstOrCreate([
            'slug' => 'enterprise',
        ], [
            'name' => 'Enterprise',
            'description' => 'All modules for the selected business type, higher limits',
            'price_monthly' => 99,
            'price_yearly' => 990,
            'trial_days' => 30,
            'max_staff' => 100,
            'max_menu_items' => 5000,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $restaurant->forceFill(['plan' => $plan->slug])->save();

        RestaurantSubscription::updateOrCreate([
            'restaurant_id' => $restaurant->id,
        ], [
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'auto_renew' => true,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

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

        User::updateOrCreate([
            'email' => 'manager@tastehut.test',
        ], [
            'phone' => '0987654321',
            'name' => 'Taste Hut Manager',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
        ]);

        $pizzaCategory = Category::firstOrCreate([
            'restaurant_id' => $restaurant->id,
            'slug' => 'pizza',
        ], [
            'name' => 'Pizza',
            'icon' => '🍕',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $burgersCategory = Category::firstOrCreate([
            'restaurant_id' => $restaurant->id,
            'slug' => 'burgers',
        ], [
            'name' => 'Burgers',
            'icon' => '🍔',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $drinksCategory = Category::firstOrCreate([
            'restaurant_id' => $restaurant->id,
            'slug' => 'drinks',
        ], [
            'name' => 'Drinks',
            'icon' => '🥤',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $pizzaCategory->id,
            'name' => 'Margherita Pizza',
            'description' => 'Classic tomato, mozzarella, and fresh basil.',
            'price' => 850,
            'is_available' => true,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $burgersCategory->id,
            'name' => 'Classic Beef Burger',
            'description' => 'Juicy beef patty with lettuce, tomato, and house sauce.',
            'price' => 650,
            'is_available' => true,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $drinksCategory->id,
            'name' => 'Fresh Lemonade',
            'description' => 'Chilled lemonade with mint and lemon slices.',
            'price' => 150,
            'is_available' => true,
        ]);

        Deal::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Taste Hut Combo',
            'deal_number' => 1,
            'price' => 1450,
            'description' => 'Pizza, burger, and drink combo for one.',
            'is_active' => true,
        ]);

        // --- Fast Food demo business ---
        $fastFoodType = \App\Models\BusinessType::where('name', 'Fast Food')->first();

        $fastFood = Restaurant::firstOrCreate([
            'slug' => 'karachi-broast',
        ], [
            'name' => 'Karachi Broast & Fries',
            'email' => 'karachibroast@example.com',
            'phone' => '03441234567',
            'address' => 'Tariq Road, Karachi',
            'plan' => 'basic',
            'status' => 'active',
            'business_type_id' => $fastFoodType?->id,
        ]);
        $fastFood->forceFill(['business_type_id' => $fastFoodType?->id])->save();

        RestaurantSubscription::updateOrCreate([
            'restaurant_id' => $fastFood->id,
        ], [
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'auto_renew' => true,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        User::updateOrCreate([
            'email' => 'manager@karachibroast.test',
        ], [
            'phone' => '03441112222',
            'name' => 'Karachi Broast Manager',
            'role' => 'admin',
            'restaurant_id' => $fastFood->id,
            'password' => bcrypt('password'),
        ]);

        $ffCategories = [
            'Broast' => ['icon' => '🍗', 'sort_order' => 1],
            'Burgers' => ['icon' => '🍔', 'sort_order' => 2],
            'Shawarma & Rolls' => ['icon' => '🌯', 'sort_order' => 3],
            'Fries & Sides' => ['icon' => '🍟', 'sort_order' => 4],
            'Drinks' => ['icon' => '🥤', 'sort_order' => 5],
        ];
        $ffCategoryModels = [];
        foreach ($ffCategories as $name => $config) {
            $ffCategoryModels[$name] = Category::firstOrCreate([
                'restaurant_id' => $fastFood->id,
                'name' => $name,
            ], [
                'slug' => \Illuminate\Support\Str::slug($fastFood->slug . '-' . $name),
                'icon' => $config['icon'],
                'sort_order' => $config['sort_order'],
                'is_active' => true,
            ]);
        }

        $ffItems = [
            ['name' => 'Full Broast (4 pcs)', 'category' => 'Broast', 'price' => 950, 'description' => 'Crispy fried chicken, 4 pieces, served with fries and coleslaw.'],
            ['name' => 'Half Broast (2 pcs)', 'category' => 'Broast', 'price' => 550, 'description' => 'Crispy fried chicken, 2 pieces, served with fries.'],
            ['name' => 'Zinger Burger', 'category' => 'Burgers', 'price' => 450, 'description' => 'Crispy chicken fillet burger with mayo and lettuce.'],
            ['name' => 'Beef Burger', 'category' => 'Burgers', 'price' => 480, 'description' => 'Grilled beef patty with cheese, lettuce, and special sauce.'],
            ['name' => 'Chicken Shawarma', 'category' => 'Shawarma & Rolls', 'price' => 250, 'description' => 'Grilled chicken shawarma roll with garlic sauce and pickles.'],
            ['name' => 'Beef Seekh Roll', 'category' => 'Shawarma & Rolls', 'price' => 280, 'description' => 'Spiced beef seekh kabab wrapped in roti.'],
            ['name' => 'French Fries (Regular)', 'category' => 'Fries & Sides', 'price' => 200, 'description' => 'Crispy golden fries, regular portion.'],
            ['name' => 'Chicken Nuggets (6 pcs)', 'category' => 'Fries & Sides', 'price' => 350, 'description' => 'Crispy chicken nuggets with dip.'],
            ['name' => 'Fresh Lime', 'category' => 'Drinks', 'price' => 120, 'description' => 'Fresh lime water, sweet or salty.'],
            ['name' => 'Soft Drink (Regular)', 'category' => 'Drinks', 'price' => 100, 'description' => 'Coke, Pepsi, or Sprite, regular size.'],
        ];

        foreach ($ffItems as $item) {
            MenuItem::firstOrCreate([
                'restaurant_id' => $fastFood->id,
                'name' => $item['name'],
            ], [
                'category_id' => $ffCategoryModels[$item['category']]->id,
                'description' => $item['description'],
                'price' => $item['price'],
                'is_available' => true,
            ]);
        }

        Deal::firstOrCreate([
            'restaurant_id' => $fastFood->id,
            'name' => 'Broast Deal for 2',
        ], [
            'deal_number' => 1,
            'price' => 1300,
            'description' => 'Full broast, 2 fries, 2 soft drinks.',
            'is_active' => true,
        ]);

        $this->call([
            PakistaniMedicineSeeder::class,
            PakistaniGeneralStoreSeeder::class,
            FoodClinicSeeder::class,
        ]);
    }
}
