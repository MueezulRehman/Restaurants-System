<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, Report $report): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $restaurantId = $user->restaurant_id ?? $user->effectiveRestaurantId();

        $hasExplicitGrant = in_array('reports', $user->getModuleAccessList(), true);

        return (int) $restaurantId === (int) $report->restaurant_id
            && ($hasExplicitGrant || $user->hasModuleAccess('reports'));
    }

    public function delete(?User $user, Report $report): bool
    {
        return $this->view($user, $report);
    }

    public function create(?User $user): bool
    {
        return $user instanceof User && $user->hasModuleAccess('reports');
    }
}
