<?php

/**
 * Codeibex Tenancy – Concrete Usage Examples
 * Copy-paste ready patterns for the team.
 *
 * @author Mueez Ul Rehman
 */

namespace App\Examples;

use App\Jobs\TenantAwareJob;
use App\Models\Order;
use App\Models\Restaurant;
use App\Support\Tenancy;
use Illuminate\Http\Request;

// =====================================================================
// 1. Super Admin enters / exits a business
// =====================================================================

class SuperAdminExample
{
    public function enter(Restaurant $restaurant)
    {
        Tenancy::enter($restaurant);

        return redirect()->route('manager.dashboard');
    }

    public function exitBusiness()
    {
        Tenancy::exit();

        return redirect()->route('admin.restaurants.index');
    }
}

// =====================================================================
// 2. Running code safely inside a specific tenant
// =====================================================================

class ReportExample
{
    public function generateFor(Restaurant $restaurant)
    {
        return Tenancy::runFor($restaurant, function () use ($restaurant) {
            $orders = Order::whereDate('created_at', today())->get();
            $total  = $orders->sum('grand_total');

            return [
                'restaurant' => $restaurant->name,
                'orders'     => $orders->count(),
                'total'      => $total,
            ];
        });
    }
}

// =====================================================================
// 3. Queue Job (Mandatory pattern)
// =====================================================================

class SendDailySalesReport extends TenantAwareJob
{
    public function __construct(
        public int $restaurantId,
        public string $email
    ) {}

    public function handleTenant(): void
    {
        // Default DB connection is already the correct tenant database
        $sales = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('grand_total');

        // Mail::to($this->email)->send(new DailySalesMail($sales));
    }
}

// Dispatch from anywhere:
// SendDailySalesReport::dispatch($restaurant->id, 'owner@example.com');


// =====================================================================
// 4. Inside a normal Manager controller (middleware already set context)
// =====================================================================

class PosControllerExample
{
    public function store(Request $request)
    {
        // Middleware has already switched to the correct tenant DB.
        // Just write normal Eloquent code.

        $order = Order::create([
            'order_number' => 'CX-' . now()->format('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
            // ...
        ]);

        return response()->json(['order' => $order]);
    }
}

// =====================================================================
// 5. When you only have the restaurant ID (e.g. from a job payload)
// =====================================================================

class FromIdExample
{
    public function process(int $restaurantId)
    {
        Tenancy::forRestaurantId($restaurantId, function () {
            $pending = Order::where('status', 'pending')->count();
            // ...
        });
    }
}
