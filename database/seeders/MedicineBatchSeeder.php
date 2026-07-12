<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Database\Seeder;

class MedicineBatchSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = Medicine::query()->orderBy('id')->get();

        foreach ($medicines as $index => $medicine) {
            MedicineBatch::firstOrCreate([
                'medicine_id' => $medicine->id,
                'batch_number' => 'BATCH-' . ($index + 1) . '001',
            ], [
                'restaurant_id' => $medicine->restaurant_id,
                'expiry_date' => now()->addYears(2)->toDateString(),
                'purchase_price' => 100,
                'selling_price' => 150,
                'quantity' => 100,
            ]);
        }
    }
}
