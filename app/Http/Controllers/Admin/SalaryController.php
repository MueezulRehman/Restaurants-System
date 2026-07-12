<?php

namespace App\Http\Controllers\Admin;

use App\Models\Salary;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = Salary::with('user')->orderBy('created_at', 'desc');

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('month') && $request->month) {
            $query->whereMonth('created_at', $request->month);
        }

        $salaries = $query->paginate(15);
        $staff = User::whereNotIn('role', ['super_admin', 'admin'])->where('restaurant_id', Auth::user()->effectiveRestaurantId())->orderBy('name')->get();

        $summary = [
            'total_paid' => Salary::sum('amount'),
            'this_month' => Salary::whereMonth('created_at', now()->month)->sum('amount'),
        ];

        return view('admin.salary.index', compact('salaries', 'staff', 'summary'));
    }

    public function create()
    {
        $staff = User::whereNotIn('role', ['super_admin', 'admin'])->where('restaurant_id', Auth::user()->effectiveRestaurantId())->orderBy('name')->get();
        return view('admin.salary.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|date_format:Y-m',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = auth()->id();

        Salary::create($validated);

        return redirect()->route('manager.salary.index')
            ->with('success', 'Salary recorded successfully.');
    }

    public function edit(Salary $salary)
    {
        $staff = User::whereNotIn('role', ['super_admin', 'admin'])->where('restaurant_id', Auth::user()->effectiveRestaurantId())->orderBy('name')->get();
        return view('admin.salary.edit', compact('salary', 'staff'));
    }

    public function update(Request $request, Salary $salary)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|date_format:Y-m',
            'notes' => 'nullable|string|max:500',
        ]);

        $salary->update($validated);

        return redirect()->route('manager.salary.index')
            ->with('success', 'Salary updated successfully.');
    }

    public function destroy(Salary $salary)
    {
        $salary->delete();
        return redirect()->route('manager.salary.index')
            ->with('success', 'Salary deleted successfully.');
    }
}
