<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LoyaltyController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $accounts = LoyaltyAccount::with('customer')->where('restaurant_id', $restaurantId)->latest()->paginate(20);
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();

        return view('admin.loyalty.index', compact('accounts', 'customers'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'points' => 'required|integer|min:0',
        ]);
        $account = LoyaltyAccount::firstOrCreate(['restaurant_id' => $restaurantId, 'customer_id' => $data['customer_id']], ['points' => 0]);
        $account->increment('points', (int) $data['points']);

        return back()->with('success', 'Loyalty points added.');
    }

    public function redeem(Request $request, \App\Models\LoyaltyAccount $loyaltyAccount)
    {
        abort_unless((int) $loyaltyAccount->restaurant_id === (int) Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate(['points' => 'required|integer|min:1']);
        abort_if((int) $data['points'] > (int) $loyaltyAccount->points, 422, 'Customer does not have enough loyalty points.');
        $loyaltyAccount->decrement('points', (int) $data['points']);

        return back()->with('success', 'Loyalty points redeemed.');
    }
}
