<?php

namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Restaurant;
use App\Services\CeoAccessService;
use App\Support\Tenancy;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request, CeoAccessService $access)
    {
        $assignments = $request->user()->ceoBusinessAssignments()
            ->where('is_active', true)
            ->with('restaurant')
            ->get();
        $branches = collect();

        foreach ($assignments as $assignment) {
            $allowedIds = $access->allowedBranchIds($request->user(), $assignment);
            $restaurant = $assignment->restaurant;
            $branches = $branches->merge(Tenancy::runFor($restaurant, function () use ($allowedIds, $restaurant) {
                return Branch::query()
                    ->where('is_active', true)
                    ->when($allowedIds !== null, fn ($query) => $query->whereIn('id', $allowedIds))
                    ->get()
                    ->map(fn (Branch $branch) => [
                        'branch' => $branch,
                        'restaurant' => $restaurant,
                    ]);
            }));
        }

        return view('ceo.branches.index', compact('branches'));
    }

    public function show(Request $request, int $business, int $branch, CeoAccessService $access)
    {
        $restaurant = Restaurant::findOrFail($business);
        abort_unless($access->canAccessBranch($request->user(), $restaurant, $branch), 403);

        $data = Tenancy::runFor($restaurant, function () use ($branch) {
            $model = Branch::query()->whereKey($branch)->firstOrFail();

            return [
                'branch' => $model,
                'orders' => \App\Models\Order::query()->where('branch_id', $model->id)->count(),
                'sales' => (float) \App\Models\Order::query()
                    ->where('branch_id', $model->id)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total'),
                'low_stock' => (int) \App\Models\BranchInventory::query()
                    ->where('branch_id', $model->id)
                    ->where('quantity', '<=', 0)
                    ->count(),
            ];
        });

        return view('ceo.branches.show', ['restaurant' => $restaurant] + $data);
    }
}
