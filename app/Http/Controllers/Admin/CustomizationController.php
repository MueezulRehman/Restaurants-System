<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomFieldDefinition;
use App\Models\WorkflowDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomizationController extends Controller
{
    private function restaurantId(): int
    {
        $id = auth()->user()?->effectiveRestaurantId();
        abort_unless($id, 403);
        return (int) $id;
    }

    public function index()
    {
        $id = $this->restaurantId();
        $fields = CustomFieldDefinition::where('restaurant_id', $id)->orderBy('entity_type')->orderBy('name')->get();
        $workflows = WorkflowDefinition::where('restaurant_id', $id)->latest()->get();
        return view('manager.customization.index', compact('fields', 'workflows'));
    }

    public function storeField(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['entity_type' => 'required|string|max:60', 'name' => 'required|string|max:100', 'field_key' => 'nullable|string|max:80', 'field_type' => ['required', Rule::in(['text', 'number', 'date', 'select', 'boolean'])], 'options' => 'nullable|string|max:2000', 'is_required' => 'nullable|boolean']);
        $data['field_key'] = Str::slug($data['field_key'] ?: $data['name'], '_');
        $data['options'] = $data['options'] ? array_values(array_filter(array_map('trim', explode(',', $data['options']))) ?: []) : null;
        CustomFieldDefinition::create([...$data, 'restaurant_id' => $id, 'is_required' => $request->boolean('is_required'), 'is_active' => true]);
        return back()->with('success', 'Custom field created.');
    }

    public function storeWorkflow(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['entity_type' => 'required|string|max:60', 'name' => 'required|string|max:100', 'statuses' => 'required|string|max:2000', 'transitions' => 'nullable|string|max:4000']);
        $statuses = array_values(array_filter(array_map('trim', explode(',', $data['statuses']))));
        abort_if(count($statuses) < 2, 422, 'A workflow needs at least two statuses.');
        WorkflowDefinition::create(['restaurant_id' => $id, 'entity_type' => $data['entity_type'], 'name' => $data['name'], 'statuses' => $statuses, 'transitions' => $data['transitions'] ? json_decode($data['transitions'], true) : null, 'is_active' => true]);
        return back()->with('success', 'Custom workflow created.');
    }
}
