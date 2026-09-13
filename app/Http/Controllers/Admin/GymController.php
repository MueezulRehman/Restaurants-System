<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GymCheckIn;
use App\Models\GymMembership;
use App\Models\GymPlan;
use App\Models\GymTrainer;
use App\Models\GymTrainerSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class GymController extends Controller
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
        $restaurantId = $this->restaurantId();
        GymMembership::where('restaurant_id', $restaurantId)
            ->where('status', 'active')
            ->whereDate('ends_at', '<', today())
            ->update(['status' => 'expired']);

        $plans = GymPlan::where('restaurant_id', $restaurantId)->where('is_active', true)->orderBy('name')->get();
        $memberships = GymMembership::with(['customer', 'plan', 'trainer'])
            ->where('restaurant_id', $restaurantId)
            ->latest('ends_at')
            ->paginate(20);
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $checkIns = GymCheckIn::with('customer')->where('restaurant_id', $restaurantId)->latest('checked_in_at')->limit(15)->get();
        $trainers = GymTrainer::where('restaurant_id', $restaurantId)->where('is_active', true)->orderBy('name')->get();
        $schedules = GymTrainerSchedule::with('trainer')->where('restaurant_id', $restaurantId)->where('is_active', true)->orderBy('day_of_week')->orderBy('starts_at')->get();

        return view('manager.gym.index', compact('plans', 'memberships', 'customers', 'checkIns', 'trainers', 'schedules'));
    }

    public function storeTrainer(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $data = $request->validate(['name' => 'required|string|max:150', 'specialty' => 'nullable|string|max:150', 'phone' => 'nullable|string|max:40']);
        GymTrainer::create([...$data, 'restaurant_id' => $restaurantId, 'is_active' => true]);
        return back()->with('success', 'Trainer added.');
    }

    public function storePlan(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('gym_plans', 'name')->where(fn($q) => $q->where('restaurant_id', $restaurantId))],
            'duration_days' => 'required|integer|min:1|max:3650',
            'price' => 'required|numeric|min:0',
        ]);
        GymPlan::create([...$validated, 'restaurant_id' => $restaurantId]);

        return back()->with('success', 'Membership plan created.');
    }

    public function storeMembership(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId))],
            'gym_plan_id' => ['required', 'integer', Rule::exists('gym_plans', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId)->where('is_active', true))],
            'gym_trainer_id' => ['nullable', 'integer', Rule::exists('gym_trainers', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId)->where('is_active', true))],
            'starts_at' => 'required|date',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        $plan = GymPlan::where('restaurant_id', $restaurantId)->findOrFail($validated['gym_plan_id']);
        $startsAt = now()->parse($validated['starts_at'])->startOfDay();
        GymMembership::create([
            'restaurant_id' => $restaurantId,
            'customer_id' => $validated['customer_id'],
            'gym_plan_id' => $plan->id,
            'gym_trainer_id' => $validated['gym_trainer_id'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addDays($plan->duration_days - 1),
            'status' => 'active',
            'amount_paid' => $validated['amount_paid'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Membership activated.');
    }

    public function assignTrainer(Request $request, GymMembership $membership)
    {
        $restaurantId = $this->restaurantId();
        abort_unless($membership->restaurant_id === $restaurantId, 404);
        $data = $request->validate(['gym_trainer_id' => ['nullable', 'integer', Rule::exists('gym_trainers', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId)->where('is_active', true))]]);
        $membership->update(['gym_trainer_id' => $data['gym_trainer_id'] ?? null]);
        return back()->with('success', 'Trainer assignment updated.');
    }

    public function storeSchedule(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $data = $request->validate([
            'gym_trainer_id' => ['required', 'integer', Rule::exists('gym_trainers', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId)->where('is_active', true))],
            'day_of_week' => 'required|integer|min:0|max:6',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'capacity' => 'required|integer|min:1|max:1000',
        ]);
        GymTrainerSchedule::create([...$data, 'restaurant_id' => $restaurantId, 'is_active' => true]);
        return back()->with('success', 'Trainer schedule saved.');
    }

    public function renew(Request $request, GymMembership $membership)
    {
        $restaurantId = $this->restaurantId();
        abort_unless($membership->restaurant_id === $restaurantId, 404);
        $validated = $request->validate(['amount_paid' => 'nullable|numeric|min:0']);
        $plan = $membership->plan;
        $endsAt = Carbon::parse($membership->ends_at);
        $start = $endsAt->isFuture() ? $endsAt->copy()->addDay() : now()->startOfDay();
        $membership->update([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addDays($plan->duration_days - 1),
            'status' => 'active',
            'amount_paid' => $validated['amount_paid'] ?? $membership->amount_paid,
        ]);

        return back()->with('success', 'Membership renewed.');
    }

    public function checkIn(GymMembership $membership)
    {
        $restaurantId = $this->restaurantId();
        abort_unless($membership->restaurant_id === $restaurantId, 404);
        abort_unless($membership->status === 'active' && ! Carbon::parse($membership->ends_at)->isPast(), 422, 'This membership is not active.');

        if ($membership->gym_trainer_id) {
            $now = now();
            $schedule = GymTrainerSchedule::where('restaurant_id', $restaurantId)->where('gym_trainer_id', $membership->gym_trainer_id)->where('day_of_week', $now->dayOfWeek)->where('starts_at', '<=', $now->format('H:i:s'))->where('ends_at', '>=', $now->format('H:i:s'))->where('is_active', true)->first();
            if ($schedule) {
                $checkIns = GymCheckIn::where('restaurant_id', $restaurantId)->whereDate('checked_in_at', today())->whereHas('membership', fn($query) => $query->where('gym_trainer_id', $membership->gym_trainer_id))->count();
                abort_if($checkIns >= $schedule->capacity, 422, 'This trainer has reached the check-in capacity for the current schedule.');
            }
        }

        GymCheckIn::create([
            'restaurant_id' => $restaurantId,
            'gym_membership_id' => $membership->id,
            'customer_id' => $membership->customer_id,
            'checked_in_at' => now(),
        ]);

        return back()->with('success', 'Member checked in.');
    }
}
