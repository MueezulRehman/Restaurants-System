<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

/**
 * Public multi-business discovery homepage.
 *
 * - Main domain "/" shows businesses Super Admin marked as show_on_homepage
 * - User can search by name
 * - Clicking a card goes to that restaurant's menu (slug or custom domain)
 *
 * Custom domain + slug behaviour is unchanged and still handled by
 * ResolveRestaurant + MenuController@showBySlug.
 *
 * @author Mueez Ul Rehman
 */
class HomeController extends Controller
{
    public function index(Request $request)
    {
        // If the request already resolved a specific restaurant via custom domain
        // (ResolveRestaurant middleware), send the visitor straight to that menu.
        if (app()->bound('restaurant')) {
            $restaurant = app('restaurant');
            if ($restaurant instanceof Restaurant && $restaurant->isStorefrontAvailable()) {
                return redirect()->route('menu.restaurant', $restaurant->slug);
            }
        }

        $search = trim((string) $request->get('q', ''));

        $query = Restaurant::query()
            ->where('status', 'active')
            ->where('show_on_homepage', true)
            ->with(['businessType', 'subscription.plan'])
            ->orderBy('homepage_sort_order')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $restaurants = $query->get()->filter(function (Restaurant $restaurant) {
            // Only show businesses whose storefront is actually available
            return $restaurant->isStorefrontAvailable();
        })->values();

        return view('customer.home', [
            'restaurants' => $restaurants,
            'search' => $search,
        ]);
    }
}
