<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InsuranceClaim;
use App\Models\InsuranceProvider;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsuranceController extends Controller
{
    private function restaurantId(): int
    {
        $id = auth()->user()?->effectiveRestaurantId();
        abort_unless($id, 403);
        return (int) $id;
    }

    public function index()
    {
        $id = $this->restaurantId();
        $providers = InsuranceProvider::where('restaurant_id', $id)->orderBy('name')->get();
        $claims = InsuranceClaim::with(['provider', 'customer'])->where('restaurant_id', $id)->latest()->paginate(20);
        $customers = Customer::where('restaurant_id', $id)->orderBy('name')->get();
        return view('manager.insurance.index', compact('providers', 'claims', 'customers'));
    }

    public function storeProvider(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'phone' => 'nullable|string|max:40', 'email' => 'nullable|email|max:150']);
        InsuranceProvider::create([...$data, 'restaurant_id' => $this->restaurantId(), 'is_active' => true]);
        return back()->with('success', 'Insurance provider added.');
    }

    public function storeClaim(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['provider_id' => ['nullable', 'integer', Rule::exists('insurance_providers', 'id')->where(fn($q) => $q->where('restaurant_id', $id))], 'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn($q) => $q->where('restaurant_id', $id))], 'policy_number' => 'nullable|string|max:100', 'claim_number' => 'nullable|string|max:100', 'claimed_amount' => 'required|numeric|min:0', 'status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'partially_approved', 'rejected', 'paid'])], 'notes' => 'nullable|string|max:2000']);
        InsuranceClaim::create([...$data, 'restaurant_id' => $id]);
        return back()->with('success', 'Insurance claim recorded.');
    }
}
