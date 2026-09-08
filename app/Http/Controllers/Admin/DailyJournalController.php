<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOperation;
use App\Models\OrderRefund;
use App\Models\ProductSize;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DailyJournalController extends Controller
{
    public function index(Request $request)
    {
        // Default to selected date or Today in Asia/Kolkata timezone
        $selectedDateStr = $request->input('date', Carbon::now('Asia/Kolkata')->toDateString());
        
        try {
            $selectedCarbon = Carbon::parse($selectedDateStr, 'Asia/Kolkata');
        } catch (\Exception $e) {
            $selectedCarbon = Carbon::now('Asia/Kolkata');
            $selectedDateStr = $selectedCarbon->toDateString();
        }

        $prevDateStr = (clone $selectedCarbon)->subDay()->toDateString();
        $nextDateStr = (clone $selectedCarbon)->addDay()->toDateString();
        $isToday = ($selectedDateStr === Carbon::now('Asia/Kolkata')->toDateString());

        // Inactive test orders & operation filter
        $inactiveOrderIds = OrderOperation::where('status', 'inactive')->pluck('order_id')->toArray();

        // 1. Orders placed / created on the selected date
        $ordersQuery = Order::with(['items.product.images', 'payment'])
            ->whereNotIn('id', $inactiveOrderIds)
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            })
            ->whereDate(DB::raw('COALESCE(sale_date, created_at)'), $selectedDateStr)
            ->orderBy('id', 'desc');

        $orders = $ordersQuery->get();

        // Orders metrics for the day
        $totalOrdersCount = $orders->count();
        $paidOrdersCount = $orders->whereIn('payment_status', ['paid', 'completed'])->where('order_status', '!=', 'cancelled')->count();
        $pendingOrdersCount = $orders->where('payment_status', 'pending')->count();
        
        // Sales revenue on selected date
        $grossSales = (float) $orders->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->sum('grand_total');

        // Total sold pcs for the day
        $paidOrderIds = $orders->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->pluck('id');
            
        $totalSoldPcs = 0;
        if (count($paidOrderIds) > 0) {
            $totalSoldPcs = (int) OrderItem::whereIn('order_id', $paidOrderIds)
                ->whereIn('item_status', ['active', 'exchanged'])
                ->sum('quantity');
        }

        // 2. Expenses on selected date
        $expenses = Expense::whereDate('expense_date', $selectedDateStr)
            ->orderBy('id', 'desc')
            ->get();

        $totalExpenses = (float) $expenses->sum('amount');

        // 3. Refunds & Returns on selected date
        $refunds = OrderRefund::with('order')
            ->whereDate('refund_date', $selectedDateStr)
            ->orderBy('id', 'desc')
            ->get();

        $totalRefunds = (float) $refunds->sum('refund_amount');

        // 4. Order Operations on selected date
        $operations = OrderOperation::with('order')
            ->whereDate('created_at', $selectedDateStr)
            ->orderBy('id', 'desc')
            ->get();

        // 5. Booked / Reserved Products & Sizes (Global & Date Relevant)
        $bookedProductSizes = ProductSize::with(['product.images'])
            ->where(function ($q) {
                $q->where('reserved_stock', '>', 0)
                  ->orWhereHas('product', function ($pQuery) {
                      $pQuery->where('is_out_of_stock', 1);
                  });
            })
            ->get();

        // Financial calculations
        $netSales = max(0, $grossSales - $totalRefunds);
        $netProfitLoss = $netSales - $totalExpenses;
        $isProfit = $netProfitLoss >= 0;

        return view('admin.daily_journal.index', compact(
            'selectedDateStr',
            'prevDateStr',
            'nextDateStr',
            'isToday',
            'orders',
            'totalOrdersCount',
            'paidOrdersCount',
            'pendingOrdersCount',
            'grossSales',
            'totalSoldPcs',
            'expenses',
            'totalExpenses',
            'refunds',
            'totalRefunds',
            'operations',
            'bookedProductSizes',
            'netSales',
            'netProfitLoss',
            'isProfit'
        ));
    }

    public function releaseReservedStock(Request $request)
    {
        $request->validate([
            'product_size_id' => 'required|exists:product_sizes,id',
        ]);

        $stockService = app(StockService::class);
        $success = $stockService->releaseReservedStockForProductSize($request->input('product_size_id'), 'Manual Admin Unbook / Release Stock');

        if ($success) {
            return back()->with('success', 'Reserved stock successfully released / unbooked back into public inventory!');
        }

        return back()->with('error', 'Failed to release reserved stock.');
    }
}
