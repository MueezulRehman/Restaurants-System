<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Visit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    private function restaurantId(): int
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $restaurantId = $user->effectiveRestaurantId();
        abort_unless($restaurantId !== null, 403);

        return (int) $restaurantId;
    }

    public function index()
    {
        $patients = Patient::query()->orderBy('name')->paginate(20);

        return view('manager.patients.index', compact('patients'));
    }

    public function create()
    {
        return view('manager.patients.create');
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $data = $request->validate($this->rules($restaurantId));
        if ($existing = $this->findDuplicate($data, $restaurantId)) {
            return redirect()->route('manager.patients.edit', $existing)
                ->withErrors(['cnic' => 'Patient already registered. Open the existing Patient record instead.'])
                ->withInput();
        }
        $data['restaurant_id'] = $restaurantId;

        Patient::create($data);

        return redirect()->route('manager.patients.index')->with('success', 'Patient created.');
    }

    public function edit(Patient $patient)
    {
        return view('manager.patients.edit', compact('patient'));
    }

    public function history(Patient $patient)
    {
        $visits = Visit::with('doctor')
            ->where('restaurant_id', $this->restaurantId())
            ->where('patient_id', $patient->id)
            ->latest('checked_in_at')
            ->paginate(15);

        return view('manager.patients.history', compact('patient', 'visits'));
    }

    public function update(Request $request, Patient $patient)
    {
        $restaurantId = $this->restaurantId();
        $data = $request->validate($this->rules($restaurantId, $patient->id));
        if ($existing = $this->findDuplicate($data, $restaurantId, $patient->id)) {
            return redirect()->route('manager.patients.edit', $existing)
                ->withErrors(['cnic' => 'Patient already registered. Open the existing Patient record instead.'])
                ->withInput();
        }
        $patient->update($data);

        return redirect()->route('manager.patients.index')->with('success', 'Patient updated.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('manager.patients.index')->with('success', 'Patient deleted.');
    }

    private function findDuplicate(array $data, int $restaurantId, ?int $ignoreId = null): ?Patient
    {
        $name = mb_strtolower(trim($data['name']));
        $query = Patient::query()->where('restaurant_id', $restaurantId);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if (! empty($data['cnic'])) {
            return $query->where('cnic', $data['cnic'])->first();
        }

        return $query
            ->whereRaw('LOWER(name) = ?', [$name])
            ->where('phone', $data['phone'])
            ->where(function ($query) use ($data): void {
                if (! empty($data['date_of_birth'])) {
                    $query->whereDate('date_of_birth', $data['date_of_birth']);
                } else {
                    $query->whereNull('date_of_birth');
                }
            })
            ->first();
    }

    private function rules(int $restaurantId, ?int $ignoreId = null): array
    {
        return [
            'patient_number' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                'unique:patients,patient_number,' . ($ignoreId ?? 'NULL') . ',id,restaurant_id,' . $restaurantId,
            ],
            'name' => ['required', 'string', 'max:255'],
            'cnic' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'is_dependent' => ['boolean'],
            'guardian_name' => ['required_if:is_dependent,1', 'nullable', 'string', 'max:255'],
            'guardian_cnic' => ['required_if:is_dependent,1', 'nullable', 'string', 'max:30'],
            'guardian_phone' => ['required_if:is_dependent,1', 'nullable', 'string', 'max:40'],
            'relationship' => [
                'required_if:is_dependent,1',
                'nullable',
                Rule::in(['self', 'father', 'mother', 'son', 'daughter', 'spouse', 'brother', 'sister', 'guardian', 'other']),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
        ];
    }
}
