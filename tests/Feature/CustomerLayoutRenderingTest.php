<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerLayoutRenderingTest extends TestCase
{
    public function test_order_lookup_form_is_rendered_once(): void
    {
        $response = $this->get(route('orders.lookup.form'));

        $response->assertOk();
        $response->assertSeeText('Track your order');
        $this->assertSame(1, substr_count($response->getContent(), 'menu-card p-6'));
    }
}
