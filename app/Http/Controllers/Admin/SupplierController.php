<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $restaurant = $user->restaurant;

        $suppliers = Supplier::where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->paginate(15);

        return view('admin.medical.suppliers.index', [
            'suppliers' => $suppliers,
            'restaurant' => $restaurant,
        ]);
    }

    public function create()
    {
        $restaurant = auth()->user()->restaurant;
        return view('admin.medical.suppliers.form', [
            'supplier' => new Supplier(),
            'restaurant' => $restaurant,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $restaurant = $user->restaurant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:20',
            'average_delivery_days' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|in:cash,credit_7,credit_14,credit_30,custom',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['restaurant_id'] = $restaurant->id;

        Supplier::create($validated);

        return redirect()->route('manager.suppliers.index')->with('success', 'Supplier created successfully');
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('update', $supplier);
        $restaurant = auth()->user()->restaurant;

        return view('admin.medical.suppliers.form', [
            'supplier' => $supplier,
            'restaurant' => $restaurant,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:20',
            'average_delivery_days' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|in:cash,credit_7,credit_14,credit_30,custom',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('manager.suppliers.index')->with('success', 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);
        $supplier->delete();

        return redirect()->route('manager.suppliers.index')->with('success', 'Supplier deleted successfully');
    }
}
