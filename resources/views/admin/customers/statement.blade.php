<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Statement — {{ $customer->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #0f172a; margin: 22px; line-height: 1.4; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 18px 0 8px; color: #0f172a; border-bottom: 2px solid #0f172a; padding-bottom: 4px; }
        h3 { font-size: 12px; margin: 0 0 6px; }
        .muted { color: #64748b; }
        .header { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 14px; }
        .box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 12px; background: #f8fafc; }
        .bill {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .bill-head { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 5px 3px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        th { font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
        td.num, th.num { text-align: right; }
        .due { color: #b91c1c; font-weight: 700; font-size: 16px; }
        .ok { color: #15803d; font-weight: 700; font-size: 16px; }
        .sub { color: #64748b; font-size: 11px; }
        .no-print { margin-top: 18px; text-align: center; }
        .no-print a, .no-print button {
            display: inline-block; margin: 0 6px; padding: 8px 14px; border-radius: 6px;
            border: 1px solid #cbd5e1; background: #f8fafc; color: #0f172a; text-decoration: none; font-weight: 600; cursor: pointer;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 10mm; }
            .bill { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>{{ $restaurant->name ?? config('app.name') }}</h1>
            <p class="muted" style="margin:0;">
                @if($restaurant?->address){{ $restaurant->address }}<br>@endif
                @if($restaurant?->phone)Phone: {{ $restaurant->phone }}@endif
            </p>
        </div>
        <div style="text-align:right;">
            <strong>Account Statement</strong><br>
            <span class="muted">Generated {{ now()->format('M d, Y h:i A') }}</span>
        </div>
    </div>

    <div class="box">
        <strong>Customer</strong><br>
        {{ $customer->name }}<br>
        Phone: {{ $customer->phone ?? '—' }} · Email: {{ $customer->email ?: '—' }}
    </div>

    <div class="box">
        <span class="muted">Current balance due</span><br>
        <span class="{{ $customer->balance > 0 ? 'due' : 'ok' }}">
            Rs. {{ number_format((float) $customer->balance, 2) }}
        </span>
    </div>

    <h2>Bills with item details</h2>

    @forelse($orders as $order)
        <div class="bill">
            <div class="bill-head">
                <div>
                    <h3>{{ $order->order_number }}</h3>
                    <div class="sub">
                        {{ $order->created_at?->format('M d, Y H:i') }}
                        · {{ $order->status_label ?? ucfirst($order->status) }}
                        · {{ ucfirst($order->payment_method ?? '—') }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <strong>Rs. {{ number_format((float) $order->total, 2) }}</strong><br>
                    <span class="sub">Received Rs. {{ number_format((float) ($order->amount_received ?? 0), 2) }}</span><br>
                    <a class="no-print sub" href="{{ route('manager.pos.receipt', ['order' => $order, 'print' => 1]) }}" target="_blank">Open full receipt</a>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="num">Qty</th>
                        <th class="num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>
                                {{ $item->item_name }}
                                @if($item->size_label)<div class="sub">{{ $item->size_label }}</div>@endif
                                @foreach($item->toppings as $t)
                                    <div class="sub">+ {{ $t->topping_name }}</div>
                                @endforeach
                            </td>
                            <td class="num">{{ $item->quantity }}</td>
                            <td class="num">Rs. {{ number_format((float) $item->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">No line items stored for this bill.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @empty
        <p class="muted">No orders found.</p>
    @endforelse

    <h2>Balance activity</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td>{{ $tx->created_at?->format('M d, Y H:i') }}</td>
                    <td>{{ ucfirst($tx->type) }}</td>
                    <td>{{ $tx->description }}</td>
                    <td class="num">Rs. {{ number_format((float) $tx->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No balance activity.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="muted" style="margin-top:18px;">
        Digital statement from {{ $restaurant->name ?? config('app.name') }}. Contact us with any questions.
    </p>

    <div class="no-print">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
        <a href="{{ route('manager.customers.show', $customer) }}">← Back to customer</a>
    </div>
</body>
</html>
