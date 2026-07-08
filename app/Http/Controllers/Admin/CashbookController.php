<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cashbook;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CashbookController extends Controller
{
    public function index(Request $request)
    {
        $query = Cashbook::orderBy('created_at', 'desc');

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $entries = $query->paginate(20);

        $summary = [
            'total_income' => Cashbook::where('type', 'income')->sum('amount'),
            'total_expense' => Cashbook::where('type', 'expense')->sum('amount'),
            'balance' => Cashbook::where('type', 'income')->sum('amount') - 
                        Cashbook::where('type', 'expense')->sum('amount'),
        ];

        return view('admin.cashbook.index', compact('entries', 'summary'));
    }

    public function create()
    {
        return view('admin.cashbook.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        Cashbook::create($validated);

        return redirect()->route('admin.cashbook.index')
            ->with('success', 'Cashbook entry created successfully.');
    }
}
