<?php

namespace Database\Seeders;

use App\Models\MedicineCategory;
use Illuminate\Database\Seeder;

class MedicineCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Tablet',
            'Capsule',
            'Syrup',
            'Injection',
            'Drops',
            'Cream',
            'Ointment',
            'Gel',
            'Powder',
            'Inhaler',
            'Vitamin',
            'Medical Device',
            'Surgical Item',
        ];

        foreach ($data as $item) {
            MedicineCategory::firstOrCreate([
                'name' => $item,
            ], [
                'status' => 1,
            ]);
        }
    }
}
