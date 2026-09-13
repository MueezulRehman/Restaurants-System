<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientAllergy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientAllergyController extends Controller
{
    public function index()
    {
        $allergies = PatientAllergy::with('patient')->latest()->paginate(20);

        return view('manager.medical.patient-allergies.index', compact('allergies'));
    }

    public function create()
    {
        return view('manager.medical.patient-allergies.form', [
            'allergy' => new PatientAllergy(['is_active' => true]),
            'patients' => Patient::orderBy('name')->get(),
            'medicines' => Medicine::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        PatientAllergy::create($this->validated($request));

        return redirect()->route('manager.patient-allergies.index')
            ->with('success', 'Patient allergy recorded.');
    }

    public function edit(PatientAllergy $patientAllergy)
    {
        return view('manager.medical.patient-allergies.form', [
            'allergy' => $patientAllergy,
            'patients' => Patient::orderBy('name')->get(),
            'medicines' => Medicine::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PatientAllergy $patientAllergy)
    {
        $patientAllergy->update($this->validated($request));

        return redirect()->route('manager.patient-allergies.index')
            ->with('success', 'Patient allergy updated.');
    }

    public function destroy(PatientAllergy $patientAllergy)
    {
        $patientAllergy->delete();

        return redirect()->route('manager.patient-allergies.index')
            ->with('success', 'Patient allergy deleted.');
    }

    private function validated(Request $request): array
    {
        $restaurantId = (int) auth()->user()->effectiveRestaurantId();
        $data = $request->validate([
            'patient_id' => ['required', Rule::exists('patients', 'id')->where('restaurant_id', $restaurantId)],
            'allergy_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', Rule::in(['mild', 'moderate', 'severe'])],
            'trigger_medicines' => ['nullable', 'array'],
            'trigger_medicines.*' => [Rule::exists('medicines', 'id')->where('restaurant_id', $restaurantId)],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['restaurant_id'] = $restaurantId;
        $data['trigger_medicines'] = array_values($request->input('trigger_medicines', []));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
