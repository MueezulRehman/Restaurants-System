<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use App\Services\CeoAccessService;
use Closure;
use Illuminate\Http\Request;

class EnsureCeoBranchAccess
{
    public function handle(Request $request, Closure $next, string $permission = 'branch.view')
    {
        $businessId = $this->routeId($request, ['business', 'restaurant']);
        $branchId = $this->routeId($request, ['branch']);

        $restaurant = $businessId ? Restaurant::find($businessId) : null;

        abort_unless(
            $restaurant
                && $branchId !== null
                && app(CeoAccessService::class)->canAccessBranch($request->user(), $restaurant, $branchId, $permission),
            403,
            'You do not have access to this branch.'
        );

        return $next($request);
    }

    private function routeId(Request $request, array $keys): ?int
    {
        foreach ($keys as $key) {
            $value = $request->route($key);
            $id = is_object($value) ? $value->getKey() : $value;
            if (is_numeric($id)) {
                return (int) $id;
            }
        }

        return null;
    }
}
