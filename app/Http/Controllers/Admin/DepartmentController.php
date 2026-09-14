<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    private function restaurantId(): int
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $restaurantId = $user->effectiveRestaurantId();
        abort_unless($restaurantId !== null, 403);

        return (int) $restaurantId;
    }

    public function index()
    {
        $departments = Department::query()->orderBy('name')->paginate(20);

        return view('manager.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('manager.departments.create');
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();
        $data = $request->validate($this->rules($restaurantId));
        $data['restaurant_id'] = $restaurantId;
        Department::create($data);

        return redirect()->route('manager.departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department)
    {
        return view('manager.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate($this->rules($this->restaurantId(), $department->id));
        $department->update($data);

        return redirect()->route('manager.departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('manager.departments.index')->with('success', 'Department deleted.');
    }

    private function rules(int $restaurantId, ?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('departments', 'code')->where(fn ($query) => $query->where('restaurant_id', $restaurantId))->ignore($ignoreId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
