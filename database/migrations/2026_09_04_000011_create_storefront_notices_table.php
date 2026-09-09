<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->unique()->constrained('restaurants')->cascadeOnDelete();
            $table->string('title', 120)->default('Important update');
            $table->text('message');
            $table->boolean('is_active')->default(false);
            $table->boolean('show_as_modal')->default(true);
            $table->timestamps();
        });

        if (Schema::hasColumn('restaurants', 'storefront_notice')) {
            DB::table('restaurants')
                ->whereNotNull('storefront_notice')
                ->where('storefront_notice', '<>', '')
                ->orderBy('id')
                ->each(function ($restaurant): void {
                    DB::table('storefront_notices')->insert([
                        'restaurant_id' => $restaurant->id,
                        'title' => 'Important update',
                        'message' => $restaurant->storefront_notice,
                        'is_active' => (bool) $restaurant->storefront_notice_enabled,
                        'show_as_modal' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_notices');
    }
};