<?php

namespace App\Http\Middleware;

use App\Services\CeoAccessService;
use Closure;
use Illuminate\Http\Request;

class EnsureCeoBusinessAccess
{
    public function handle(Request $request, Closure $next, string $permission = 'business.view')
    {
        $businessId = $this->routeId($request, ['business', 'restaurant']);

        abort_unless(
            $businessId !== null
                && app(CeoAccessService::class)->canAccessBusiness($request->user(), $businessId, $permission),
            403,
            'You do not have access to this business.'
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
