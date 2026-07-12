<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topping;
use Illuminate\Http\Request;

class ToppingController extends Controller
{
    public function index()
    {
        $toppings = Topping::orderBy('name')->paginate(20);

        return view('admin.toppings.index', compact('toppings'));
    }

    public function create()
    {
        return view('admin.toppings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'type' => 'nullable|string|max:50',
        ]);

        Topping::create($validated);

        return redirect()->route('manager.toppings.index')
            ->with('success', 'Topping created successfully.');
    }

    public function edit(Topping $topping)
    {
        return view('admin.toppings.edit', compact('topping'));
    }

    public function update(Request $request, Topping $topping)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'type' => 'nullable|string|max:50',
        ]);

        $topping->update($validated);

        return redirect()->route('manager.toppings.index')
            ->with('success', 'Topping updated successfully.');
    }

    public function destroy(Topping $topping)
    {
        $topping->delete();

        return redirect()->route('manager.toppings.index')
            ->with('success', 'Topping deleted successfully.');
    }
}
