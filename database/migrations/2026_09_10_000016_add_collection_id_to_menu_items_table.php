<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menu_items', 'collection_id')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->foreignId('collection_id')->nullable()->after('category_id')->constrained('retail_collections')->nullOnDelete();
                $table->index(['restaurant_id', 'collection_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'collection_id')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->dropForeign(['collection_id']);
                $table->dropIndex(['restaurant_id', 'collection_id']);
                $table->dropColumn('collection_id');
            });
        }
    }
};
