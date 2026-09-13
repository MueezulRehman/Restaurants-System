<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FollowUpReminder;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FollowUpReminderController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $reminders = FollowUpReminder::with(['customer', 'medicalRecord'])->where('restaurant_id', $restaurantId)->orderBy('due_at')->paginate(20);
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $records = MedicalRecord::where('restaurant_id', $restaurantId)->latest()->limit(100)->get();

        return view('manager.follow-up-reminders.index', compact('reminders', 'customers', 'records'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'medical_record_id' => ['nullable', 'integer', Rule::exists('medical_records', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'due_at' => 'required|date',
            'note' => 'nullable|string|max:2000',
        ]);
        FollowUpReminder::create(array_merge($data, ['restaurant_id' => $restaurantId, 'status' => 'scheduled', 'created_by' => Auth::id()]));

        return back()->with('success', 'Follow-up reminder scheduled.');
    }

    public function update(Request $request, FollowUpReminder $followUpReminder)
    {
        abort_unless((int) $followUpReminder->restaurant_id === (int) Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate(['status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled'])]]);
        $followUpReminder->update(array_merge($data, ['completed_at' => $data['status'] === 'completed' ? now() : null]));

        return back()->with('success', 'Follow-up reminder updated.');
    }
}
