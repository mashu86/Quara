<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $todayStr = Carbon::now('Asia/Kolkata')->toDateString();

        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();
        $totalCategories = Category::count();

        $inactiveOrderIds = \App\Models\OrderOperation::where('status', 'inactive')->pluck('order_id')->toArray();

        // Real Orders Base Query (Excludes INACTIVE operations & test phone 9544832975)
        $realOrdersQuery = Order::query()
            ->whereNotIn('id', $inactiveOrderIds)
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            });

        // Dummy / Test Orders Base Query
        $dummyOrdersQuery = Order::query()
            ->where(function ($q) use ($inactiveOrderIds) {
                $q->whereIn('id', $inactiveOrderIds)
                  ->orWhere('customer_phone', 'LIKE', '%9544832975%');
            });

        $totalOrders = (clone $realOrdersQuery)->count();
        $pendingOrders = (clone $realOrdersQuery)->where('order_status', 'pending')->count();
        $processingOrders = (clone $realOrdersQuery)->whereIn('order_status', ['confirmed', 'processing', 'packed'])->count();
        $completedOrders = (clone $realOrdersQuery)->where('order_status', 'delivered')->count();
        $cancelledOrders = (clone $realOrdersQuery)->where('order_status', 'cancelled')->count();

        $allTimeActiveOps = \App\Models\OrderOperation::where('status', 'active');
        $allTimeOperationRefunds = (float) \App\Models\OrderRefund::sum('refund_amount');
        $allTimeOperationExpenses = (float) (clone $allTimeActiveOps)->sum('additional_expense_total');

        $grossSales = (float) (clone $realOrdersQuery)->where('payment_status', 'paid')->sum('grand_total');
        $totalSales = max(0, $grossSales - $allTimeOperationRefunds);

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

        // Today Metrics Calculations (Asia/Kolkata Timezone)
        $todayGrossSales = (float) (clone $realOrdersQuery)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->whereDate(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_date, created_at)'), $todayStr)
            ->sum('grand_total');

        $todayRefunds = (float) \App\Models\OrderRefund::whereDate('refund_date', $todayStr)->sum('refund_amount');

        $todaySales = max(0, $todayGrossSales - $todayRefunds);
        
        $todayExpensesData = \App\Http\Controllers\Admin\ExpenseController::getBusinessExpensesSummary($todayStr, $todayStr);
        $todayExpenses = $todayExpensesData['total'];
        
        $todayPaidOrdersQuery = (clone $realOrdersQuery)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->whereDate(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_date, created_at)'), $todayStr);

        $todayOrdersCount = (int) (clone $todayPaidOrdersQuery)->count();
        $todayBookingsCount = Product::where('is_out_of_stock', 1)->count();

        // Today Sold Products Pcs
        $todaySuccessOrderIds = (clone $successOrdersQuery)
            ->whereDate(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_date, created_at)'), $todayStr)
            ->pluck('id');

        $todaySoldProductsPcs = 0;
        if (count($todaySuccessOrderIds) > 0) {
            $todaySoldProductsPcs = (int) \App\Models\OrderItem::whereIn('order_id', $todaySuccessOrderIds)
                ->whereIn('item_status', ['active', 'exchanged'])
                ->sum('quantity');
        }

        // ALL-TIME Financial Overview (Total Net Sales Revenue, Total Expense, Net Profit / Loss)
        $allTimePaidOrdersQuery = (clone $realOrdersQuery)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled');

        $allTimeGrossRevenue = (float) (clone $allTimePaidOrdersQuery)->sum('grand_total');
        $allTimeNetSalesRevenue = max(0, $allTimeGrossRevenue - $allTimeOperationRefunds);
        $allTimeAdditionalIncome = (float) \App\Models\Income::where('status', 'active')->sum('total_income_amount');
        $allTimeTotalRevenue = $allTimeGrossRevenue + $allTimeAdditionalIncome;

        $allTimeExpensesData = \App\Http\Controllers\Admin\ExpenseController::getBusinessExpensesSummary();
        $allTimeTotalExpenses = $allTimeExpensesData['total'];

        $allTimeNetProfitLoss = $allTimeTotalRevenue - $allTimeTotalExpenses;
        $allTimeIsProfit = $allTimeNetProfitLoss >= 0;

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
        $newOrders = (clone $realOrdersQuery)->orderBy('id', 'desc')->take(10)->get();
        $recentOrders = $newOrders;

        // Dummy / Test Orders listing for separate tab
        $dummyOrders = (clone $dummyOrdersQuery)->orderBy('id', 'desc')->take(10)->get();

        $unreadNotifications = Notification::where('is_read', false)->orderBy('id', 'desc')->get();

        return view('admin.dashboard', compact(
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
            'todayExpenses',
            'todayOrdersCount',
            'todayBookingsCount',
            'allTimeTotalRevenue',
            'allTimeTotalExpenses',
            'allTimeNetProfitLoss',
            'allTimeIsProfit',
            'lowStockSizes',
            'outOfStockSizes',
            'newOrders',
            'recentOrders',
            'dummyOrders',
            'unreadNotifications'
        ));
    }
}
