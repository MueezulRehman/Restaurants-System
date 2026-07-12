<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; max-width: 380px; margin: 20px auto; color: #111; font-size: 13px; }
        h1 { font-size: 16px; text-align: center; margin: 0 0 2px; }
        .center { text-align: center; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 3px 0; }
        th:last-child, td:last-child { text-align: right; }
        hr { border: none; border-top: 1px dashed #999; margin: 8px 0; }
        .total-row { font-weight: bold; font-size: 15px; }
        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { padding: 8px 18px; border-radius: 6px; border: 1px solid #333; background: #FACC15; cursor: pointer; font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <h1>{{ $restaurant->name ?? 'Receipt' }}</h1>
    @if($restaurant?->address)
        <p class="center muted">{{ $restaurant->address }}</p>
    @endif
    @if($restaurant?->phone)
        <p class="center muted">{{ $restaurant->phone }}</p>
    @endif

    <hr>

    <p>
        Receipt #: {{ $order->invoice_number ?? $order->order_number }}<br>
        Order #: {{ $order->order_number }}<br>
        Date: {{ $order->created_at->format('M d, Y h:i A') }}<br>
        Customer: {{ $order->customer_name }}<br>
        @if($order->table_number)
            Table: {{ $order->table_number }}<br>
        @endif
        Payment: {{ ucfirst($order->payment_method) }}
    </p>

    <hr>

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

    <hr>

    <table>
        <tr><td>Subtotal</td><td>Rs. {{ number_format($order->subtotal) }}</td></tr>
        @if($order->delivery_fee > 0)
            <tr><td>Delivery</td><td>Rs. {{ number_format($order->delivery_fee) }}</td></tr>
        @endif
        <tr class="total-row"><td>Total</td><td>Rs. {{ number_format($order->total) }}</td></tr>
    </table>

    <hr>
    <p class="center muted">Thank you for your business!</p>

    <div class="no-print">
        <button onclick="window.print()">Print Receipt</button>
        <p><a href="{{ route('manager.pos.index') }}">← Back to POS</a></p>
    </div>
</body>
</html>
