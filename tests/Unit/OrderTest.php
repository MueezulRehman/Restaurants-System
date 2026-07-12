<?php

namespace Tests\Unit;

use App\Models\Order;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_table_number_is_mass_assignable(): void
    {
        $order = new Order(['table_number' => 'T12']);

        $this->assertSame('T12', $order->table_number);
    }
}
