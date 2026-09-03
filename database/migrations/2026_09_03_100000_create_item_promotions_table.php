<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-item sales (percent or fixed amount) managed by the business manager.
 * Run on CENTRAL if menu lives shared, or on TENANT if using DB-per-business.
 * Prefer running on the same connection as menu_items (typically tenant).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Central: used when shared DB; also create in tenant_migrations copy for tenant DBs
        if (! Schema::hasTable('item_promotions')) {
            Schema::create('item_promotions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->unsignedBigInteger('menu_item_id')->index();
                $table->string('label')->nullable(); // e.g. "Weekend Sale"
                $table->enum('type', ['percent', 'fixed'])->default('percent');
                $table->decimal('value', 12, 2); // % or Rs
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_promotions');
    }
};
