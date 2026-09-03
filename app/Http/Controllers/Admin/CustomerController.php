<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use App\Services\StatementCardImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
            $q->where('restaurant_id', $restaurantId)->with(['items.toppings'])->latest();
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

        // Throttle: avoid spamming the same customer more than once per 24 hours
        // unless the manager explicitly confirms via ?force=1
        if ($customer->last_reminder_at
            && $customer->last_reminder_at->gt(now()->subDay())
            && ! request()->boolean('force')) {
            return back()->with(
                'error',
                'A reminder was already sent to this customer within the last 24 hours ('
                . $customer->last_reminder_at->diffForHumans()
                . '). Add ?force=1 or wait before sending again.'
            );
        }

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

        $shop = $restaurant?->name ?? 'our store';
        $shopPhone = $restaurant?->phone ? "\n📞 {$restaurant->phone}" : '';
        $balanceFmt = number_format($balance, 2);

        $orderLines = $recentOrders->isEmpty()
            ? '• No recent bills on file'
            : $recentOrders->map(function ($order) {
                $date = $order->created_at?->format('d M Y') ?? '';
                return "• {$order->order_number} ({$date}) — Rs. " . number_format((float) $order->total, 2);
            })->implode("\n");

        // WhatsApp-friendly structured reminder (line breaks render well in WA)
        $message = "🏪 *{$shop}*\n"
            . "━━━━━━━━━━━━━━\n"
            . "Hello *{$freshCustomer->name}*,\n\n"
            . "This is a polite account reminder.\n\n"
            . "💰 *Balance due: Rs. {$balanceFmt}*\n\n"
            . "🧾 *Recent bills*\n"
            . "{$orderLines}\n\n"
            . "Please settle the outstanding amount at your earliest convenience.\n"
            . "Reply to this message or visit us — we are happy to help.\n"
            . "{$shopPhone}\n"
            . "━━━━━━━━━━━━━━\n"
            . "Thank you for choosing *{$shop}*.";

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
            $wa = new WhatsAppService();
            // Unique designed image card per customer/record, then caption text
            $cardPath = (new StatementCardImage())->generate(
                $freshCustomer,
                $restaurant,
                $recentOrders->all()
            );
            if ($cardPath) {
                $wa->sendImage($freshCustomer->phone, $cardPath, $message);
                @unlink($cardPath);
            } else {
                $wa->sendText($freshCustomer->phone, $message);
            }
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

    /**
     * Digital account statement: recent bills, amounts, balance activity.
     * Opens a print-ready page (Print → Save as PDF in the browser).
     * If barryvdh/laravel-dompdf is installed, ?format=pdf downloads a real PDF.
     */
    public function statement(Customer $customer)
    {
        $restaurantId = $this->restaurantId();
        abort_unless(
            $customer->restaurant_id === $restaurantId
                || $customer->orders()->where('restaurant_id', $restaurantId)->exists(),
            404
        );

        $restaurant = Auth::user()->effectiveRestaurant();
        $orders = $customer->orders()
            ->where('restaurant_id', $restaurantId)
            ->with(['items.toppings'])
            ->latest()
            ->take(50)
            ->get();
        $transactions = $customer->balanceTransactions()
            ->where('restaurant_id', $restaurantId)
            ->latest()
            ->take(50)
            ->get();

        $data = compact('customer', 'restaurant', 'orders', 'transactions');

        if (request()->query('format') === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.customers.statement', $data);
            $filename = 'statement-' . \Illuminate\Support\Str::slug($customer->name) . '-' . now()->format('Ymd') . '.pdf';

            return $pdf->download($filename);
        }

        return response()->view('admin.customers.statement', $data);
    }

    /**
     * Email the statement link / summary to the customer (if email is on file).
     */
    public function emailStatement(Customer $customer)
    {
        $restaurantId = $this->restaurantId();
        abort_unless(
            $customer->restaurant_id === $restaurantId
                || $customer->orders()->where('restaurant_id', $restaurantId)->exists(),
            404
        );

        if (empty($customer->email)) {
            return back()->with('error', 'This customer has no email address on file.');
        }

        $restaurant = Auth::user()->effectiveRestaurant();
        $balance = number_format((float) $customer->balance, 2);
        $orders = $customer->orders()
            ->where('restaurant_id', $restaurantId)
            ->latest()
            ->take(10)
            ->get();

        $lines = $orders->map(function ($o) {
            return sprintf(
                '- %s | %s | Rs. %s (received Rs. %s)',
                $o->created_at?->format('Y-m-d'),
                $o->order_number,
                number_format((float) $o->total, 2),
                number_format((float) ($o->amount_received ?? 0), 2)
            );
        })->implode("\n");

        $subject = ($restaurant?->name ?? 'Store') . ' — your account statement';
        $body = "Hello {$customer->name},\n\n"
            . "Here is a summary of your account with " . ($restaurant?->name ?? 'us') . ".\n\n"
            . "Current balance due: Rs. {$balance}\n\n"
            . "Recent bills:\n" . ($lines ?: '(no recent orders)') . "\n\n"
            . "For a full printable statement, please contact the store or reply to this email.\n\n"
            . "Thank you.";

        try {
            Mail::raw($body, function ($message) use ($customer, $subject) {
                $message->to($customer->email, $customer->name)->subject($subject);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not send email: ' . $e->getMessage());
        }

        return back()->with('success', "Account statement emailed to {$customer->email}.");
    }



    public function emailReceipt(Customer $customer, \App\Models\Order $order)
    {
        $restaurantId = $this->restaurantId();
        abort_unless(
            ($customer->restaurant_id === $restaurantId
                || $customer->orders()->where('restaurant_id', $restaurantId)->exists())
            && $order->customer_id === $customer->id
            && $order->restaurant_id === $restaurantId,
            404
        );

        if (empty($customer->email)) {
            return back()->with('error', 'This customer has no email address on file.');
        }

        $order->load(['items.toppings', 'items.variant']);
        $restaurant = Auth::user()->effectiveRestaurant() ?: $order->restaurant;

        $lines = $order->items->map(function ($item) {
            return sprintf(
                '- %s x%d = Rs. %s',
                $item->item_name,
                $item->quantity,
                number_format((float) $item->total_price, 2)
            );
        })->implode("\n");

        $subject = ($restaurant?->name ?? 'Store') . ' — Receipt ' . ($order->invoice_number ?? $order->order_number);
        $body = "Hello {$customer->name},\n\n"
            . "Here is your bill from " . ($restaurant?->name ?? 'us') . ".\n\n"
            . "Order: {$order->order_number}\n"
            . "Date: " . ($order->created_at?->format('M d, Y h:i A') ?? '') . "\n"
            . "Payment: " . ucfirst($order->payment_method ?? 'cash') . "\n\n"
            . "Items:\n{$lines}\n\n"
            . "Total: Rs. " . number_format((float) $order->total, 2) . "\n"
            . "Cash received: Rs. " . number_format((float) ($order->amount_received ?? 0), 2) . "\n\n"
            . "Thank you for your business.";

        try {
            Mail::raw($body, function ($message) use ($customer, $subject) {
                $message->to($customer->email, $customer->name)->subject($subject);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not send receipt: ' . $e->getMessage());
        }

        return back()->with('success', "Receipt {$order->order_number} emailed to {$customer->email}.");
    }


}
