<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromArray, WithHeadings
{
    protected $sales;
    protected $incomeExpense;
    protected $itemPerformance;

    public function __construct(array $sales, array $incomeExpense, array $itemPerformance)
    {
        $this->sales = $sales;
        $this->incomeExpense = $incomeExpense;
        $this->itemPerformance = $itemPerformance;
    }

    public function array(): array
    {
        return [
            ['Report Type', 'Metric', 'Value'],
            ['Sales', 'Total Sales', $this->sales['total_sales'] ?? 0],
            ['Sales', 'Order Count', $this->sales['order_count'] ?? 0],
            ['Income vs Expense', 'Income', $this->incomeExpense['total_income'] ?? 0],
            ['Income vs Expense', 'Expense', $this->incomeExpense['total_expense'] ?? 0],
            ['Income vs Expense', 'Net Profit', $this->incomeExpense['net_profit'] ?? 0],
        ];
    }

    public function headings(): array
    {
        return ['Report Type', 'Metric', 'Value'];
    }
}
