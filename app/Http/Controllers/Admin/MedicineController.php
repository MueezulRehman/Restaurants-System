<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicineController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();
        $medicines = Medicine::with(['category', 'batches' => function ($q) use ($restaurantId) {
            $q->where(function ($sub) use ($restaurantId) {
                $sub->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurantId);
            });
        }])->where(function ($q) use ($restaurantId) {
            $q->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurantId);
        })->orderBy('name')->paginate(30);
        $categories = MedicineCategory::orderBy('name')->get();

        return view('admin.medicines.index', compact('medicines', 'categories'));
    }

    public function create()
    {
        $categories = MedicineCategory::orderBy('name')->get();

        return view('admin.medicines.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'image' => 'nullable|string|max:255',
            'tax' => 'nullable|numeric|min:0',
            'requires_prescription' => 'sometimes|boolean',
            'track_stock' => 'sometimes|boolean',
            'min_stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'new_category_name' => 'nullable|string|max:255',
        ]);

        if (!empty($data['new_category_name'])) {
            $category = MedicineCategory::firstOrCreate([
                'name' => trim($data['new_category_name']),
            ], [
                'status' => true,
            ]);
            $data['category_id'] = $category->id;
        }

        $data['restaurant_id'] = $restaurantId;
        $data['requires_prescription'] = $request->boolean('requires_prescription');
        $data['track_stock'] = $request->boolean('track_stock', true);
        unset($data['new_category_name']);

        $medicine = Medicine::create($data);

        return redirect()->route('manager.medicines.index')->with('success', 'Medicine created.');
    }

    public function edit(Medicine $medicine)
    {
        $categories = MedicineCategory::orderBy('name')->get();

        return view('admin.medicines.edit', compact('medicine', 'categories'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'image' => 'nullable|string|max:255',
            'tax' => 'nullable|numeric|min:0',
            'requires_prescription' => 'sometimes|boolean',
            'track_stock' => 'sometimes|boolean',
            'min_stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'new_category_name' => 'nullable|string|max:255',
        ]);

        if (!empty($data['new_category_name'])) {
            $category = MedicineCategory::firstOrCreate([
                'name' => trim($data['new_category_name']),
            ], [
                'status' => true,
            ]);
            $data['category_id'] = $category->id;
        }

        unset($data['new_category_name']);

        $medicine->update(array_merge($data, [
            'requires_prescription' => $request->boolean('requires_prescription'),
            'track_stock' => $request->boolean('track_stock', true),
        ]));

        return redirect()->route('manager.medicines.index')->with('success', 'Medicine updated.');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return redirect()->route('manager.medicines.index')->with('success', 'Medicine deleted.');
    }
}
