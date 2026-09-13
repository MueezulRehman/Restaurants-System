<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CreditSalesController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $customers = Customer::where('restaurant_id', $restaurantId)
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->paginate(20);

        return view('manager.credit-sales.index', compact('customers'));
    }

    public function payment(Request $request, Customer $customer)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        abort_unless((int) $customer->restaurant_id === (int) $restaurantId, 404);
        $data = $request->validate(['amount' => 'required|numeric|min:0.01', 'description' => 'nullable|string|max:255']);
        abort_if((float) $data['amount'] > (float) $customer->balance, 422, 'Payment cannot exceed the outstanding balance.');

        DB::transaction(function () use ($customer, $data, $restaurantId): void {
            $customer->recordBalanceChange((float) $data['amount'], $data['description'] ?? 'Wholesale credit payment', [
                'type' => 'payment',
                'source' => 'credit-sales',
                'restaurant_id' => $restaurantId,
                'created_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Customer payment recorded.');
    }
}
