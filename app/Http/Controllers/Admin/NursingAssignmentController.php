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

        $showHistory = request()->boolean('history');
        return view('manager.nursing.index', [
            'showHistory' => $showHistory,
            'assignments' => NursingAssignment::with(['admission.patient', 'nurse'])
                ->when(! $showHistory, fn ($query) => $query->where('status', 'active'))
                ->latest('assigned_at')->paginate(20),
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
            'shift' => ['required', Rule::in(['morning', 'evening', 'night'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $workload = NursingAssignment::where('nurse_id', $data['nurse_id'])->where('status', 'active')->count();
        if ($workload >= 5) {
            return back()->withErrors(['nurse_id' => 'This nurse already has the maximum of five active assignments.']);
        }
        $duplicate = NursingAssignment::where('hospital_admission_id', $data['hospital_admission_id'])->where('status', 'active')->exists();
        if ($duplicate) {
            return back()->withErrors(['hospital_admission_id' => 'This admission already has an active nursing assignment.']);
        }
        NursingAssignment::create($data + ['restaurant_id' => $restaurantId, 'status' => 'active']);
        return back()->with('success', 'Nurse assigned.');
    }

    public function reassign(Request $request, NursingAssignment $assignment)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->effectiveRestaurantId() === $assignment->restaurant_id, 404);
        abort_unless($assignment->status === 'active', 422);
        $restaurantId = (int) $user->effectiveRestaurantId();
        $data = $request->validate([
            'nurse_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId)->where('staff_type', 'nurse')->where('is_active', true))],
            'shift' => ['required', Rule::in(['morning', 'evening', 'night'])],
        ]);
        if (NursingAssignment::where('nurse_id', $data['nurse_id'])->where('status', 'active')->count() >= 5) {
            return back()->withErrors(['nurse_id' => 'This nurse already has the maximum of five active assignments.']);
        }
        $assignment->update($data);
        return back()->with('success', 'Nurse assignment updated.');
    }

    public function end(Request $request, NursingAssignment $assignment)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->effectiveRestaurantId() === $assignment->restaurant_id, 404);
        abort_unless($assignment->status === 'active', 422);
        $data = $request->validate(['status' => ['required', Rule::in(['completed', 'cancelled'])]]);
        $assignment->update(['status' => $data['status'], 'ended_at' => now()]);
        return back()->with('success', 'Nursing assignment ended.');
    }
}
