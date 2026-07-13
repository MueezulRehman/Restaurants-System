<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

        abort_unless($customer->restaurant_id === $restaurantId || $customer->orders()->where('restaurant_id', $restaurantId)->exists(), 404);

        $customer->load(['orders' => function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId)->latest();
        }, 'balanceTransactions' => function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId)->latest()->take(20);
        }]);

        return view('admin.customers.show', compact('customer'));
    }

    public function store(Request $request)
    {
        $restaurantId = $this->restaurantId();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'phone')->where(fn ($query) => $query->where('restaurant_id', $restaurantId)),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->where(fn ($query) => $query->where('restaurant_id', $restaurantId)),
            ],
        ]);

        $cart = $request->input('cart');
        if (is_string($cart)) {
            $decodedCart = json_decode($cart, true);
            $cart = is_array($decodedCart) ? $decodedCart : [];
        }

        if (is_array($cart)) {
            session(['pos_last_cart' => $cart]);
        }

        $customer = Customer::create([
            'restaurant_id' => $restaurantId,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => bcrypt(Str::random(16)),
            'balance' => 0,
        ]);

        $redirectRoute = $request->boolean('redirect_to_pos') ? 'manager.pos.index' : 'manager.customers.index';

        return redirect()->route($redirectRoute)->with('success', "Customer {$customer->name} registered successfully.");
    }

    public function remind(Customer $customer)
    {
        $restaurantId = $this->restaurantId();

        abort_unless($customer->restaurant_id === $restaurantId || $customer->orders()->where('restaurant_id', $restaurantId)->exists(), 404);

        $customer->update(['last_reminder_at' => now()]);

        $freshCustomer = $customer->fresh();
        $restaurant = $freshCustomer->restaurant;
        $balance = (float) $freshCustomer->balance;
        $channels = [];

        if (! empty($freshCustomer->email)) {
            $channels[] = 'email';
        }

        if (! empty($freshCustomer->phone)) {
            $channels[] = 'whatsapp';
        }

        if (empty($channels)) {
            $channels[] = 'email';
        }

        $recentOrders = $freshCustomer->orders()
            ->where('restaurant_id', $restaurantId)
            ->latest()
            ->take(5)
            ->get();

        $salesDetails = $recentOrders->isEmpty()
            ? 'No recent sales found.'
            : 'Sales details: ' . $recentOrders->map(function ($order) {
                return $order->order_number . ' - Rs. ' . number_format((float) $order->total, 2);
            })->implode('; ');

        $message = "Hello {$freshCustomer->name}, this is a friendly reminder that your current account balance is Rs. " . number_format($balance, 2) . ". This message includes your sales details: {$salesDetails}. Please contact us if you would like the full balance details or want to settle the amount.";

        NotificationService::send(
            $restaurantId,
            'custom',
            $restaurant ? "Balance reminder from {$restaurant->name}" : 'Balance reminder',
            $message,
            array_values(array_unique($channels)),
            null,
            $freshCustomer
        );

        if (! empty($freshCustomer->phone)) {
            (new WhatsAppService())->sendText($freshCustomer->phone, $message);
        }

        return back()->with('success', "Balance reminder logged for {$freshCustomer->name}. Current balance: Rs. " . number_format($balance, 2) . '.');
    }

    public function recordPayment(Request $request, Customer $customer)
    {
        $restaurantId = $this->restaurantId();

        abort_unless($customer->restaurant_id === $restaurantId || $customer->orders()->where('restaurant_id', $restaurantId)->exists(), 404);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $customer->recordBalanceChange((float) $validated['amount'], 'Payment recorded by manager', [
            'restaurant_id' => $restaurantId,
            'created_by' => Auth::id(),
            'source' => 'admin',
            'type' => 'payment',
        ]);

        return back()->with('success', 'Payment recorded and customer balance updated.');
    }
}
