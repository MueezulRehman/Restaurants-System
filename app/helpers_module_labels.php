<?php

use App\Support\ModuleLabels;

if (! function_exists('module_label')) {
    function module_label(string $key, $restaurant = null): string
    {
        $restaurant = $restaurant ?? (auth()->user()?->effectiveRestaurant() ?? null);

        return ModuleLabels::label($restaurant, $key);
    }
}

if (! function_exists('module_icon')) {
    function module_icon(string $key, $restaurant = null): string
    {
        $restaurant = $restaurant ?? (auth()->user()?->effectiveRestaurant() ?? null);

        return ModuleLabels::icon($restaurant, $key);
    }
}
