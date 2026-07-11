<?php

namespace App\Http\Controllers\Admin;

use App\Models\Deal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index()
    {
        $deals = Deal::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.deals.index', compact('deals'));
    }

    public function create()
    {
        return view('admin.deals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $dir = public_path('images/deals');
            if (! is_dir($dir)) mkdir($dir, 0755, true);
            $file = $request->file('image');
            $filename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['image'] = 'deals/'.$filename;
        }

        $validated['is_active'] = $validated['active'] ?? false;
        unset($validated['active']);

        Deal::create($validated);

        return redirect()->route('manager.deals.index')
            ->with('success', 'Deal created successfully.');
    }

    public function edit(Deal $deal)
    {
        return view('admin.deals.edit', compact('deal'));
    }

    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($deal->image && file_exists(public_path('images/'.$deal->image))) {
                @unlink(public_path('images/'.$deal->image));
            }
            $dir = public_path('images/deals');
            if (! is_dir($dir)) mkdir($dir, 0755, true);
            $file = $request->file('image');
            $filename = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['image'] = 'deals/'.$filename;
        }

        $validated['is_active'] = $validated['active'] ?? false;
        unset($validated['active']);

        $deal->update($validated);

        return redirect()->route('manager.deals.index')
            ->with('success', 'Deal updated successfully.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();
        return redirect()->route('manager.deals.index')
            ->with('success', 'Deal deleted successfully.');
    }
}
