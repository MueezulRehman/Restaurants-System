<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Multi-tenant data isolation.
 *
 * Any model using this trait is automatically scoped to the logged-in
 * admin/staff member's restaurant_id on every query (index, show, edit,
 * update, destroy, route-model-binding lookups, relation queries — all of
 * it), and new records automatically get restaurant_id filled in.
 *
 * This means a restaurant's admin can NEVER see, edit, or delete another
 * restaurant's data, even by guessing an ID in the URL — the row simply
 * won't be found (Laravel will 404 it via route model binding).
 *
 * Super admins (role = super_admin) are not scoped, since they manage the
 * SaaS platform itself rather than a single restaurant's day-to-day data.
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
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.restaurant_id', $user->restaurant_id);
        });

        static::creating(function ($model) {
            $user = Auth::user();

            if ($model->restaurant_id) {
                return;
            }

            if ($user instanceof User && $user->restaurant_id) {
                $model->restaurant_id = $user->restaurant_id;
            }
        });
    }
}
