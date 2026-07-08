<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerMenuTest extends TestCase
{
    public function test_menu_page_shows_a_polished_intro_and_cta(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Freshly baked favorites');
        $response->assertSee('Start your order');
    }
}
