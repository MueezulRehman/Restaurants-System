<?php

namespace Tests\Unit;

use App\Http\Middleware\ResolveRestaurant;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ResolveRestaurantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_binds_the_matching_restaurant_to_the_request_and_container(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Taste Hut',
            'slug' => 'taste-hut',
            'custom_domain' => 'tenant.example.test',
            'status' => 'active',
        ]);

        $request = Request::create('https://tenant.example.test/menu');
        $middleware = new ResolveRestaurant();

        $response = $middleware->handle($request, function ($handledRequest) use ($restaurant) {
            $this->assertSame($restaurant->id, $handledRequest->attributes->get('restaurant')->id);
            $this->assertSame($restaurant->id, app('restaurant')->id);

            return response('ok');
        });

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_it_allows_checkout_requests_without_a_restaurant_on_localhost(): void
    {
        $request = Request::create('http://localhost/checkout');
        $middleware = new ResolveRestaurant();

        $response = $middleware->handle($request, function ($handledRequest) {
            $this->assertNull($handledRequest->attributes->get('restaurant'));

            return response('ok');
        });

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_it_resolves_a_restaurant_by_slug_on_the_main_domain_path(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Taste Hut',
            'slug' => 'taste-hut',
            'status' => 'active',
        ]);

        $request = Request::create('https://example.com/taste-hut');
        $middleware = new ResolveRestaurant();

        $response = $middleware->handle($request, function ($handledRequest) use ($restaurant) {
            $this->assertSame($restaurant->id, $handledRequest->attributes->get('restaurant')->id);
            $this->assertSame($restaurant->id, app('restaurant')->id);

            return response('ok');
        });

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_it_generates_a_public_url_for_restaurants_with_and_without_custom_domains(): void
    {
        $restaurantWithDomain = Restaurant::create([
            'name' => 'Domain Hut',
            'slug' => 'domain-hut',
            'domain' => 'tenant.example.test',
            'status' => 'active',
        ]);

        $restaurantWithSlugOnly = Restaurant::create([
            'name' => 'Slug Hut',
            'slug' => 'slug-hut',
            'status' => 'active',
        ]);

        $this->assertSame('https://tenant.example.test', $restaurantWithDomain->getPublicUrl());
        $this->assertStringContainsString('/slug-hut', $restaurantWithSlugOnly->getPublicUrl());
    }
}
