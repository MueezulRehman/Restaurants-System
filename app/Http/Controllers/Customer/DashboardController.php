<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $orders = $customer->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('customer.account.dashboard', compact('customer', 'orders'));
    }
}
