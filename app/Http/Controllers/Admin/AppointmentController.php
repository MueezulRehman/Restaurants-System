<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Services\CommissionService;

class AppointmentController extends Controller
{
    private const STATUSES = ['scheduled', 'confirmed', 'completed', 'cancelled'];

    private function restaurantId(): int
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $restaurantId = $user->effectiveRestaurantId();
        abort_unless($restaurantId !== null, 403);

        return (int) $restaurantId;
    }

    public function index(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $query = Appointment::with(['customer', 'staff'])
            ->where('restaurant_id', $restaurantId)
            ->orderBy('starts_at');

        if ($request->filled('status') && in_array($request->status, self::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('starts_at', $request->date);
        }

        $appointments = $query->paginate(20)->withQueryString();

        return view('admin.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $restaurantId = $this->restaurantId();
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $staff = User::where('restaurant_id', $restaurantId)
            ->whereIn('role', ['admin', 'manager', 'staff'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.appointments.create', compact('customers', 'staff'));
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'service_name' => 'required|string|max:150',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => ['required', Rule::in(self::STATUSES)],
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        Appointment::create([...$validated, 'restaurant_id' => $restaurantId]);

        return redirect()->route('manager.appointments.index')->with('success', 'Appointment booked successfully.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        abort_unless($appointment->restaurant_id === $this->restaurantId(), 404);
        $validated = $request->validate(['status' => ['required', Rule::in(self::STATUSES)]]);
        $appointment->update(['status' => $validated['status']]);
        if ($validated['status'] === 'completed') {
            CommissionService::generateForAppointment($appointment->fresh());
        }

        return back()->with('success', 'Appointment status updated.');
    }

    public function destroy(Appointment $appointment)
    {
        abort_unless($appointment->restaurant_id === $this->restaurantId(), 404);
        $appointment->delete();

        return back()->with('success', 'Appointment cancelled and removed.');
    }
}
