<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalAdmission;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HospitalAdmissionController extends Controller
{
    private function restaurantId(): int
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $id = $user->effectiveRestaurantId();
        abort_unless($id !== null, 403);
        return (int) $id;
    }

    public function index()
    {
        $admissions = HospitalAdmission::with(['patient', 'doctor', 'department'])->latest('admitted_at')->paginate(20);
        return view('manager.admissions.index', compact('admissions'));
    }

    public function create()
    {
        return view('manager.admissions.create', [
            'patients' => Patient::orderBy('name')->get(),
            'doctors' => Doctor::where('status', 'active')->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $data = $request->validate([
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId))],
            'doctor_id' => ['nullable', 'integer', Rule::exists('doctors', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId))],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId)->where('is_active', true))],
            'admitted_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $data['restaurant_id'] = $restaurantId;
        $data['admission_number'] = 'ADM-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        $data['status'] = 'admitted';
        HospitalAdmission::create($data);
        return redirect()->route('manager.admissions.index')->with('success', 'Patient admitted.');
    }

    public function updateStatus(Request $request, HospitalAdmission $admission)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['admitted', 'transferred', 'discharged'])]]);
        $admission->update(['status' => $data['status'], 'discharged_at' => $data['status'] === 'discharged' ? now() : null]);
        return back()->with('success', 'Admission status updated.');
    }
}
