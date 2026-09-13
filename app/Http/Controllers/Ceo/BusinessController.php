<?php

namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use App\Models\CeoBusinessAssignment;
use App\Models\Restaurant;
use App\Services\CeoAccessService;
use App\Services\TenantPortfolioAggregator;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $businesses = CeoBusinessAssignment::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with('restaurant')
            ->get();

        return view('ceo.businesses.index', compact('businesses'));
    }

    public function show(
        Request $request,
        int $business,
        CeoAccessService $access,
        TenantPortfolioAggregator $portfolio
    ) {
        abort_unless($access->canAccessBusiness($request->user(), $business, 'business.view'), 403);

        $assignment = CeoBusinessAssignment::query()
            ->where('user_id', $request->user()->id)
            ->where('restaurant_id', $business)
            ->where('is_active', true)
            ->with('restaurant')
            ->firstOrFail();

        $summary = $portfolio->summarize(
            collect([$assignment->restaurant]),
            $request->date('from')?->toDateString(),
            $request->date('to')?->toDateString(),
            [$assignment->restaurant_id => $access->allowedBranchIds($request->user(), $assignment)]
        );

        return view('ceo.businesses.show', compact('assignment', 'summary'));
    }
}
