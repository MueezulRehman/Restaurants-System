<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InstallmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InstallmentController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $plans = InstallmentPlan::with('customer')->where('restaurant_id', $restaurantId)->latest()->paginate(20);
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();

        return view('admin.installments.index', compact('plans', 'customers'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'item_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0.01',
            'deposit' => 'required|numeric|min:0',
            'months' => 'required|integer|min:1|max:120',
            'next_due_at' => 'nullable|date',
        ]);
        if ((float) $data['deposit'] > (float) $data['total_amount']) {
            return back()->withInput()->withErrors(['deposit' => 'Deposit cannot exceed the total amount.']);
        }
        InstallmentPlan::create(array_merge($data, ['restaurant_id' => $restaurantId, 'status' => 'active']));

        return back()->with('success', 'Installment plan created.');
    }

    public function update(Request $request, InstallmentPlan $installmentPlan)
    {
        abort_unless($installmentPlan->restaurant_id === Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'paid', 'overdue', 'cancelled'])], 'next_due_at' => 'nullable|date']);
        $installmentPlan->update($data);

        return back()->with('success', 'Installment plan updated.');
    }
}
