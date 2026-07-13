<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id')->nullable()->index();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable()->index();
            $table->unsignedBigInteger('manufacturer_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('dosage_form')->nullable();
            $table->string('strength')->nullable();
            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable()->index();
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('track_stock')->default(true);
            $table->integer('min_stock')->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('tax', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicines');
    }
};
