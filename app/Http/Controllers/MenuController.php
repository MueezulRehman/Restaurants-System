<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Deal;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Home page — shows the first active restaurant's menu,
     * or redirects to /tastehut if none bound yet.
     */
    public function index()
    {
        // Try to resolve restaurant by subdomain/slug from the URL or fall back to first active one
        $restaurant = Restaurant::where('status', 'active')->first()
            ?? Restaurant::first();

        if (! $restaurant) {
            return view('customer.no-restaurant');
        }

        if (! app()->bound('restaurant')) {
            return redirect()->route('menu.restaurant', $restaurant->slug);
        }

        return $this->renderMenu($restaurant);
    }

    /**
     * Per-restaurant public menu page: /{slug}
     * e.g. /tastehut, /pizza-house
     */
    public function showBySlug(string $slug)
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return $this->renderMenu($restaurant);
    }

   protected function renderMenu(Restaurant $restaurant)
    {
        $categories = Category::where('is_active', true)
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('sort_order')
            ->with(['availableMenuItems.sizes'])
            ->get();

        $deals = Deal::active()
            ->where('restaurant_id', $restaurant->id)
            ->get();

        session(['current_restaurant_id' => $restaurant->id]);
        app()->instance('restaurant', $restaurant);
        view()->share('currentRestaurant', $restaurant);

        return view('customer.menu', [
            'categories' => $categories,
            'deals' => $deals,
            'currentRestaurant' => $restaurant,
        ]);
    }
}
