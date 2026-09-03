<?php

/**
 * PATCH for App\Http\Controllers\Admin\RestaurantController
 *
 * Add this private/protected method to the class, then call it in store() and update()
 * after resolving $selectedModuleKeys / $updateData['enabled_modules'].
 *
 * -------------------------------------------------------------------------
 * 1) Add method to RestaurantController:
 * -------------------------------------------------------------------------
 */

/*
    /**
     * Enforce subscription plan max_modules on the selected module key list.
     * Returns the (possibly trimmed) list of module keys.
     *
     * @param  array<int, string>  $moduleKeys
     * @return array<int, string>
     */
    protected function enforcePlanModuleLimit(array $moduleKeys, ?string $planSlug): array
    {
        $moduleKeys = array_values(array_unique(array_filter($moduleKeys)));

        if ($planSlug === null || $planSlug === '') {
            return $moduleKeys;
        }

        $plan = \App\Models\SubscriptionPlan::where('slug', $planSlug)->first();
        if (! $plan || $plan->max_modules === null || (int) $plan->max_modules <= 0) {
            return $moduleKeys; // unlimited
        }

        $max = (int) $plan->max_modules;
        if (count($moduleKeys) <= $max) {
            return $moduleKeys;
        }

        // Keep first N keys (order follows checkbox / type default order)
        return array_slice($moduleKeys, 0, $max);
    }
*/

/**
 * -------------------------------------------------------------------------
 * 2) In store() — after $selectedModuleKeys is built, before assigning to restaurantData:
 * -------------------------------------------------------------------------
 *
 *     $selectedModuleKeys = $this->enforcePlanModuleLimit(
 *         $selectedModuleKeys,
 *         $validated['plan'] ?? null
 *     );
 *
 * -------------------------------------------------------------------------
 * 3) In update() — after building $updateData['enabled_modules']:
 * -------------------------------------------------------------------------
 *
 *     if (isset($updateData['enabled_modules']) && is_array($updateData['enabled_modules'])) {
 *         $updateData['enabled_modules'] = $this->enforcePlanModuleLimit(
 *             $updateData['enabled_modules'],
 *             $updateData['plan'] ?? $restaurant->plan ?? null
 *         );
 *     }
 *
 * Optionally flash a warning if trimmed:
 *     if (count($original) > count($trimmed)) {
 *         session()->flash('warning', 'Module count was limited by the selected plan (max '.$max.').');
 *     }
 */
