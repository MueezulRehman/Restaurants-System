<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\Module;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ModuleService;
use App\Services\SubscriptionManager;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $restaurants = Restaurant::withCount('users')->latest()->get();

        return view('admin.restaurants.index', compact('restaurants'));
    }

    /**
     * Super admin "enters" a restaurant — from this point until they exit,
     * they're treated as that restaurant's own manager: /manager/* routes
     * become reachable, and every tenant-scoped model (menu, POS, cashbook,
     * staff, etc.) is scoped to this restaurant, exactly like a real
     * manager would experience.
     */
    public function enter(Restaurant $restaurant)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        Tenancy::enter($restaurant);

        return redirect()->route('manager.dashboard')
            ->with('success', "You're now managing {$restaurant->name}.");
    }

    public function exit()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        Tenancy::exit();

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Back to the platform-wide view.');
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        ModuleService::ensureDefaults();

        $businessTypes = BusinessType::where('is_active', true)->orderBy('sort_order')->get();
        $modules = Module::where('is_active', true)->orderBy('sort_order')->get();
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        $selectedTypeId = old('business_type_id') ?: ($businessTypes->first()?->id ?? null);
        $selectedModules = old('enabled_modules', []);
        $selectedPlanSlug = old('plan');

        if (empty($selectedModules) && $selectedTypeId) {
            $selectedModules = BusinessType::find($selectedTypeId)?->modules()->pluck('id')->toArray() ?? [];
        }

        return view('admin.restaurants.create', compact('businessTypes', 'modules', 'selectedModules', 'subscriptionPlans', 'selectedPlanSlug'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        ModuleService::ensureDefaults();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:restaurants,slug',
            'business_type_id' => 'required|exists:business_types,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'custom_domain' => 'nullable|string|max:255|unique:restaurants,custom_domain',
            'domain' => 'nullable|string|max:255|unique:restaurants,domain',
            'plan' => 'nullable|string|max:50',
            'status' => 'nullable|in:trial,active,suspended,cancelled',
            'logo_path' => 'nullable|image|max:2048',
            'enabled_modules' => 'nullable|array',
            'enabled_modules.*' => 'integer|exists:modules,id',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:25',
            'owner_password' => 'nullable|string|min:8',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        if ($request->hasFile('logo_path')) {
            $validated['logo_path'] = $request->file('logo_path')->store('restaurant-logos', 'public');
        }

        $selectedModuleKeys = [];
        if ($request->filled('enabled_modules')) {
            $selectedModuleKeys = Module::whereIn('id', $request->input('enabled_modules'))->pluck('key')->toArray();
        }

        if (empty($selectedModuleKeys)) {
            $selectedModuleKeys = BusinessType::find($validated['business_type_id'])
                ->modules()->pluck('key')->toArray();
        }

        $restaurantData = $validated;
        if (Schema::hasColumn('restaurants', 'enabled_modules')) {
            $restaurantData['enabled_modules'] = $selectedModuleKeys;
        } else {
            unset($restaurantData['enabled_modules']);
        }

        $restaurant = Restaurant::create($restaurantData);

        $this->syncSubscriptionPlan($restaurant, $restaurantData['plan'] ?? null, $restaurantData['status'] ?? 'trial');

        $ownerCredentials = null;

        if ($request->filled('owner_email')) {
            $password = $request->input('owner_password') ?: Str::random(10);

            $owner = User::create([
                'name' => $request->input('owner_name') ?: $validated['name'] . ' Owner',
                'email' => $request->input('owner_email'),
                'phone' => $request->input('owner_phone'),
                'role' => 'admin',
                'restaurant_id' => $restaurant->id,
                'password' => Hash::make($password),
            ]);

            $ownerCredentials = [
                'email' => $owner->email,
                'password' => $password,
            ];
        }

        $message = 'Restaurant created successfully.';

        if ($ownerCredentials) {
            $message .= ' Owner login: ' . $ownerCredentials['email'] . ' / ' . $ownerCredentials['password'];
        }

        return redirect()->route('admin.restaurants.index')->with('success', $message);
    }

    public function edit(Restaurant $restaurant)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        ModuleService::ensureDefaults();

        $businessTypes = BusinessType::where('is_active', true)->orderBy('sort_order')->get();
        $modules = Module::where('is_active', true)->orderBy('sort_order')->get();
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        $selectedModules = old('enabled_modules');
        if (empty($selectedModules)) {
            if ($restaurant->enabled_modules && is_array($restaurant->enabled_modules)) {
                $selectedModules = Module::whereIn('key', $restaurant->enabled_modules)->pluck('id')->toArray();
            } else {
                $selectedModules = $restaurant->businessType?->modules()->pluck('id')->toArray() ?? [];
            }
        }

        $selectedPlanSlug = old('plan', $restaurant->plan ?? $restaurant->subscription?->plan?->slug ?? null);

        return view('admin.restaurants.edit', compact('restaurant', 'businessTypes', 'modules', 'selectedModules', 'subscriptionPlans', 'selectedPlanSlug'));
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:restaurants,slug,' . $restaurant->id,
            'business_type_id' => 'required|exists:business_types,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'custom_domain' => 'nullable|string|max:255|unique:restaurants,custom_domain,' . $restaurant->id,
            'domain' => 'nullable|string|max:255|unique:restaurants,domain,' . $restaurant->id,
            'plan' => 'nullable|string|max:50',
            'status' => 'nullable|in:trial,active,suspended,cancelled',
            'logo_path' => 'nullable|image|max:2048',
            'enabled_modules' => 'nullable|array',
            'enabled_modules.*' => 'integer|exists:modules,id',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        if ($request->hasFile('logo_path')) {
            if ($restaurant->logo_path) {
                Storage::disk('public')->delete($restaurant->logo_path);
            }
            $validated['logo_path'] = $request->file('logo_path')->store('restaurant-logos', 'public');
        }

        $updateData = $validated;

        if (Schema::hasColumn('restaurants', 'enabled_modules')) {
            if ($request->has('enabled_modules')) {
                $updateData['enabled_modules'] = Module::whereIn('id', $request->input('enabled_modules', []))->pluck('key')->toArray();
            } elseif ($restaurant->business_type_id !== $validated['business_type_id']) {
                $updateData['enabled_modules'] = BusinessType::find($validated['business_type_id'])
                    ->modules()->pluck('key')->toArray();
            } else {
                $updateData['enabled_modules'] = $restaurant->enabled_modules ?? $restaurant->businessType?->modules()->pluck('key')->toArray() ?? [];
            }
        } else {
            unset($updateData['enabled_modules']);
        }

        $restaurant->update($updateData);
        $this->syncSubscriptionPlan($restaurant, $updateData['plan'] ?? null, $updateData['status'] ?? $restaurant->status);

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant updated successfully.');
    }

    protected function syncSubscriptionPlan(Restaurant $restaurant, ?string $planSlug, string $status): void
    {
        if (blank($planSlug)) {
            return;
        }

        $planModel = SubscriptionPlan::where('slug', $planSlug)->first();
        if (!$planModel) {
            return;
        }

        $subscription = $restaurant->subscription()->first();

        if ($subscription) {
            $subscription->update([
                'subscription_plan_id' => $planModel->id,
                'status' => $status === 'active' ? 'active' : 'trial',
            ]);

            if ($status === 'active') {
                SubscriptionManager::upgradeToPaidSubscription($subscription);
            }

            return;
        }

        $newSubscription = SubscriptionManager::createTrialSubscription($restaurant, $planModel);
        if ($status === 'active') {
            SubscriptionManager::upgradeToPaidSubscription($newSubscription);
        }
    }

    public function destroy(Restaurant $restaurant)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant deleted successfully.');
    }
}
