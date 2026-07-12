<?php

namespace Database\Seeders;

use App\Models\MedicineUnit;
use Illuminate\Database\Seeder;

class MedicineUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'Tablet',
            'Capsule',
            'Bottle',
            'Strip',
            'Box',
            'Tube',
            'Vial',
            'Ampoule',
            'Pack',
            'Piece',
        ];

        foreach ($units as $unit) {
            MedicineUnit::firstOrCreate([
                'name' => $unit,
            ], [
                'status' => 1,
            ]);
        }
    }
}
