<?php

namespace App\Http\Controllers\Admin;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionFeature;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlanController extends Controller
{
    protected function ensureSuperAdmin(): void
    {
        $user = Auth::user();

        abort_unless($user && $user->isSuperAdmin(), 403, 'This area is only accessible to the platform super admin.');
    }

    /**
     * Show all subscription plans (super admin only).
     */
    public function index()
    {
        $this->ensureSuperAdmin();

        $plans = SubscriptionPlan::withCount('features', 'restaurants')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.subscription-plans.index', compact('plans'));
    }

    /**
     * Show create plan form.
     */
    public function create()
    {
        $this->ensureSuperAdmin();

        $features = SubscriptionFeature::orderBy('sort_order')->get();
        return view('admin.subscription-plans.create', compact('features'));
    }

    /**
     * Store new subscription plan.
     */
    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:subscription_plans',
            'slug' => 'required|string|max:100|unique:subscription_plans|regex:/^[a-z\-]+$/',
            'description' => 'nullable|string|max:500',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'trial_days' => 'required|integer|min:0|max:365',
            'max_staff' => 'required|integer|min:1',
            'max_menu_items' => 'required|integer|min:1',
            'max_modules' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'exists:subscription_features,id',
        ]);

        $features = $validated['features'] ?? [];
        unset($validated['features']);

        $plan = SubscriptionPlan::create($validated);

        if (!empty($features)) {
            $plan->features()->attach($features);
        }

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Show edit plan form.
     */
    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $this->ensureSuperAdmin();

        $features = SubscriptionFeature::orderBy('sort_order')->get();
        $selectedFeatures = $subscriptionPlan->features()->pluck('id')->toArray();

        return view('admin.subscription-plans.edit', compact('subscriptionPlan', 'features', 'selectedFeatures'));
    }

    /**
     * Update subscription plan.
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:subscription_plans,name,' . $subscriptionPlan->id,
            'slug' => 'required|string|max:100|unique:subscription_plans,slug,' . $subscriptionPlan->id . '|regex:/^[a-z\-]+$/',
            'description' => 'nullable|string|max:500',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'trial_days' => 'required|integer|min:0|max:365',
            'max_staff' => 'required|integer|min:1',
            'max_menu_items' => 'required|integer|min:1',
            'max_modules' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'exists:subscription_features,id',
        ]);

        $features = $validated['features'] ?? [];
        unset($validated['features']);

        $subscriptionPlan->update($validated);
        $subscriptionPlan->features()->sync($features);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Delete subscription plan.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $this->ensureSuperAdmin();

        if ($subscriptionPlan->restaurants()->exists()) {
            return redirect()->route('admin.subscription-plans.index')
                ->with('error', 'Cannot delete: restaurants are subscribed to this plan.');
        }

        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan deleted successfully.');
    }
}
