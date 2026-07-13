<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PosController;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PosMedicalCategoryFilterTest extends TestCase
{
    public function test_medical_items_can_be_filtered_by_selected_category()
    {
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('medicine_categories');

        Schema::create('medicine_categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('medicines', function ($table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->timestamps();
        });

        Schema::create('medicine_batches', function ($table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('mfg_date')->nullable();
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        $categoryOne = MedicineCategory::create(['name' => 'Ointment']);
        $categoryTwo = MedicineCategory::create(['name' => 'Pain Relief']);

        Medicine::create([
            'name' => 'Hydrocortisone Cream',
            'category_id' => $categoryOne->id,
            'sku' => 'MED-001',
            'barcode' => '123456789001',
        ]);

        Medicine::create([
            'name' => 'Paracetamol Tablets',
            'category_id' => $categoryTwo->id,
            'sku' => 'MED-002',
            'barcode' => '123456789002',
        ]);

        $controller = new class extends PosController {
            public function exposeGetMedicalItemsForPos($restaurant, $categoryId = null)
            {
                return $this->getMedicalItemsForPos($restaurant, $categoryId);
            }
        };

        $restaurant = new class {
            public $id = 1;
        };

        $items = $controller->exposeGetMedicalItemsForPos($restaurant, $categoryOne->id);

        $this->assertCount(1, $items);
        $this->assertSame('Hydrocortisone Cream', $items->first()->name);
    }
}
