<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Deal;
use App\Models\PlatformSetting;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Support\Tenancy;

class MenuController extends Controller
{
    /**
     * CodeIbex platform homepage — lists active businesses.
     * Individual storefronts are served by /{slug}.
     */
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->query('q', ''));
            $restaurants = Restaurant::query()
                ->where('status', 'active')
                ->with('businessType')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->get();

            $platform = [
                'name' => PlatformSetting::getValue('platform_name', 'CodeIbex'),
                'hero_title' => PlatformSetting::getValue('homepage_hero_title', 'CodeIbex'),
                'hero_subtitle' => PlatformSetting::getValue('homepage_hero_subtitle', 'One platform for discovering and ordering from independent businesses.'),
            ];

            return view('customer.home', compact('restaurants', 'search', 'platform'));
        } catch (\Exception $e) {
            return view('customer.home', [
                'restaurants' => collect(),
                'search' => trim((string) $request->query('q', '')),
                'platform' => [
                    'name' => 'CodeIbex',
                    'hero_title' => 'CodeIbex',
                    'hero_subtitle' => 'One platform for discovering and ordering from independent businesses.',
                ],
            ]);
        }
    }

    /**
     * Per-restaurant public menu page: /{slug}
     * e.g. /tastehut, /pizza-house
     */
    public function showBySlug(string $slug)
    {
        $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $restaurant = Restaurant::on($central)->with('subscription.plan')
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $restaurant->isStorefrontAvailable()) {
            return view('customer.storefront-unavailable', [
                'restaurant' => $restaurant,
            ]);
        }

        // Switch to tenant DB so Category / MenuItem queries hit the right database
        if ($restaurant->hasTenantDatabase()) {
            Tenancy::configureTenantConnection($restaurant);
        }
        Tenancy::setCurrent($restaurant);

        return $this->renderMenu($restaurant);
    }

    protected function renderMenu(Restaurant $restaurant)
    {
        if (! $restaurant->isStorefrontAvailable()) {
            return view('customer.storefront-unavailable', [
                'restaurant' => $restaurant,
            ]);
        }

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

        if ($categories->isEmpty() && $deals->isEmpty()) {
            return view('customer.menu-empty', [
                'restaurant' => $restaurant,
            ]);
        }

        $template = $restaurant->getCustomerMenuTemplate() ?: 'default';
        $templatePath = resource_path("views/customer/menu_templates/{$template}.blade.php");
        // Empty template files (0 bytes) cause a blank white page — fall back to main menu
        $viewName = (is_file($templatePath) && filesize($templatePath) > 50)
            ? "customer.menu_templates.{$template}"
            : 'customer.menu';

        return view($viewName, [
            'categories' => $categories,
            'deals' => $deals,
            'currentRestaurant' => $restaurant,
        ]);
    }
}
