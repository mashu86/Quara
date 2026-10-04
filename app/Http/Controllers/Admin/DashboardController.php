<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Capital;
use App\Models\Expense;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Services\BusinessStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request, BusinessStatistics $businessStatistics)
    {
        $todayStr = Carbon::now('Asia/Kolkata')->toDateString();
        $businessStartDate = config('business.start_date');
        $dateFilter = $request->validate([
            'as_of_date' => 'sometimes|required|date_format:Y-m-d|after_or_equal:'.$businessStartDate.'|before_or_equal:'.$todayStr,
        ]);
        $selectedDate = $dateFilter['as_of_date'] ?? $todayStr;
        $selectedDateLabel = Carbon::parse($selectedDate)->format('d M Y');
        $dailyLabel = $selectedDate === $todayStr ? 'Today' : 'Selected Day';
        $orderDateRange = [$businessStartDate.' 00:00:00', $selectedDate.' 23:59:59'];
        $filters = $request->validate([
            'period' => 'sometimes|in:today,all,week,month,range',
            'start_date' => 'exclude_unless:period,range|required|date_format:Y-m-d|after_or_equal:'.$businessStartDate.'|before_or_equal:'.$selectedDate,
            'end_date' => 'exclude_unless:period,range|required|date_format:Y-m-d|after_or_equal:start_date|before_or_equal:'.$selectedDate,
            'metrics' => 'sometimes|array',
            'metrics.*' => 'in:sales,expense,revenue',
            'metrics_submitted' => 'sometimes|in:1',
        ]);
        $selectedMetrics = $request->has('metrics_submitted') ? ($filters['metrics'] ?? []) : ['sales', 'expense', 'revenue'];

        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();
        $totalCategories = Category::count();

        $inactiveOrderIds = \App\Models\OrderOperation::where('status', 'inactive')->pluck('order_id')->toArray();

        // Real Orders Base Query (Excludes INACTIVE operations & test phone 9544832975)
        $realOrdersQuery = Order::query()
            ->whereBetween(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_date, created_at)'), $orderDateRange)
            ->whereNotIn('id', $inactiveOrderIds)
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            });

        // Dummy / Test Orders Base Query
        $dummyOrdersQuery = Order::query()
            ->whereBetween(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_date, created_at)'), $orderDateRange)
            ->where(function ($q) use ($inactiveOrderIds) {
                $q->whereIn('id', $inactiveOrderIds)
                  ->orWhere('customer_phone', 'LIKE', '%9544832975%');
            });

        $totalOrders = (clone $realOrdersQuery)->count();
        $pendingOrders = (clone $realOrdersQuery)->where('order_status', 'pending')->count();
        $processingOrders = (clone $realOrdersQuery)->whereIn('order_status', ['confirmed', 'processing', 'packed'])->count();
        $completedOrders = (clone $realOrdersQuery)->where('order_status', 'delivered')->count();
        $cancelledOrders = (clone $realOrdersQuery)->where('order_status', 'cancelled')->count();

        $refundOrderDateExpr = 'COALESCE(orders.sale_date, orders.created_at)';
        $refundsQuery = \App\Models\OrderRefund::query()
            ->join('orders', 'order_refunds.order_id', '=', 'orders.id')
            ->whereHas('orderOperation', fn ($query) => $query->where('status', 'active'))
            ->whereDate(\Illuminate\Support\Facades\DB::raw($refundOrderDateExpr), '>=', $businessStartDate)
            ->whereDate(\Illuminate\Support\Facades\DB::raw($refundOrderDateExpr), '<=', $selectedDate);
        $allTimeOperationRefunds = (float) (clone $refundsQuery)->sum('order_refunds.refund_amount');

        // Success Orders (Paid / Completed orders, excluding cancelled)
        $successOrdersQuery = (clone $realOrdersQuery)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled');

        $successOrdersCount = (int) (clone $successOrdersQuery)->count();
        $successGrossAmount = (float) (clone $successOrdersQuery)->sum('grand_total');
        $successOrdersAmount = max(0, $successGrossAmount - $allTimeOperationRefunds);

        // Total Sold Products Pcs (Total quantity of sold products across success orders)
        $successOrderIds = (clone $successOrdersQuery)->pluck('id');
        $totalSoldProductsPcs = 0;
        if (count($successOrderIds) > 0) {
            $totalSoldProductsPcs = (int) \App\Models\OrderItem::whereIn('order_id', $successOrderIds)
                ->whereIn('item_status', ['active', 'exchanged'])
                ->sum('quantity');
        }

        $saleExpr = "COALESCE(sale_date, created_at)";

        // Daily cards use the selected day (Asia/Kolkata timezone).
        $todayGrossSales = (float) (clone $realOrdersQuery)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->whereDate(\Illuminate\Support\Facades\DB::raw($saleExpr), $selectedDate)
            ->sum('grand_total');

        $todayRefunds = (float) (clone $refundsQuery)
            ->whereDate(\Illuminate\Support\Facades\DB::raw($refundOrderDateExpr), $selectedDate)
            ->sum('order_refunds.refund_amount');
        $todayRefundPayments = (clone $refundsQuery)
            ->whereDate(\Illuminate\Support\Facades\DB::raw($refundOrderDateExpr), $selectedDate)
            ->select('order_refunds.*')
            ->with(['order.payment', 'orderOperation'])
            ->orderBy('order_refunds.id')
            ->get();

        $todaySales = max(0, $todayGrossSales - $todayRefunds);
        
        $todayExpensesData = \App\Http\Controllers\Admin\ExpenseController::getBusinessExpensesSummary($selectedDate, $selectedDate);
        $todayExpenses = $todayExpensesData['total'];
        
        $todayPaidOrdersQuery = (clone $realOrdersQuery)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->whereDate(\Illuminate\Support\Facades\DB::raw($saleExpr), $selectedDate);

        $todayPaidOrdersList = (clone $todayPaidOrdersQuery)
            ->orderBy(\Illuminate\Support\Facades\DB::raw($saleExpr), 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $dailySalesLedger = collect();
        foreach ($todayPaidOrdersList as $paidOrder) {
            $dailySalesLedger->push([
                'type' => 'sale',
                'occurred_at' => $paidOrder->sale_date ?? $paidOrder->created_at,
                'order' => $paidOrder,
                'method' => $paidOrder->payment_method ?: 'unknown',
                'amount' => (float) $paidOrder->grand_total,
            ]);
        }
        foreach ($todayRefundPayments as $refundPayment) {
            $dailySalesLedger->push([
                'type' => 'refund',
                // Refunds are included in the selected sale-day figures by their order's sale date.
                'occurred_at' => $refundPayment->order?->sale_date ?? $refundPayment->order?->created_at,
                'order' => $refundPayment->order,
                'method' => $refundPayment->payment_method ?: 'manual',
                'reference' => $refundPayment->transaction_reference,
                'amount' => (float) $refundPayment->refund_amount,
            ]);
        }
        $dailySalesLedger = $dailySalesLedger->sortBy(fn ($entry) => $entry['occurred_at']?->timestamp ?? 0)->values();
        $dailyRunningTotal = 0.0;
        $dailySalesLedger = $dailySalesLedger->map(function ($entry) use (&$dailyRunningTotal) {
            $dailyRunningTotal += $entry['type'] === 'refund' ? -$entry['amount'] : $entry['amount'];
            $entry['running_total'] = $dailyRunningTotal;

            return $entry;
        });

        $todayOrdersCount = (int) (clone $todayPaidOrdersQuery)->count();
        $todayBookingsCount = Product::where('is_out_of_stock', 1)->count();

        // Today Sold Products Pcs
        $todaySuccessOrderIds = (clone $todayPaidOrdersQuery)->pluck('id');

        $todaySoldProductsPcs = 0;
        if (count($todaySuccessOrderIds) > 0) {
            $todaySoldProductsPcs = (int) \App\Models\OrderItem::whereIn('order_id', $todaySuccessOrderIds)
                ->whereIn('item_status', ['active', 'exchanged'])
                ->sum('quantity');
        }

        // Cumulative financial cards cover business start through the selected day.
        $allTimePaidOrdersQuery = (clone $realOrdersQuery)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled');

        $allTimeCapital = (float) Capital::whereDate('capital_date', '>=', $businessStartDate)
            ->whereDate('capital_date', '<=', $selectedDate)->sum('amount');
        $allTimeGrossRevenue = (float) (clone $allTimePaidOrdersQuery)->sum('grand_total');
        $allTimeNetSalesRevenue = max(0, $allTimeGrossRevenue - $allTimeOperationRefunds);
        $allTimeAdditionalIncome = (float) \App\Models\Income::where('status', 'active')
            ->whereDate('income_date', '>=', $businessStartDate)->whereDate('income_date', '<=', $selectedDate)->sum('total_income_amount');
        $allTimeTotalRevenue = $allTimeGrossRevenue + $allTimeAdditionalIncome;

        $allTimeExpensesData = \App\Http\Controllers\Admin\ExpenseController::getBusinessExpensesSummary($businessStartDate, $selectedDate);
        $allTimeTotalExpenses = $allTimeExpensesData['total'];

        $cashInBank = ($allTimeCapital + $allTimeTotalRevenue) - $allTimeTotalExpenses;
        $allTimeNetProfitLoss = $cashInBank - $allTimeCapital;
        $allTimeIsProfit = $allTimeNetProfitLoss >= 0;
        $businessStats = $businessStatistics->report($filters + ['as_of_date' => $selectedDate]);
        $totalSales = $businessStats['totalSales'];

        // Low stock products (size stock <= 3)
        $lowStockSizes = ProductSize::with('product')
            ->where('stock', '>', 0)
            ->where('stock', '<=', 3)
            ->get();

        // Out of stock products
        $outOfStockSizes = ProductSize::with('product')
            ->where('stock', 0)
            ->get();

        // Real Orders listing
        $newOrders = (clone $realOrdersQuery)->orderBy(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_date, created_at)'), 'desc')->orderBy('id', 'desc')->take(10)->get();
        $recentOrders = $newOrders;

        // Dummy / Test Orders listing for separate tab
        $dummyOrders = (clone $dummyOrdersQuery)->orderBy(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_date, created_at)'), 'desc')->orderBy('id', 'desc')->take(10)->get();

        // Calculate All-Time Highest Sales Day
        $dailySalesData = Order::query()
            ->selectRaw("DATE({$saleExpr}) as sale_day, SUM(grand_total) as gross_sales, COUNT(id) as orders_count")
            ->whereNotIn('id', $inactiveOrderIds)
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            })
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->groupBy(\Illuminate\Support\Facades\DB::raw("DATE({$saleExpr})"))
            ->get();

        $dailyRefundsData = \App\Models\OrderRefund::query()
            ->join('orders', 'order_refunds.order_id', '=', 'orders.id')
            ->whereHas('orderOperation', fn ($query) => $query->where('status', 'active'))
            ->selectRaw("DATE({$refundOrderDateExpr}) as refund_day, SUM(order_refunds.refund_amount) as total_refund")
            ->groupBy(\Illuminate\Support\Facades\DB::raw("DATE({$refundOrderDateExpr})"))
            ->pluck('total_refund', 'refund_day');

        $highestSalesDay = null;
        $maxNetSales = 0;

        foreach ($dailySalesData as $row) {
            $dStr = $row->sale_day;
            $ref = (float) ($dailyRefundsData[$dStr] ?? 0);
            $net = max(0, (float)$row->gross_sales - $ref);
            if ($net > $maxNetSales) {
                $maxNetSales = $net;
                $highestSalesDay = [
                    'date' => $dStr,
                    'date_formatted' => Carbon::parse($dStr)->format('d M Y'),
                    'amount' => $net,
                    'orders_count' => (int) $row->orders_count,
                ];
            }
        }

        $isTodayHighestSalesDay = false;
        if ($highestSalesDay && $maxNetSales > 0 && ($selectedDate === $highestSalesDay['date'] || ($selectedDate === $todayStr && $todaySales >= $maxNetSales))) {
            $isTodayHighestSalesDay = true;
        }

        $unreadNotifications = Notification::where('is_read', false)->orderBy('id', 'desc')->get();

        return view('admin.dashboard', compact(
            'todayStr',
            'businessStartDate',
            'selectedDate',
            'selectedDateLabel',
            'dailyLabel',
            'businessStats',
            'selectedMetrics',
            'totalProducts',
            'activeProducts',
            'totalCategories',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'completedOrders',
            'cancelledOrders',
            'successOrdersCount',
            'successOrdersAmount',
            'totalSoldProductsPcs',
            'todaySoldProductsPcs',
            'totalSales',
            'todaySales',
            'todayGrossSales',
            'todayRefunds',
            'todayRefundPayments',
            'todayExpenses',
            'todayOrdersCount',
            'todayPaidOrdersList',
            'dailySalesLedger',
            'todayBookingsCount',
            'allTimeCapital',
            'allTimeTotalRevenue',
            'allTimeTotalExpenses',
            'cashInBank',
            'allTimeNetProfitLoss',
            'allTimeIsProfit',
            'lowStockSizes',
            'outOfStockSizes',
            'newOrders',
            'recentOrders',
            'dummyOrders',
            'unreadNotifications',
            'highestSalesDay',
            'isTodayHighestSalesDay'
        ));
    }
}
