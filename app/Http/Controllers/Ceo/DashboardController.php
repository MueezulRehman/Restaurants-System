<?php

namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use App\Models\CeoBranchAssignment;
use App\Models\CeoBusinessAssignment;
use App\Services\TenantPortfolioAggregator;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, TenantPortfolioAggregator $portfolio)
    {
        $businesses = CeoBusinessAssignment::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with('restaurant')
            ->get();

        $branches = CeoBranchAssignment::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->get();

        $restaurants = $businesses->pluck('restaurant')->filter();
        $branchScopes = $businesses->mapWithKeys(function (CeoBusinessAssignment $assignment) use ($branches): array {
            if ($assignment->hasAllBranchAccess()) {
                return [$assignment->restaurant_id => null];
            }

            return [
                $assignment->restaurant_id => $branches
                    ->where('restaurant_id', $assignment->restaurant_id)
                    ->pluck('branch_id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all(),
            ];
        })->all();

        $summary = $portfolio->summarize(
            $restaurants,
            $request->date('from')?->toDateString(),
            $request->date('to')?->toDateString(),
            $branchScopes
        );

        return view('ceo.dashboard', compact('businesses', 'branches', 'summary'));
    }
}
