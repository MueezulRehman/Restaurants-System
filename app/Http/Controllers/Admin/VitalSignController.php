<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HospitalAdmission;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VitalSignController extends Controller
{
    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->effectiveRestaurantId(), 403);
        abort_unless($user->isRestaurantManager() || in_array($user->staff_type, ['nurse', 'doctor'], true), 403);
        return $user;
    }

    public function index()
    {
        $user = $this->user();
        $restaurantId = (int) $user->effectiveRestaurantId();
        return view('manager.vitals.index', [
            'vitals' => VitalSign::with(['admission.patient', 'recorder'])->latest('recorded_at')->paginate(20),
            'admissions' => HospitalAdmission::with('patient')->where('restaurant_id', $restaurantId)->whereIn('status', ['admitted', 'transferred'])->orderBy('admitted_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->user();
        $restaurantId = (int) $user->effectiveRestaurantId();
        $data = $request->validate([
            'hospital_admission_id' => ['required', 'integer', Rule::exists('hospital_admissions', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId)->whereIn('status', ['admitted', 'transferred']))],
            'recorded_at' => ['required', 'date'],
            'temperature' => ['nullable', 'numeric', 'between:25,45'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'pulse' => ['nullable', 'integer', 'between:20,250'],
            'respiratory_rate' => ['nullable', 'integer', 'between:5,80'],
            'oxygen_saturation' => ['nullable', 'numeric', 'between:50,100'],
            'weight' => ['nullable', 'numeric', 'between:0.1,500'],
            'pain_score' => ['nullable', 'integer', 'between:0,10'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        VitalSign::create($data + ['restaurant_id' => $restaurantId, 'recorded_by' => $user->id]);
        return back()->with('success', 'Vital signs recorded.');
    }
}
