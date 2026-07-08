<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Deal;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a login-ready super admin user for the app.
        User::firstOrCreate([
            'phone' => '1234567890',
        ], [
            'name' => 'Super Admin',
            'role' => 'super_admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

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
        ]);

        User::firstOrCreate([
            'phone' => '0987654321',
        ], [
            'name' => 'Taste Hut Manager',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
            'email' => 'manager@tastehut.test',
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

        $burgersCategory = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Burgers',
            'slug' => 'burgers',
            'icon' => '🍔',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $drinksCategory = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Drinks',
            'slug' => 'drinks',
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
    }
}
