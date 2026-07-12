<?php

namespace App\Http\Controllers\Admin;

use App\Models\MedicineInteraction;
use App\Models\Medicine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MedicineInteractionController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurant;
        
        $interactions = MedicineInteraction::with('medicineFirst', 'medicineSecond')
            ->whereHas('medicineFirst', function ($q) use ($restaurant) {
                $q->where('restaurant_id', $restaurant->id);
            })
            ->paginate(15);

        return view('admin.medical.interactions.index', [
            'interactions' => $interactions,
            'restaurant' => $restaurant,
        ]);
    }

    public function create()
    {
        $restaurant = auth()->user()->restaurant;
        $medicines = Medicine::where('restaurant_id', $restaurant->id)->orderBy('name')->get();

        return view('admin.medical.interactions.form', [
            'interaction' => new MedicineInteraction(),
            'restaurant' => $restaurant,
            'medicines' => $medicines,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_id_1' => 'required|exists:medicines,id|different:medicine_id_2',
            'medicine_id_2' => 'required|exists:medicines,id',
            'interaction_type' => 'required|in:contraindicated,serious,moderate,mild',
            'interaction_description' => 'required|string',
            'recommended_action' => 'nullable|string',
            'source' => 'nullable|string|max:100',
        ]);

        // Check if interaction already exists (in either direction)
        if (MedicineInteraction::hasInteraction($validated['medicine_id_1'], $validated['medicine_id_2'])) {
            return redirect()->back()->withErrors(['medicine_id_2' => 'This interaction already exists']);
        }

        MedicineInteraction::create($validated);

        return redirect()->route('manager.medicine-interactions.index')
            ->with('success', 'Medicine interaction added successfully');
    }

    public function edit(MedicineInteraction $medicineInteraction)
    {
        $restaurant = Auth::user()?->restaurant;
        $medicines = Medicine::where('restaurant_id', $restaurant->id)->orderBy('name')->get();

        return view('admin.medical.interactions.form', [
            'interaction' => $medicineInteraction,
            'restaurant' => $restaurant,
            'medicines' => $medicines,
        ]);
    }

    public function update(Request $request, MedicineInteraction $medicineInteraction)
    {
        $validated = $request->validate([
            'interaction_type' => 'required|in:contraindicated,serious,moderate,mild',
            'interaction_description' => 'required|string',
            'recommended_action' => 'nullable|string',
            'source' => 'nullable|string|max:100',
        ]);

        $medicineInteraction->update($validated);

        return redirect()->route('manager.medicine-interactions.index')
            ->with('success', 'Medicine interaction updated successfully');
    }

    public function destroy(MedicineInteraction $medicineInteraction)
    {
        $medicineInteraction->delete();

        return redirect()->route('manager.medicine-interactions.index')
            ->with('success', 'Medicine interaction deleted successfully');
    }
}
