<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id')->index();
            $table->unsignedBigInteger('restaurant_id')->nullable()->index();
            $table->string('batch_number')->nullable()->index();
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->string('rack_number')->nullable();
            $table->string('storage_location')->nullable();
            $table->timestamps();

            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicine_batches');
    }
};
