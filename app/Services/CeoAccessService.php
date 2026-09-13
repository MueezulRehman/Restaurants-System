<?php

namespace App\Services;

use App\Models\CeoBranchAssignment;
use App\Models\CeoBusinessAssignment;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\Tenancy;

class CeoAccessService
{
    public function businessAssignment(User $user, int $restaurantId): ?CeoBusinessAssignment
    {
        if (! $user->isCeo()) {
            return null;
        }

        return CeoBusinessAssignment::query()
            ->where('user_id', $user->id)
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->first();
    }

    public function canAccessBusiness(User $user, int $restaurantId, string $permission = 'business.view'): bool
    {
        $assignment = $this->businessAssignment($user, $restaurantId);

        return $assignment !== null && $this->allows($assignment, $permission);
    }

    public function canAccessPortfolio(User $user, string $permission = 'portfolio.view'): bool
    {
        return CeoBusinessAssignment::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->contains(fn (CeoBusinessAssignment $assignment) => $this->allows($assignment, $permission));
    }

    public function canAccessBranch(User $user, Restaurant $restaurant, int $branchId, string $permission = 'branch.view'): bool
    {
        $assignment = $this->businessAssignment($user, $restaurant->id);
        if (! $assignment || ! $this->allows($assignment, $permission)) {
            return false;
        }

        if ($assignment->hasAllBranchAccess()) {
            return Tenancy::runFor($restaurant, fn () => $this->branchBelongsToTenant($branchId));
        }

        return CeoBranchAssignment::query()
            ->where('user_id', $user->id)
            ->where('restaurant_id', $restaurant->id)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->exists();
    }

    public function allowedBranchIds(User $user, CeoBusinessAssignment $assignment): ?array
    {
        if ($assignment->hasAllBranchAccess()) {
            return null;
        }

        return CeoBranchAssignment::query()
            ->where('user_id', $user->id)
            ->where('restaurant_id', $assignment->restaurant_id)
            ->where('is_active', true)
            ->pluck('branch_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function allows(CeoBusinessAssignment $assignment, string $permission): bool
    {
        return match ($permission) {
            'portfolio.view', 'business.view', 'branch.view', 'reports.view' => true,
            'financials.view' => $assignment->can_view_financials,
            'staff.summary.view' => $assignment->can_view_staff,
            'inventory.summary.view' => $assignment->can_view_inventory,
            'branches.manage' => $assignment->can_manage_branches,
            default => false,
        };
    }

    protected function branchBelongsToTenant(int $branchId): bool
    {
        return \App\Models\Branch::query()->whereKey($branchId)->exists();
    }
}
