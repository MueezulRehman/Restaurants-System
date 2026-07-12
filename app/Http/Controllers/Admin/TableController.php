<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $tables = Table::where('restaurant_id', $restaurantId)->orderBy('number')->get();
        return view('admin.tables.index', compact('tables'));
    }

    public function create()
    {
        return view('admin.tables.create');
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();

        $data = $request->validate([
            'label' => 'nullable|string|max:100',
            'number' => 'nullable|string|max:50',
            'seats' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['restaurant_id'] = $restaurantId;
        $data['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        Table::create($data);

        return redirect()->route('manager.tables.index')->with('success', 'Table created.');
    }

    public function edit(Table $table)
    {
        $this->authorizeTable($table);
        return view('admin.tables.edit', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $this->authorizeTable($table);

        $data = $request->validate([
            'label' => 'nullable|string|max:100',
            'number' => 'nullable|string|max:50',
            'seats' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : $table->is_active;

        $table->update($data);

        return redirect()->route('manager.tables.index')->with('success', 'Table updated.');
    }

    public function destroy(Table $table)
    {
        $this->authorizeTable($table);
        $table->delete();
        return redirect()->route('manager.tables.index')->with('success', 'Table removed.');
    }

    protected function authorizeTable(Table $table)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        if ($table->restaurant_id !== $restaurantId) {
            abort(403);
        }
    }
}
