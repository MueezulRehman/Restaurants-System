<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\QueueCounter;
use App\Models\QueueEntry;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Visit;
use App\Services\QueueNotificationService;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MedicalQueueController extends Controller
{
    private function restaurantId(): int
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->effectiveRestaurantId(), 403);
        return (int) $user->effectiveRestaurantId();
    }

    public function index(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $doctors = Doctor::where('restaurant_id', $restaurantId)->where('status', 'active')->orderBy('name')->get();
        $doctorId = $request->integer('doctor_id') ?: $doctors->first()?->id;
        $entries = $doctorId
            ? QueueEntry::with(['patient', 'doctor'])->where('restaurant_id', $restaurantId)->where('doctor_id', $doctorId)->whereDate('queue_date', today())->orderBy('token_number')->get()
            : collect();
        return view('manager.medical-queue.index', compact('doctors', 'entries', 'doctorId'));
    }

    public function create()
    {
        $restaurantId = $this->restaurantId();
        $doctors = Doctor::where('restaurant_id', $restaurantId)->where('status', 'active')->orderBy('name')->get();
        $patients = Patient::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $appointments = Appointment::with('patient')->where('restaurant_id', $restaurantId)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereDate('starts_at', today())
            ->orderBy('starts_at')->get();
        return view('manager.medical-queue.create', compact('doctors', 'patients', 'appointments'));
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $data = $request->validate([
            'doctor_id' => ['required', Rule::exists('doctors', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId)->where('status', 'active'))],
            'patient_id' => [ 'required', Rule::exists('patients', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId))],
            'appointment_id' => ['nullable', Rule::exists('appointments', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId)->whereIn('status', ['scheduled', 'confirmed']))],
            'reason' => ['nullable', 'string', 'max:500'],
            'notification_consent' => ['nullable', 'boolean'],
        ]);

        $entry = DB::transaction(function () use ($data, $restaurantId): QueueEntry {
            if (! empty($data['appointment_id'])) {
                $appointment = Appointment::where('restaurant_id', $restaurantId)->lockForUpdate()->findOrFail($data['appointment_id']);
                abort_unless($appointment->starts_at->isSameDay(today()), 422, 'Only today’s appointments can be checked in.');
                abort_if(Visit::where('restaurant_id', $restaurantId)->where('appointment_id', $appointment->id)->exists(), 422, 'This appointment has already been checked in.');
                if ($appointment->patient_id && (int) $appointment->patient_id !== (int) $data['patient_id']) {
                    abort(422, 'The selected patient does not match this appointment.');
                }
                $appointment->update(['patient_id' => $data['patient_id'], 'status' => 'confirmed']);
            }
            Patient::where('restaurant_id', $restaurantId)
                ->whereKey($data['patient_id'])
                ->update(['notification_consent' => (bool) ($data['notification_consent'] ?? false)]);
            $date = today()->toDateString();
            $counter = QueueCounter::where('restaurant_id', $restaurantId)->where('doctor_id', $data['doctor_id'])->whereDate('queue_date', $date)->lockForUpdate()->first();
            if (! $counter) {
                $counter = QueueCounter::create(['restaurant_id' => $restaurantId, 'doctor_id' => $data['doctor_id'], 'queue_date' => $date, 'last_issued_number' => 0]);
                $counter = QueueCounter::whereKey($counter->id)->lockForUpdate()->first();
            }
            $number = $counter->last_issued_number + 1;
            $counter->update(['last_issued_number' => $number]);
            $visit = Visit::create([...$data, 'restaurant_id' => $restaurantId, 'status' => 'checked_in', 'checked_in_at' => now()]);
            return QueueEntry::create(['restaurant_id' => $restaurantId, 'doctor_id' => $data['doctor_id'], 'patient_id' => $data['patient_id'], 'visit_id' => $visit->id, 'queue_date' => $date, 'token_number' => $number, 'public_token' => Str::random(48), 'status' => 'waiting']);
        });

        return redirect()->route('manager.medical-queue.print', $entry)->with('success', 'Patient checked in and token issued.');
    }

    public function print(QueueEntry $queueEntry)
    {
        abort_unless($queueEntry->restaurant_id === $this->restaurantId(), 404);
        $queueEntry = QueueEntry::with(['patient', 'doctor'])->findOrFail($queueEntry->getKey());
        $restaurant = Restaurant::on(config('tenancy.central_connection', env('DB_CONNECTION', 'mysql')))->findOrFail($queueEntry->restaurant_id);
        return view('manager.medical-queue.print', compact('queueEntry', 'restaurant'));
    }

    public function publicToken(string $restaurantSlug, string $publicToken)
    {
        $restaurant = Restaurant::where('slug', $restaurantSlug)->firstOrFail();
        Tenancy::configureTenantConnection($restaurant);
        $centralConnection = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $row = DB::connection($centralConnection)->table('queue_entries')
            ->where('public_token', $publicToken)
            ->where('restaurant_id', $restaurant->id)
            ->first();
        $queueEntry = $row
            ? QueueEntry::on($centralConnection)->withoutGlobalScopes()->with('doctor')->find($row->id)
            : null;
        if (! $queueEntry && $restaurant->hasTenantDatabase()) {
            $queueEntry = QueueEntry::on(config('tenancy.connection', 'tenant'))->withoutGlobalScopes()
                ->with('doctor')
                ->where('public_token', $publicToken)
                ->where('restaurant_id', $restaurant->id)
                ->first();
        }
        abort_unless($queueEntry, 404);

        return response()
            ->view('medical.queue-token', compact('queueEntry'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function publicDisplay(string $restaurantSlug)
    {
        $restaurant = Restaurant::where('slug', $restaurantSlug)->firstOrFail();
        Tenancy::configureTenantConnection($restaurant);
        $connection = $restaurant->hasTenantDatabase()
            ? config('tenancy.connection', 'tenant')
            : config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $entries = QueueEntry::on($connection)->withoutGlobalScopes()
            ->with('doctor')
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('queue_date', today())
            ->whereIn('status', ['called', 'in_progress'])
            ->orderByDesc('called_at')
            ->get();

        return response()
            ->view('medical.queue-display', compact('restaurant', 'entries'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function next(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $data = $request->validate(['doctor_id' => ['required', 'integer', Rule::exists('doctors', 'id')->where(fn ($q) => $q->where('restaurant_id', $restaurantId))]]);
        $entry = DB::transaction(function () use ($data, $restaurantId): ?QueueEntry {
            $entry = QueueEntry::where('restaurant_id', $restaurantId)->where('doctor_id', $data['doctor_id'])->whereDate('queue_date', today())->where('status', 'waiting')->orderBy('token_number')->lockForUpdate()->first();
            if (! $entry) return null;
            $entry->update(['status' => 'called', 'called_at' => now()]);
            return $entry;
        });
        if ($entry) {
            $entry->load(['patient', 'doctor']);
            QueueNotificationService::dispatchCalled($entry);
        }
        return back()->with('success', $entry ? "Token {$entry->token_number} called." : 'No waiting patients.');
    }

    public function status(Request $request, QueueEntry $queueEntry)
    {
        abort_unless($queueEntry->restaurant_id === $this->restaurantId(), 404);
        $data = $request->validate(['status' => ['required', Rule::in(['in_progress', 'completed', 'no_show', 'cancelled'])]]);
        $updates = ['status' => $data['status']];
        if ($data['status'] === 'in_progress') { $updates['started_at'] = now(); $queueEntry->visit->update(['status' => 'in_progress', 'started_at' => now()]); }
        if ($data['status'] === 'completed') { $updates['completed_at'] = now(); $queueEntry->visit->update(['status' => 'completed', 'completed_at' => now()]); }
        if ($data['status'] === 'no_show') { $updates['no_show_at'] = now(); $queueEntry->visit->update(['status' => 'no_show']); }
        if ($data['status'] === 'cancelled') { $queueEntry->visit->update(['status' => 'cancelled']); }
        $queueEntry->update($updates);
        return back()->with('success', 'Queue status updated.');
    }
}
