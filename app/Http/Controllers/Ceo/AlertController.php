<?php

namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use App\Models\CeoBusinessAssignment;
use App\Services\CeoAccessService;
use App\Services\TenantPortfolioAggregator;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request, TenantPortfolioAggregator $portfolio, CeoAccessService $access)
    {
        abort_unless($access->canAccessPortfolio($request->user(), 'inventory.summary.view'), 403);

        $assignments = CeoBusinessAssignment::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with('restaurant')
            ->get();

        $branchScopes = $assignments->mapWithKeys(function (CeoBusinessAssignment $assignment) use ($request, $access) {
            return [$assignment->restaurant_id => $access
                ->allowedBranchIds($request->user(), $assignment)];
        })->all();

        $summary = $portfolio->summarize(
            $assignments->pluck('restaurant')->filter(),
            null,
            null,
            $branchScopes
        );

        return view('ceo.alerts.index', compact('summary'));
    }
}
