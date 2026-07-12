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
            'total_income' => Cashbook::where('type', 'in')->sum('amount'),
            'total_expense' => Cashbook::where('type', 'out')->sum('amount'),
            'balance' => Cashbook::where('type', 'in')->sum('amount') - 
                        Cashbook::where('type', 'out')->sum('amount'),
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
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:255',
        ]);

        $data = [
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'source' => $validated['reference'] ?? null,
            'date' => now()->toDateString(),
            'created_by' => auth()->id(),
        ];

        Cashbook::create($data);

        return redirect()->route('manager.cashbook.index')
            ->with('success', 'Cashbook entry created successfully.');
    }
}
