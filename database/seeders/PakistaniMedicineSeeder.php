<?php

namespace Database\Seeders;

use App\Models\Category as MenuCategory;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class PakistaniMedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'tastehut')->first() ?? Restaurant::first();

        if (! $restaurant) {
            return;
        }

        $categories = [
            'Pain Relief' => ['icon' => '💊', 'sort_order' => 1],
            'Antibiotics' => ['icon' => '🧪', 'sort_order' => 2],
            'Cold & Flu' => ['icon' => '🌡️', 'sort_order' => 3],
            'Vitamins' => ['icon' => '🧬', 'sort_order' => 4],
            'Digestive Health' => ['icon' => '🫙', 'sort_order' => 5],
        ];

        foreach ($categories as $name => $config) {
            MedicineCategory::firstOrCreate([
                'name' => $name,
            ], [
                'status' => true,
            ]);
        }

        $medicines = [
            [
                'name' => 'Panadol Extra',
                'generic_name' => 'Paracetamol',
                'category' => 'Pain Relief',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'sku' => 'PA-001',
                'requires_prescription' => false,
                'track_stock' => true,
                'min_stock' => 20,
                'description' => 'Common pain relief medicine widely used in Pakistan.',
                'tax' => 5,
            ],
            [
                'name' => 'Brufen',
                'generic_name' => 'Ibuprofen',
                'category' => 'Pain Relief',
                'dosage_form' => 'Tablet',
                'strength' => '400mg',
                'sku' => 'PA-002',
                'requires_prescription' => false,
                'track_stock' => true,
                'min_stock' => 20,
                'description' => 'Fast relief for fever and body pain.',
                'tax' => 5,
            ],
            [
                'name' => 'Augmentin 625',
                'generic_name' => 'Amoxicillin/Clavulanic Acid',
                'category' => 'Antibiotics',
                'dosage_form' => 'Tablet',
                'strength' => '625mg',
                'sku' => 'AB-001',
                'requires_prescription' => true,
                'track_stock' => true,
                'min_stock' => 15,
                'description' => 'Widely prescribed antibiotic for bacterial infections.',
                'tax' => 7,
            ],
            [
                'name' => 'Cefixime',
                'generic_name' => 'Cefixime',
                'category' => 'Antibiotics',
                'dosage_form' => 'Tablet',
                'strength' => '200mg',
                'sku' => 'AB-002',
                'requires_prescription' => true,
                'track_stock' => true,
                'min_stock' => 10,
                'description' => 'Common oral antibiotic used in many clinics.',
                'tax' => 7,
            ],
            [
                'name' => 'Benylin',
                'generic_name' => 'Diphenhydramine',
                'category' => 'Cold & Flu',
                'dosage_form' => 'Syrup',
                'strength' => '100ml',
                'sku' => 'CF-001',
                'requires_prescription' => false,
                'track_stock' => true,
                'min_stock' => 12,
                'description' => 'Popular cold and cough relief syrup.',
                'tax' => 5,
            ],
            [
                'name' => 'ORS Sachet',
                'generic_name' => 'Oral Rehydration Salts',
                'category' => 'Cold & Flu',
                'dosage_form' => 'Powder',
                'strength' => '20.5g',
                'sku' => 'CF-002',
                'requires_prescription' => false,
                'track_stock' => true,
                'min_stock' => 30,
                'description' => 'Essential rehydration solution for dehydration.',
                'tax' => 0,
            ],
            [
                'name' => 'Vitamin C',
                'generic_name' => 'Ascorbic Acid',
                'category' => 'Vitamins',
                'dosage_form' => 'Tablet',
                'strength' => '1000mg',
                'sku' => 'VT-001',
                'requires_prescription' => false,
                'track_stock' => true,
                'min_stock' => 20,
                'description' => 'Daily vitamin supplement.',
                'tax' => 5,
            ],
            [
                'name' => '100 Plus',
                'generic_name' => 'Electrolyte Drink',
                'category' => 'Vitamins',
                'dosage_form' => 'Drink',
                'strength' => '250ml',
                'sku' => 'VT-002',
                'requires_prescription' => false,
                'track_stock' => true,
                'min_stock' => 20,
                'description' => 'Refreshing electrolyte drink for hydration and energy support.',
                'tax' => 5,
            ],
            [
                'name' => 'Omeprazole',
                'generic_name' => 'Omeprazole',
                'category' => 'Digestive Health',
                'dosage_form' => 'Capsule',
                'strength' => '20mg',
                'sku' => 'DG-001',
                'requires_prescription' => false,
                'track_stock' => true,
                'min_stock' => 15,
                'description' => 'Common medicine for acidity and heartburn.',
                'tax' => 5,
            ],
        ];

        foreach ($medicines as $medicineData) {
            $category = MedicineCategory::where('name', $medicineData['category'])->first();

            $medicine = Medicine::updateOrCreate([
                'restaurant_id' => $restaurant->id,
                'sku' => $medicineData['sku'],
            ], [
                'name' => $medicineData['name'],
                'generic_name' => $medicineData['generic_name'],
                'category_id' => $category?->id,
                'dosage_form' => $medicineData['dosage_form'],
                'strength' => $medicineData['strength'],
                'barcode' => null,
                'requires_prescription' => $medicineData['requires_prescription'],
                'track_stock' => $medicineData['track_stock'],
                'min_stock' => $medicineData['min_stock'],
                'description' => $medicineData['description'],
                'tax' => $medicineData['tax'],
            ]);

            $resolvedCategoryId = $this->resolveMedicineCategoryId($medicine->category_id);
            if ($resolvedCategoryId && $medicine->category_id !== $resolvedCategoryId) {
                $medicine->category_id = $resolvedCategoryId;
                $medicine->save();
            }

            MedicineBatch::updateOrCreate([
                'medicine_id' => $medicine->id,
                'restaurant_id' => $restaurant->id,
                'batch_number' => 'BATCH-' . $medicine->id,
            ], [
                'mfg_date' => now()->subMonths(2)->toDateString(),
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'purchase_price' => 80,
                'selling_price' => 120,
                'wholesale_price' => 100,
                'quantity' => 100,
                'rack_number' => 'R1',
                'storage_location' => 'Main Shelf',
            ]);
        }
    }

    protected function resolveMedicineCategoryId(?int $categoryId): ?int
    {
        if (! $categoryId) {
            return null;
        }

        $medicineCategory = MedicineCategory::find($categoryId);
        if ($medicineCategory) {
            return $medicineCategory->id;
        }

        $legacyCategory = MenuCategory::find($categoryId);
        if (! $legacyCategory) {
            return null;
        }

        $mappedCategory = MedicineCategory::firstOrCreate([
            'name' => $legacyCategory->name,
        ], [
            'status' => true,
        ]);

        return $mappedCategory->id;
    }
}
