<?php

namespace Tests;

use App\Support\Tenancy;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        Tenancy::end();
        DB::purge('tenant');
        config(['database.default' => env('DB_CONNECTION', 'sqlite')]);

        parent::tearDown();
    }
}
