<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\ServicePackagePurchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
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
        $query = Appointment::with(['customer', 'staff', 'servicePackagePurchase.package'])
            ->where('restaurant_id', $restaurantId)
            ->orderBy('starts_at');

        if ($request->filled('status') && in_array($request->status, self::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('starts_at', $request->date);
        }

        $appointments = $query->paginate(20)->withQueryString();

        return view('manager.appointments.index', compact('appointments'));
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
        $packages = ServicePackagePurchase::with('package')->where('restaurant_id', $restaurantId)->where('status', 'active')->where('remaining_visits', '>', 0)->whereDate('ends_at', '>=', today())->latest()->get();

        return view('manager.appointments.create', compact('customers', 'staff', 'packages'));
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'service_package_purchase_id' => ['nullable', 'integer', Rule::exists('service_package_purchases', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId)->where('status', 'active')->where('remaining_visits', '>', 0))],
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'service_name' => 'required|string|max:150',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => ['required', Rule::in(self::STATUSES)],
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($validated, $restaurantId): void {
            if (! empty($validated['service_package_purchase_id'])) {
                $package = ServicePackagePurchase::where('restaurant_id', $restaurantId)->lockForUpdate()->findOrFail($validated['service_package_purchase_id']);
                abort_unless((int) $package->customer_id === (int) $validated['customer_id'] && $package->status === 'active' && $package->remaining_visits > 0 && Carbon::parse($package->ends_at)->isFuture(), 422, 'The selected package is not valid for this customer or appointment date.');
                $package->decrement('remaining_visits');
                if ($package->fresh()->remaining_visits < 1) $package->update(['status' => 'completed']);
            }
            Appointment::create([...$validated, 'restaurant_id' => $restaurantId]);
        });

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
