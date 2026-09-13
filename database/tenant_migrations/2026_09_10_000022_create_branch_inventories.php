<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('branch_inventories')) {
            Schema::create('branch_inventories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->unsignedBigInteger('branch_id')->index();
                $table->string('item_type', 30);
                $table->unsignedBigInteger('item_id');
                $table->decimal('quantity', 14, 3)->default(0);
                $table->timestamps();
                $table->unique(['branch_id', 'item_type', 'item_id'], 'branch_inventory_item_unique');
                $table->index(['restaurant_id', 'item_type', 'item_id'], 'branch_inventory_lookup_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_inventories');
    }
};
