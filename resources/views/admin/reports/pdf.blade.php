<html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
            h1, h2 { color: #333; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            table th, table td { border: 1px solid #ccc; padding: 8px; }
        </style>
    </head>
    <body>
        <h1>Business Reports</h1>
        <h2>Sales Summary</h2>
        <table>
            <tr><th>Total Sales</th><td>{{ number_format($sales['total_sales'], 2) }}</td></tr>
            <tr><th>Order Count</th><td>{{ $sales['order_count'] }}</td></tr>
            <tr><th>Average Order Value</th><td>{{ number_format($sales['avg_order_value'], 2) }}</td></tr>
        </table>

        <h2>Income vs Expense</h2>
        <table>
            <tr><th>Income</th><td>{{ number_format($incomeExpense['total_income'], 2) }}</td></tr>
            <tr><th>Expense</th><td>{{ number_format($incomeExpense['total_expense'], 2) }}</td></tr>
            <tr><th>Net Profit</th><td>{{ number_format($incomeExpense['net_profit'], 2) }}</td></tr>
        </table>
    </body>
</html>
