<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerMenuTest extends TestCase
{
    public function test_homepage_shows_the_codeibex_business_directory(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CodeIbex');
        $response->assertSee('One platform for discovering and ordering from independent businesses.');
        $response->assertSee('Search by name or area...');
    }
}
