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

        $totalSales = (clone $realOrdersQuery)->where('payment_status', 'paid')->sum('grand_total');

        // Today Metrics Calculations (Asia/Kolkata Timezone)
        $todaySales = (clone $realOrdersQuery)
            ->where('payment_status', 'paid')
            ->where(function ($q) use ($todayStr) {
                $q->whereDate('created_at', $todayStr)
                  ->orWhereDate('sale_date', $todayStr);
            })
            ->sum('grand_total');

        $todayExpenses = Expense::whereDate('expense_date', $todayStr)->sum('amount');
        $todayOrdersCount = (clone $realOrdersQuery)->whereDate('created_at', $todayStr)->count();
        $todayBookingsCount = Product::where('is_out_of_stock', 1)->count();

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
            'totalSales',
            'todaySales',
            'todayExpenses',
            'todayOrdersCount',
            'todayBookingsCount',
            'lowStockSizes',
            'outOfStockSizes',
            'newOrders',
            'recentOrders',
            'dummyOrders',
            'unreadNotifications'
        ));
    }
}
