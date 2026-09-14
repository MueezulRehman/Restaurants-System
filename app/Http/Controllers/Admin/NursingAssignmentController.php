<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HospitalAdmission;
use App\Models\NursingAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NursingAssignmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->effectiveRestaurantId(), 403);

        return view('manager.nursing.index', [
            'assignments' => NursingAssignment::with(['admission.patient', 'nurse'])->latest('assigned_at')->paginate(20),
            'admissions' => HospitalAdmission::with('patient')->whereIn('status', ['admitted', 'transferred'])->orderBy('admitted_at')->get(),
            'nurses' => User::where('restaurant_id', $user->effectiveRestaurantId())
                ->where('staff_type', 'nurse')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->effectiveRestaurantId(), 403);
        $restaurantId = (int) $user->effectiveRestaurantId();
        $data = $request->validate([
            'hospital_admission_id' => ['required', 'integer', Rule::exists('hospital_admissions', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId)->whereIn('status', ['admitted', 'transferred']))],
            'nurse_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId)->where('staff_type', 'nurse')->where('is_active', true))],
            'assigned_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        NursingAssignment::create($data + ['restaurant_id' => $restaurantId, 'status' => 'active']);
        return back()->with('success', 'Nurse assigned.');
    }
}
