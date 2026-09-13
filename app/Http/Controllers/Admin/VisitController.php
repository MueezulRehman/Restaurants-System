<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VisitController extends Controller
{
    public function index()
    {
        $visits = Visit::with(['patient', 'doctor', 'queueEntry'])
            ->latest('checked_in_at')
            ->paginate(20);

        return view('manager.visits.index', compact('visits'));
    }

    public function show(Visit $visit)
    {
        $this->assertTenant($visit);
        $visit = Visit::with([
            'patient' => fn ($query) => $query->with([
                'allergies' => fn ($allergyQuery) => $allergyQuery->where('is_active', true),
            ]),
            'doctor',
            'queueEntry',
            'prescriptions',
        ])->findOrFail($visit->getKey());

        return view('manager.visits.show', compact('visit'));
    }

    public function edit(Visit $visit)
    {
        return $this->show($visit);
    }

    public function print(Visit $visit)
    {
        $this->assertTenant($visit);
        $visit = Visit::with(['patient', 'doctor', 'prescriptions'])->findOrFail($visit->getKey());
        return view('manager.visits.print', compact('visit'));
    }

    private function assertTenant(Visit $visit): void
    {
        abort_unless((int) $visit->restaurant_id === (int) auth()->user()?->effectiveRestaurantId(), 404);
    }

    public function update(Request $request, Visit $visit)
    {
        $this->assertTenant($visit);
        $visit = Visit::with('queueEntry')->findOrFail($visit->getKey());
        $data = $request->validate([
            'diagnosis' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['checked_in', 'in_progress', 'completed', 'no_show', 'cancelled'])],
        ]);

        $visit->update($data);
        if ($visit->queueEntry) {
            $queueStatus = match ($data['status']) {
                'completed' => 'completed',
                'no_show' => 'no_show',
                'cancelled' => 'cancelled',
                'in_progress' => 'in_progress',
                default => $visit->queueEntry->status,
            };
            if ($queueStatus !== $visit->queueEntry->status) {
                $visit->queueEntry->update(['status' => $queueStatus]);
            }
        }

        return redirect()->route('manager.visits.show', $visit)->with('success', 'Consultation updated.');
    }
}
