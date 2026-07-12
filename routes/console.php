<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('modules:sync', function () {
    \App\Models\Restaurant::all()->each(function ($r) {
        if ((!$r->enabled_modules || count($r->enabled_modules) === 0) && $r->businessType) {
            $keys = $r->businessType->modules()->where('is_active', true)->pluck('key')->toArray();
            $r->enabled_modules = $keys;
            $r->save();
            $this->info('Updated restaurant '.$r->id);
        }
    });
})->describe('Sync enabled_modules for restaurants from their business types');
