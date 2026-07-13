<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; max-width: 400px; margin: 20px auto; color: #111827; font-size: 13px; background: #fff; }
        .box { border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; }
        h1 { font-size: 18px; text-align: center; margin: 0 0 4px; }
        .center { text-align: center; }
        .muted { color: #6b7280; }
        .header-line { border-top: 1px dashed #d1d5db; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { text-align: left; padding: 4px 0; vertical-align: top; }
        th:last-child, td:last-child { text-align: right; }
        .total-row { font-weight: 700; font-size: 14px; }
        .no-print { text-align: center; margin-top: 18px; }
        .no-print button { padding: 8px 16px; border-radius: 6px; border: 1px solid #d97706; background: #facc15; cursor: pointer; font-weight: 700; }
        .pill { display: inline-block; margin-top: 6px; padding: 3px 8px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 11px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="box">
        <h1>{{ $restaurant->name ?? 'Receipt' }}</h1>
        <p class="center muted" style="margin: 2px 0 0;">{{ $restaurant?->address ?? 'Professional service' }}</p>
        @if($restaurant?->phone)
            <p class="center muted">Phone: {{ $restaurant->phone }}</p>
        @endif

        <div class="header-line"></div>

        <p style="margin: 0;">
            <strong>Receipt #:</strong> {{ $order->invoice_number ?? $order->order_number }}<br>
            <strong>Order #:</strong> {{ $order->order_number }}<br>
            <strong>Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}<br>
            <strong>Customer:</strong> {{ $order->customer_name }}<br>
            @if($order->table_number)
                <strong>Table:</strong> {{ $order->table_number }}<br>
            @endif
            <strong>Payment:</strong> {{ ucfirst($order->payment_method) }}
        </p>

        <div class="header-line"></div>

        <table>
            <thead>
                <tr><th>Item</th><th>Qty</th><th>Amount</th></tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->item_name }}
                            @if($item->size_label)<br><span class="muted">({{ $item->size_label }})</span>@endif
                            @foreach($item->toppings as $t)
                                <br><span class="muted">+ {{ $t->topping_name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->total_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="header-line"></div>

        <table>
            <tr><td>Subtotal</td><td>Rs. {{ number_format($order->subtotal) }}</td></tr>
            @if($order->delivery_fee > 0)
                <tr><td>Delivery</td><td>Rs. {{ number_format($order->delivery_fee) }}</td></tr>
            @endif
            <tr class="total-row"><td>Total</td><td>Rs. {{ number_format($order->total) }}</td></tr>
            @if($order->payment_method === 'cash')
                <tr><td>Cash received</td><td>Rs. {{ number_format($order->amount_received, 2) }}</td></tr>
                <tr><td>{{ $order->change_amount >= 0 ? 'Change' : 'Balance due' }}</td><td>Rs. {{ number_format(abs($order->change_amount), 2) }}</td></tr>
            @endif
        </table>

        <div class="header-line"></div>
        <p class="center muted" style="margin: 0;">Thank you for your business!</p>
        <p class="center"><span class="pill">{{ $restaurant->name ?? 'Business' }} • {{ $restaurant?->phone ?? 'Contact available' }}</span></p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Print Receipt</button>
        <p><a href="{{ route('manager.pos.index') }}">← Back to POS</a></p>
    </div>

    <script>
        @if(request()->boolean('print'))
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 400);
            });
        @endif
    </script>
</body>
</html>
