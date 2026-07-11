<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalRecordController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user && $user->hasModuleAccess('medical-records'), 403);

        $records = collect();

        return view('admin.medical-records.index', compact('records'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasModuleAccess('medical-records'), 403);

        $request->validate([
            'patient_name' => 'required|string|max:255',
            'medicine_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        return redirect()->route('manager.medical-records.index')->with('success', 'Medical record saved.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasModuleAccess('medical-records'), 403);

        return redirect()->route('manager.medical-records.index')->with('success', 'Medical record removed.');
    }
}
