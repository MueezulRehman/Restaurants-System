<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Restaurant;

echo "=== All businesses ===\n";
Restaurant::with(['subscription.plan', 'businessType'])->get()->each(function (Restaurant $r) {
    $sub = $r->subscription;
    echo str_pad($r->slug, 28)
        . " | status=" . str_pad($r->status ?? 'NULL', 10)
        . " | businessType=" . str_pad($r->businessType?->name ?? 'NONE', 16)
        . " | hasSub=" . ($sub ? 'yes' : 'NO')
        . " | subStatus=" . ($sub?->status ?? '-')
        . " | periodEnd=" . ($sub?->current_period_end ?? '-')
        . " | planActive=" . ($sub?->plan ? ($sub->plan->is_active ? 'yes' : 'NO') : '-')
        . " | storefrontOK=" . ($r->isStorefrontAvailable() ? 'YES' : 'NO')
        . "\n";
});

echo "\n=== Menu data counts per business ===\n";
Restaurant::all()->each(function (Restaurant $r) {
    $cats = \App\Models\Category::where('restaurant_id', $r->id)->count();
    $items = \App\Models\MenuItem::where('restaurant_id', $r->id)->count();
    echo str_pad($r->slug, 28) . " | categories={$cats} | menu_items={$items}\n";
});
