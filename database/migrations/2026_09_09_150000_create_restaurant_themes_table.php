<?php

use App\Models\Restaurant;
use App\Models\RestaurantTheme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->unique()->constrained('restaurants')->cascadeOnDelete();
            $table->json('manager_light')->nullable();
            $table->json('manager_dark')->nullable();
            $table->json('customer')->nullable();
            $table->timestamps();
        });

        Restaurant::query()->each(function (Restaurant $restaurant): void {
            $legacy = is_array($restaurant->theme) ? $restaurant->theme : [];
            $defaults = RestaurantTheme::defaults();
            RestaurantTheme::create([
                'restaurant_id' => $restaurant->id,
                'manager_light' => array_merge($defaults['manager_light'], [
                    'primary' => $legacy['primary'] ?? $defaults['manager_light']['primary'],
                    'accent' => $legacy['accent'] ?? $defaults['manager_light']['accent'],
                    'dark' => $legacy['secondary'] ?? $defaults['manager_light']['dark'],
                    'background' => $legacy['light'] ?? $defaults['manager_light']['background'],
                ]),
                'manager_dark' => $defaults['manager_dark'],
                'customer' => array_merge($defaults['customer'], $legacy),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_themes');
    }
};
