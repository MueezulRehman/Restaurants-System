<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropUnique(['email']);
            $table->unique(['restaurant_id', 'phone']);
            $table->unique(['restaurant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['restaurant_id', 'phone']);
            $table->dropUnique(['restaurant_id', 'email']);
            $table->unique('phone');
            $table->unique('email');
        });
    }
};
