<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    private function restaurantId(): int
    {
        $id = auth()->user()?->effectiveRestaurantId();
        abort_unless($id, 403);
        return (int) $id;
    }

    public function index()
    {
        $branches = Branch::where('restaurant_id', $this->restaurantId())->latest()->get();
        return view('manager.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => ['required', 'string', 'max:40', Rule::unique('branches', 'code')->where(fn($q) => $q->where('restaurant_id', $id))],
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:40',
        ]);
        Branch::create([...$data, 'restaurant_id' => $id, 'is_active' => true]);
        return back()->with('success', 'Branch created successfully.');
    }

    public function update(Request $request, Branch $branch)
    {
        $id = $this->restaurantId();
        abort_unless($branch->restaurant_id === $id, 404);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => ['required', 'string', 'max:40', Rule::unique('branches', 'code')->ignore($branch->id)->where(fn($q) => $q->where('restaurant_id', $id))],
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:40',
            'is_active' => 'nullable|boolean',
        ]);
        $branch->update([...$data, 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Branch updated successfully.');
    }
}
