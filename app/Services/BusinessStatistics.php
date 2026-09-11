<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderOperation;
use App\Models\OrderRefund;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BusinessStatistics
{
    public function salesQuery(): Builder
    {
        return Order::query()
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->whereNotIn('id', OrderOperation::where('status', 'inactive')->select('order_id'))
            ->where(function (Builder $query) {
                $query->whereNull('customer_phone')
                    ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            });
    }

    public function report(array $filters): array
    {
        $today = Carbon::today('Asia/Kolkata');
        $saleDate = 'COALESCE(sale_date, created_at)';
        $salesQuery = $this->salesQuery();
        $businessStart = Carbon::parse(config('business.start_date'), 'Asia/Kolkata')->startOfDay();

        $period = $filters['period'] ?? 'month';
        $start = match ($period) {
            'today' => $today->copy(),
            'all' => $businessStart->copy(),
            'week' => $today->copy()->startOfWeek(Carbon::MONDAY),
            'range' => Carbon::parse($filters['start_date'], 'Asia/Kolkata'),
            default => $today->copy()->startOfMonth(),
        };
        $end = $period === 'range' ? Carbon::parse($filters['end_date'], 'Asia/Kolkata') : $today->copy();
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $orders = (clone $salesQuery)->whereBetween(DB::raw($saleDate), [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
        $sales = $this->dailyTotals(clone $orders, $saleDate, 'grand_total');
        $fees = $this->dailyTotals((clone $orders)->where('payment_method', 'online'), $saleDate, 'razorpay_total_charge');
        $costs = Schema::hasColumn('products', 'cost_price') ? DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.id', (clone $orders)->select('orders.id'))
            ->where(function ($query) {
                $query->where('order_items.item_status', 'active')
                    ->orWhere('order_items.inventory_condition', 'do_not_restock');
            })
            ->selectRaw('DATE(COALESCE(orders.sale_date, orders.created_at)) as day, SUM(COALESCE(products.cost_price, 0) * order_items.quantity) as total')
            ->groupByRaw('DATE(COALESCE(orders.sale_date, orders.created_at))')
            ->pluck('total', 'day') : collect();
        $expenses = $this->dailyTotals(Expense::whereDate('expense_date', '>=', $startDate)->whereDate('expense_date', '<=', $endDate), 'expense_date', 'amount');
        $operations = $this->dailyTotals(OrderOperation::where('status', 'active')->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]), 'created_at', 'additional_expense_total');
        $refunds = $this->dailyTotals(OrderRefund::whereHas('orderOperation', fn (Builder $query) => $query->where('status', 'active'))->whereDate('refund_date', '>=', $startDate)->whereDate('refund_date', '<=', $endDate), 'refund_date', 'refund_amount');

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $day = $date->toDateString();
            $dailySales = (float) ($sales[$day] ?? 0);
            $dailyExpenses = round(($costs[$day] ?? 0) + ($fees[$day] ?? 0) + ($expenses[$day] ?? 0) + ($operations[$day] ?? 0) + ($refunds[$day] ?? 0), 2);
            $days[] = [
                'date' => $day,
                'sales' => round($dailySales, 2),
                'expense' => $dailyExpenses,
                'revenue' => round($dailySales - $dailyExpenses, 2),
            ];
        }

        $businessDays = max(1, (int) $businessStart->diffInDays($today) + 1);
        $totalSales = (float) (clone $salesQuery)
            ->whereBetween(DB::raw($saleDate), [$businessStart->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->sum('grand_total');
        $rankedDays = collect($days)->where('date', '>=', $businessStart->toDateString());
        $highest = $rankedDays->max('sales');
        $lowest = $rankedDays->min('sales');

        return [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'today' => $today->toDateString(),
            'businessStart' => $businessStart->toDateString(),
            'businessDays' => $businessDays,
            'totalSales' => $totalSales,
            'averageSales' => $totalSales / $businessDays,
            'days' => $days,
            'highest' => $rankedDays->firstWhere('sales', $highest),
            'lowest' => $rankedDays->firstWhere('sales', $lowest),
            'highestCount' => $rankedDays->where('sales', $highest)->count(),
            'lowestCount' => $rankedDays->where('sales', $lowest)->count(),
        ];
    }

    private function dailyTotals(Builder $query, string $dateColumn, string $amountColumn)
    {
        return $query->selectRaw("DATE($dateColumn) as day, SUM($amountColumn) as total")
            ->groupByRaw("DATE($dateColumn)")->pluck('total', 'day');
    }
}
