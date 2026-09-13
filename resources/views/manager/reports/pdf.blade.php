<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->name }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #0f172a; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 18px 0 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .muted { color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #f8fafc; font-size: 11px; text-transform: uppercase; color: #64748b; }
        td.num, th.num { text-align: right; }
        .cards { width: 100%; margin: 12px 0; }
        .cards td { border: none; padding: 8px 12px 8px 0; }
        .stat { font-size: 16px; font-weight: 700; }
        @media print { body { margin: 12mm; } }
    </style>
</head>
<body>
    <h1>{{ $report->name }}</h1>
    <p class="muted">
        Type: {{ ucfirst($report->type) }} ·
        {{ $report->filters['date_from'] ?? '' }} — {{ $report->filters['date_to'] ?? '' }} ·
        Generated {{ now()->format('M d, Y H:i') }}
    </p>

    @php $data = $data ?? (is_array($report->data_snapshot) ? $report->data_snapshot : []); @endphp

    @if(($report->type ?? '') === 'sales')
        <table class="cards">
            <tr>
                <td>Total sales<br><span class="stat">Rs. {{ number_format((float)($data['total_sales'] ?? 0), 0) }}</span></td>
                <td>Orders<br><span class="stat">{{ number_format((float)($data['order_count'] ?? 0)) }}</span></td>
                <td>Qty sold<br><span class="stat">{{ number_format((float)($data['total_quantity'] ?? 0)) }}</span></td>
            </tr>
        </table>
        <h2>Items sold</h2>
        <table>
            <thead>
                <tr><th>Item</th><th class="num">Qty</th><th class="num">Revenue</th></tr>
            </thead>
            <tbody>
                @forelse(($data['items_sold'] ?? []) as $key => $row)
                    @php
                        $name = is_array($row) ? ($row['name'] ?? $key) : $key;
                        $qty = is_array($row) ? ($row['quantity'] ?? 0) : 0;
                        $rev = is_array($row) ? ($row['revenue'] ?? 0) : 0;
                    @endphp
                    <tr>
                        <td>{{ $name }}</td>
                        <td class="num">{{ number_format((float)$qty) }}</td>
                        <td class="num">Rs. {{ number_format((float)$rev, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No items in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <h2>Summary</h2>
        <table>
            @foreach($data as $k => $v)
                @if(!is_array($v))
                    <tr>
                        <th>{{ str_replace('_', ' ', $k) }}</th>
                        <td>{{ is_numeric($v) ? number_format((float)$v, 2) : $v }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
    @endif

    <script>window.addEventListener('load', function(){ /* optional auto print */ });</script>
</body>
</html>
