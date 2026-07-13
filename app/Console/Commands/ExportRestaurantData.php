<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportRestaurantData extends Command
{
    protected $signature = 'restaurant:export {restaurantId} {--output=export.json}';
    protected $description = 'Export all data for a specific restaurant (scoped to that business only)';

    public function handle(): void
    {
        $restaurantId = $this->argument('restaurantId');
        $outputFile = $this->option('output');

        $restaurant = Restaurant::find($restaurantId);
        if (!$restaurant) {
            $this->error("Restaurant with ID $restaurantId not found");
            return;
        }

        $this->info("Exporting data for restaurant: {$restaurant->name}");

        // Export all tenant-scoped data
        $exportData = [
            'restaurant' => $restaurant->toArray(),
            'menu_items' => $restaurant->menuItems ?? [],
            'categories' => $restaurant->categories ?? [],
            'orders' => $restaurant->orders ?? [],
            'expenses' => $restaurant->expenses ?? [],
            'staff' => $restaurant->users ?? [],
            'timestamps' => now()->toIso8601String(),
        ];

        file_put_contents($outputFile, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info("Data exported to: $outputFile");
    }
}
