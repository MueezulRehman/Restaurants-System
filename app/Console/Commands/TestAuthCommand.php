<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TestAuthCommand extends Command
{
    protected $signature = 'test:auth';

    protected $description = 'Verify test admin, manager, and customer credentials';

    public function handle(): int
    {
        $this->info('Checking credentials...');

        $a = User::where('email', 'admin@example.com')->first();
        $this->line('admin: ' . (($a && Hash::check('password', $a->password)) ? 'OK' : 'FAIL'));

        // Also try Auth::attempt to mimic login flow
        $this->line('admin (Auth::attempt): ' . (Auth::attempt(['phone' => '10000000001', 'password' => 'password']) ? 'OK' : 'FAIL'));

        $m = User::where('email', 'manager@example.com')->first();
        $this->line('manager: ' . (($m && Hash::check('password', $m->password)) ? 'OK' : 'FAIL'));
        $this->line('manager (Auth::attempt): ' . (Auth::attempt(['phone' => '10000000002', 'password' => 'password']) ? 'OK' : 'FAIL'));

        $c = Customer::where('email', 'customer@example.com')->first();
        $this->line('customer: ' . (($c && Hash::check('password', $c->password)) ? 'OK' : 'FAIL'));

        $this->line('customer (guard attempt): ' . (Auth::guard('customer')->attempt(['phone' => '20000000001', 'password' => 'password']) ? 'OK' : 'FAIL'));

        return 0;
    }
}
