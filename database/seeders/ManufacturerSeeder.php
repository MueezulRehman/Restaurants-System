<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;

class ManufacturerSeeder extends Seeder
{
    public function run(): void
    {
        $manufacturers = [
            'Getz Pharma',
            'GSK Pakistan',
            'Abbott Laboratories',
            'Highnoon Laboratories',
            'Sami Pharmaceuticals',
            'Hilton Pharma',
            'Martin Dow',
            'AGP Limited',
            'Ferozsons Laboratories',
            'The Searle Company',
            'Pfizer Pakistan',
            'Sanofi Pakistan',
            'Novartis',
            'Bayer Pakistan',
        ];

        foreach ($manufacturers as $name) {
            Manufacturer::firstOrCreate([
                'name' => $name,
            ], [
                'status' => 1,
            ]);
        }
    }
}
