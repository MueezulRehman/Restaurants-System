<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->string('cnic', 30)->nullable()->after('name');
            $table->boolean('is_dependent')->default(false)->after('gender');
            $table->string('guardian_name')->nullable()->after('is_dependent');
            $table->string('guardian_cnic', 30)->nullable()->after('guardian_name');
            $table->string('guardian_phone', 40)->nullable()->after('guardian_cnic');
            $table->string('relationship', 30)->nullable()->after('guardian_phone');
            $table->unique(['restaurant_id', 'cnic']);
            $table->index(['restaurant_id', 'name', 'phone', 'date_of_birth']);
        });

        Schema::table('patients', function (Blueprint $table): void {
            $table->string('phone', 40)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique(['restaurant_id', 'cnic']);
            $table->dropIndex(['restaurant_id', 'name', 'phone', 'date_of_birth']);
            $table->dropColumn([
                'cnic',
                'is_dependent',
                'guardian_name',
                'guardian_cnic',
                'guardian_phone',
                'relationship',
            ]);
        });
    }
};
