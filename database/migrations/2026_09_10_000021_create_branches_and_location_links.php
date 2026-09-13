<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('name', 120);
                $table->string('code', 40);
                $table->string('address')->nullable();
                $table->string('phone', 40)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['restaurant_id', 'code']);
            });
        }

        if (Schema::hasTable('stock_transfers') && ! Schema::hasColumn('stock_transfers', 'from_branch_id')) {
            Schema::table('stock_transfers', function (Blueprint $table): void {
                $table->foreignId('from_branch_id')->nullable()->after('restaurant_id')->constrained('branches')->nullOnDelete();
                $table->foreignId('to_branch_id')->nullable()->after('from_branch_id')->constrained('branches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_transfers') && Schema::hasColumn('stock_transfers', 'from_branch_id')) {
            Schema::table('stock_transfers', function (Blueprint $table): void {
                $table->dropForeign(['from_branch_id']);
                $table->dropForeign(['to_branch_id']);
                $table->dropColumn(['from_branch_id', 'to_branch_id']);
            });
        }
        Schema::dropIfExists('branches');
    }
};
