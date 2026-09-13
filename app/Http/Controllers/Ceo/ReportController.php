<?php

namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use App\Models\CeoBusinessAssignment;
use App\Services\CeoAccessService;
use App\Services\TenantPortfolioAggregator;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, TenantPortfolioAggregator $portfolio, CeoAccessService $access)
    {
        abort_unless($access->canAccessPortfolio($request->user(), 'reports.view'), 403);

        $assignments = CeoBusinessAssignment::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with('restaurant')
            ->get();

        $restaurants = $assignments->pluck('restaurant')->filter();
        $branchScopes = $assignments->mapWithKeys(function (CeoBusinessAssignment $assignment) use ($request, $access) {
            return [$assignment->restaurant_id => $access
                ->allowedBranchIds($request->user(), $assignment)];
        })->all();

        $summary = $portfolio->summarize(
            $restaurants,
            $request->date('from')?->toDateString(),
            $request->date('to')?->toDateString(),
            $branchScopes
        );

        return view('ceo.reports.index', compact('summary'));
    }
}
