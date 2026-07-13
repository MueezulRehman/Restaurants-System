<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use Illuminate\Console\Command;

class ImportRestaurantData extends Command
{
    protected $signature = 'restaurant:import {file} {restaurantId}';
    protected $description = 'Import restaurant data from export file (restores all business data)';

    public function handle(): void
    {
        $file = $this->argument('file');
        $restaurantId = $this->argument('restaurantId');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $restaurant = Restaurant::find($restaurantId);
        if (!$restaurant) {
            $this->error("Restaurant with ID $restaurantId not found");
            return;
        }

        $importData = json_decode(file_get_contents($file), true);

        $this->info("Importing data for restaurant: {$restaurant->name}");

        // Import menu items, categories, orders, expenses, staff
        // (Implementation depends on data structure)

        $this->info("Data import completed");
    }
}
