<?php

namespace Tests\Unit;

use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use Tests\TestCase;

class RestaurantStorefrontAvailabilityTest extends TestCase
{
    public function test_storefront_is_available_when_subscription_is_active(): void
    {
        $restaurant = new Restaurant(['status' => 'active']);
        $restaurant->setRelation('subscription', new RestaurantSubscription([
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]));

        $this->assertTrue($restaurant->isStorefrontAvailable());
    }

    public function test_storefront_is_unavailable_when_subscription_has_expired(): void
    {
        $restaurant = new Restaurant(['status' => 'active']);
        $restaurant->setRelation('subscription', new RestaurantSubscription([
            'status' => 'expired',
            'current_period_end' => now()->subDay(),
        ]));

        $this->assertFalse($restaurant->isStorefrontAvailable());
    }

    public function test_public_discovery_requires_homepage_access(): void
    {
        $restaurant = new Restaurant(['status' => 'active', 'show_on_homepage' => false]);
        $restaurant->setRelation('subscription', new RestaurantSubscription([
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]));

        $this->assertFalse($restaurant->isPubliclyDiscoverable());

        $restaurant->show_on_homepage = true;

        $this->assertTrue($restaurant->isPubliclyDiscoverable());
    }
}
