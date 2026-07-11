<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user')->orderBy('created_at', 'desc');

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $attendance = $query->paginate(20);

        $staff = User::whereNotIn('role', ['super_admin', 'admin'])->where('restaurant_id', Auth::user()->restaurant_id)->orderBy('name')->get();

        return view('admin.attendance.index', compact('attendance', 'staff'));
    }

    public function create()
    {
        $staff = User::whereNotIn('role', ['super_admin', 'admin'])->where('restaurant_id', Auth::user()->restaurant_id)->orderBy('name')->get();
        return view('admin.attendance.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        Attendance::create($validated);

        return redirect()->route('manager.attendance.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    public function edit(Attendance $attendance)
    {
        $staff = User::whereNotIn('role', ['super_admin', 'admin'])->where('restaurant_id', Auth::user()->restaurant_id)->orderBy('name')->get();
        return view('admin.attendance.edit', compact('attendance', 'staff'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance->update($validated);

        return redirect()->route('manager.attendance.index')
            ->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('manager.attendance.index')
            ->with('success', 'Attendance deleted successfully.');
    }
}
