<?php

namespace App\Http\Controllers\Admin;

use App\Models\CustomerAllergy;
use App\Models\Customer;
use App\Models\Medicine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CustomerAllergyController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurant;
        
        $allergies = CustomerAllergy::whereHas('customer', function ($q) use ($restaurant) {
            $q->where('restaurant_id', $restaurant->id);
        })->with('customer')->paginate(15);

        return view('admin.medical.allergies.index', [
            'allergies' => $allergies,
            'restaurant' => $restaurant,
        ]);
    }

    public function create()
    {
        $restaurant = auth()->user()->restaurant;
        $customers = Customer::where('restaurant_id', $restaurant->id)->orderBy('name')->get();
        $medicines = Medicine::where('restaurant_id', $restaurant->id)->orderBy('name')->get();

        return view('admin.medical.allergies.form', [
            'allergy' => new CustomerAllergy(),
            'restaurant' => $restaurant,
            'customers' => $customers,
            'medicines' => $medicines,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'allergy_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'severity' => 'required|in:mild,moderate,severe',
            'trigger_medicines' => 'nullable|array',
            'trigger_medicines.*' => 'exists:medicines,id',
            'is_active' => 'boolean',
        ]);

        $validated['trigger_medicines'] = $request->input('trigger_medicines') ?? [];

        CustomerAllergy::create($validated);

        return redirect()->route('manager.customer-allergies.index')
            ->with('success', 'Allergy recorded successfully');
    }

    public function edit(CustomerAllergy $customerAllergy)
    {
        $restaurant = Auth::user()?->restaurant;
        $customers = Customer::where('restaurant_id', $restaurant->id)->orderBy('name')->get();
        $medicines = Medicine::where('restaurant_id', $restaurant->id)->orderBy('name')->get();

        return view('admin.medical.allergies.form', [
            'allergy' => $customerAllergy,
            'restaurant' => $restaurant,
            'customers' => $customers,
            'medicines' => $medicines,
        ]);
    }

    public function update(Request $request, CustomerAllergy $customerAllergy)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'allergy_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'severity' => 'required|in:mild,moderate,severe',
            'trigger_medicines' => 'nullable|array',
            'trigger_medicines.*' => 'exists:medicines,id',
            'is_active' => 'boolean',
        ]);

        $validated['trigger_medicines'] = $request->input('trigger_medicines') ?? [];

        $customerAllergy->update($validated);

        return redirect()->route('manager.customer-allergies.index')
            ->with('success', 'Allergy updated successfully');
    }

    public function destroy(CustomerAllergy $customerAllergy)
    {
        $customerAllergy->delete();

        return redirect()->route('manager.customer-allergies.index')
            ->with('success', 'Allergy deleted successfully');
    }
}
