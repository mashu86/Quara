<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();
        $totalCategories = Category::count();

        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $processingOrders = Order::whereIn('order_status', ['confirmed', 'processing', 'packed'])->count();
        $completedOrders = Order::where('order_status', 'delivered')->count();
        $cancelledOrders = Order::where('order_status', 'cancelled')->count();

        $totalSales = Order::where('payment_status', 'paid')->sum('grand_total');

        // Low stock products (size stock <= 3)
        $lowStockSizes = ProductSize::with('product')
            ->where('stock', '>', 0)
            ->where('stock', '<=', 3)
            ->get();

        // Out of stock products
        $outOfStockSizes = ProductSize::with('product')
            ->where('stock', 0)
            ->get();

        // Prominent New Orders listing (newest first)
        $newOrders = Order::orderBy('id', 'desc')->take(10)->get();
        $recentOrders = $newOrders;

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
            'lowStockSizes',
            'outOfStockSizes',
            'newOrders',
            'recentOrders',
            'unreadNotifications'
        ));
    }
}
