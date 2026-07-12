<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Multi-tenant data isolation.
 *
 * Any model using this trait is automatically scoped to the current
 * restaurant on every query (index, show, edit, update, destroy, route-
 * model-binding lookups, relation queries — all of it), and new records
 * automatically get restaurant_id filled in.
 *
 * "Current restaurant" is:
 *   - the logged-in staff member's own restaurant_id, for restaurant
 *     admins/managers, or
 *   - the restaurant a super admin has "entered" via Tenancy::enter(), if
 *     any, or
 *   - unscoped (sees everything), for a super admin who hasn't entered any
 *     restaurant — used only on genuine platform-wide screens.
 *
 * This means a restaurant's admin can NEVER see, edit, or delete another
 * restaurant's data, even by guessing an ID in the URL — the row simply
 * won't be found (Laravel will 404 it via route model binding). The same
 * is true for a super admin who has entered a *different* restaurant than
 * the one being requested.
 */
trait BelongsToRestaurant
{
    public static function bootBelongsToRestaurant(): void
    {
        static::addGlobalScope('restaurant', function (Builder $builder) {
            $user = Auth::user();

            if (! $user instanceof User) {
                return;
            }

            if ($user->isSuperAdmin()) {
                if (Tenancy::isImpersonating()) {
                    $builder->where($builder->getModel()->getTable() . '.restaurant_id', Tenancy::impersonatedRestaurantId());
                }

                return;
            }

            $builder->where($builder->getModel()->getTable() . '.restaurant_id', $user->restaurant_id);
        });

        static::creating(function ($model) {
            if ($model->restaurant_id) {
                return;
            }

            $model->restaurant_id = Auth::user()?->effectiveRestaurantId();
        });
    }
}
