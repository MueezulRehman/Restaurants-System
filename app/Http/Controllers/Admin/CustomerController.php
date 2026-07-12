<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    protected function restaurantId(): ?int
    {
        $user = Auth::user();

        if (! $user instanceof \App\Models\User) {
            abort(403);
        }

        $restaurantId = $user->effectiveRestaurantId();

        abort_unless($restaurantId, 403);

        return $restaurantId;
    }

    public function index(Request $request)
    {
        $restaurantId = $this->restaurantId();

        $query = Customer::query()
            ->whereHas('orders', function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            })
            ->withCount(['orders' => function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            }])
            ->latest();

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $restaurantId = $this->restaurantId();

        abort_unless($customer->orders()->where('restaurant_id', $restaurantId)->exists(), 404);

        $customer->load(['orders' => function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId)->latest();
        }]);

        return view('admin.customers.show', compact('customer'));
    }
}
