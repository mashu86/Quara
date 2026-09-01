<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        $today = Carbon::now('Asia/Kolkata')->startOfDay();

        $totalExpenses = Expense::sum('amount');
        $thisMonthExpenses = Expense::whereBetween('expense_date', [
            $today->copy()->startOfMonth()->toDateString(),
            $today->copy()->endOfMonth()->toDateString(),
        ])->sum('amount');
        $thisWeekExpenses = Expense::whereBetween('expense_date', [
            $today->copy()->startOfWeek()->toDateString(),
            $today->copy()->endOfWeek()->toDateString(),
        ])->sum('amount');
        $todayExpenses = Expense::whereDate('expense_date', $today->toDateString())->sum('amount');

        $filteredExpenseTotal = (clone $query)->sum('amount');
        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $desktopHtml = view('admin.expenses.partials.desktop_rows', compact('expenses'))->render();

            return response()->json([
                'desktop_html' => $desktopHtml,
                'next_page_url' => $expenses->nextPageUrl(),
                'has_more' => $expenses->hasMorePages(),
                'total' => $expenses->total(),
            ]);
        }

        return view('admin.expenses.index', compact(
            'expenses',
            'totalExpenses',
            'thisMonthExpenses',
            'thisWeekExpenses',
            'todayExpenses',
            'filteredExpenseTotal'
        ));
    }

    public function create()
    {
        return view('admin.expenses.create');
    }

    public function store(Request $request)
    {
        $title = $request->input('title') ?? $request->input('expense_name');
        $request->merge(['title' => $title]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'receipt_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('receipt_images')) {
            foreach ($request->file('receipt_images') as $file) {
                if ($file && $file->isValid()) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/expenses'), $filename);
                    $imagePaths[] = 'uploads/expenses/' . $filename;
                }
            }
        } elseif ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/expenses'), $filename);
            $imagePaths[] = 'uploads/expenses/' . $filename;
        }

        $receiptValue = null;
        if (count($imagePaths) === 1) {
            $receiptValue = $imagePaths[0];
        } elseif (count($imagePaths) > 1) {
            $receiptValue = json_encode($imagePaths);
        }

        Expense::create([
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'] ?? 'General',
            'notes' => $validated['notes'] ?? null,
            'receipt_image' => $receiptValue,
        ]);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function show(Expense $expense)
    {
        if (request()->wantsJson()) {
            $images = array_map(function($img) {
                return asset($img);
            }, $expense->receipt_images);

            return response()->json([
                'success' => true,
                'expense' => [
                    'id' => $expense->id,
                    'title' => $expense->title,
                    'amount' => number_format($expense->amount, 2),
                    'category' => $expense->category,
                    'expense_date' => \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y'),
                    'notes' => $expense->notes,
                    'receipt_image_urls' => $images,
                    'receipt_image_url' => count($images) > 0 ? $images[0] : null,
                ]
            ]);
        }

        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        return view('admin.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $title = $request->input('title') ?? $request->input('expense_name');
        $request->merge(['title' => $title]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'receipt_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $existingImages = $expense->receipt_images;
        $removedImages = $request->input('removed_receipt_images', []);
        if (is_string($removedImages)) {
            $removedImages = json_decode($removedImages, true) ?? [];
        }

        if ($request->input('remove_receipt_image') == '1') {
            foreach ($existingImages as $img) {
                if (file_exists(public_path($img))) {
                    @unlink(public_path($img));
                }
            }
            $existingImages = [];
        } elseif (!empty($removedImages)) {
            $keptImages = [];
            foreach ($existingImages as $img) {
                if (in_array($img, $removedImages)) {
                    if (file_exists(public_path($img))) {
                        @unlink(public_path($img));
                    }
                } else {
                    $keptImages[] = $img;
                }
            }
            $existingImages = $keptImages;
        }

        $newImagePaths = [];
        if ($request->hasFile('receipt_images')) {
            foreach ($request->file('receipt_images') as $file) {
                if ($file && $file->isValid()) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/expenses'), $filename);
                    $newImagePaths[] = 'uploads/expenses/' . $filename;
                }
            }
        } elseif ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/expenses'), $filename);
            $newImagePaths[] = 'uploads/expenses/' . $filename;
        }

        $finalImages = array_merge($existingImages, $newImagePaths);

        $receiptValue = null;
        if (count($finalImages) === 1) {
            $receiptValue = $finalImages[0];
        } elseif (count($finalImages) > 1) {
            $receiptValue = json_encode(array_values($finalImages));
        }

        $expense->update([
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'] ?? 'General',
            'notes' => $validated['notes'] ?? null,
            'receipt_image' => $receiptValue,
        ]);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense record updated successfully!');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_image && file_exists(public_path($expense->receipt_image))) {
            @unlink(public_path($expense->receipt_image));
        }

        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted successfully.');
    }

    /**
     * Profit & Loss Report with itemized Razorpay charges, product costs, shipping, and expenses.
     */
    public function profitLossReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Fetch order IDs with INACTIVE operations (dummy/test operations that exclude the order from financial reporting)
        $inactiveOperationOrderIds = \App\Models\OrderOperation::where('status', 'inactive')
            ->pluck('order_id')
            ->toArray();

        // Base Orders Query for paid/completed orders (excluding cancelled/refunded, INACTIVE operations, and test phone 9544832975)
        $ordersQuery = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            });

        if (count($inactiveOperationOrderIds) > 0) {
            $ordersQuery->whereNotIn('id', $inactiveOperationOrderIds);
        }

        $totalSalesRevenue = (clone $ordersQuery)->sum('subtotal');
        $totalShippingRevenue = (clone $ordersQuery)->sum('shipping');
        $totalGrossRevenue = (clone $ordersQuery)->sum('grand_total');

        // Revenue split by Payment Method
        $codSalesRevenue = (clone $ordersQuery)->where('payment_method', 'cod')->sum('grand_total');
        $onlineSalesRevenue = (clone $ordersQuery)->where('payment_method', 'online')->sum('grand_total');

        $totalOrdersCount = (clone $ordersQuery)->count();
        $codOrdersCount = (clone $ordersQuery)->where('payment_method', 'cod')->count();
        $onlineOrdersCount = (clone $ordersQuery)->where('payment_method', 'online')->count();

        // Calculate Product Cost Price for sold items
        $paidOrderIds = (clone $ordersQuery)->pluck('id');
        $totalProductCost = 0.00;
        if (Schema::hasColumn('products', 'cost_price') && count($paidOrderIds) > 0) {
            $totalProductCost = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('order_items.order_id', $paidOrderIds)
                ->sum(DB::raw('COALESCE(products.cost_price, 0) * order_items.quantity'));
        }

        // Calculate Razorpay Gateway Charges (Only for Online Paid orders)
        // Auto-recalculate if razorpay_total_charge is zero on online paid order
        $onlinePaidOrders = (clone $ordersQuery)->where('payment_method', 'online')->get();
        $feePct = (float) Setting::get('razorpay_fee_percent', 2.00);
        $gstPct = (float) Setting::get('razorpay_gst_percent', 18.00);

        foreach ($onlinePaidOrders as $o) {
            if ($o->razorpay_total_charge <= 0) {
                $o->calculateRazorpayCharge(null, $feePct, $gstPct);
            }
        }

        $totalRazorpayCharges = (clone $ordersQuery)->where('payment_method', 'online')->sum('razorpay_total_charge');

        // Recorded General Expenses
        $otherExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $expensesList = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        // Fetch ACTIVE Order Operations in the selected date range
        $activeOperationsQuery = \App\Models\OrderOperation::with(['order', 'product', 'orderItem', 'expenses'])
            ->where('status', 'active')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $activeOperationsList = (clone $activeOperationsQuery)->orderBy('created_at', 'desc')->get();
        $totalOperationRefunds = (float) (clone $activeOperationsQuery)->sum('total_refund_amount');
        $totalOperationExpenses = (float) (clone $activeOperationsQuery)->sum('additional_expense_total');
        $totalOperationAdjustment = (float) (clone $activeOperationsQuery)->sum('total_financial_adjustment');

        // Fetch ACTIVE Incomes (Wholesale & Manual Additional Incomes) in the selected date range
        $activeIncomesQuery = \App\Models\Income::where('status', 'active')
            ->whereBetween('income_date', [$startDate, $endDate]);

        $activeIncomesList = (clone $activeIncomesQuery)->orderBy('income_date', 'desc')->get();
        $totalAdditionalIncome = (float) (clone $activeIncomesQuery)->sum('total_income_amount');

        // Original P&L Base
        $originalExpenses = $totalProductCost + $totalShippingRevenue + $totalRazorpayCharges + $otherExpenses;
        $originalNetProfitLoss = ($totalGrossRevenue + $totalAdditionalIncome) - $originalExpenses;

        // Adjusted P&L including ACTIVE Order Operations & Active Incomes
        $totalCombinedRevenue = $totalGrossRevenue + $totalAdditionalIncome;
        $adjustedGrossRevenue = max(0, $totalCombinedRevenue - $totalOperationRefunds);
        $totalExpenses = $originalExpenses + $totalOperationExpenses;
        $netProfitLoss = $totalCombinedRevenue - $totalExpenses - $totalOperationRefunds;
        $isProfit = $netProfitLoss >= 0;

        return view('admin.expenses.profit_loss', compact(
            'startDate',
            'endDate',
            'totalSalesRevenue',
            'totalShippingRevenue',
            'totalGrossRevenue',
            'codSalesRevenue',
            'onlineSalesRevenue',
            'totalOrdersCount',
            'codOrdersCount',
            'onlineOrdersCount',
            'totalProductCost',
            'totalRazorpayCharges',
            'otherExpenses',
            'originalExpenses',
            'originalNetProfitLoss',
            'activeOperationsList',
            'totalOperationRefunds',
            'totalOperationExpenses',
            'totalOperationAdjustment',
            'adjustedGrossRevenue',
            'totalExpenses',
            'expensesList',
            'activeIncomesList',
            'totalAdditionalIncome',
            'totalCombinedRevenue',
            'netProfitLoss',
            'isProfit',
            'feePct',
            'gstPct'
        ));
    }

    /**
     * Dedicated Razorpay Payment Gateway Charges Report.
     */
    public function razorpayReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $feePct = (float) Setting::get('razorpay_fee_percent', 2.00);
        $gstPct = (float) Setting::get('razorpay_gst_percent', 18.00);

        $inactiveOperationOrderIds = \App\Models\OrderOperation::where('status', 'inactive')
            ->pluck('order_id')
            ->toArray();

        $query = Order::where('payment_method', 'online')
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('id', 'desc');

        if (count($inactiveOperationOrderIds) > 0) {
            $query->whereNotIn('id', $inactiveOperationOrderIds);
        }

        // Recalculate if any record missing fee data
        $orders = $query->paginate(20)->withQueryString();
        foreach ($orders as $o) {
            if ($o->razorpay_total_charge <= 0) {
                $o->calculateRazorpayCharge(null, $feePct, $gstPct);
            }
        }

        $summaryQuery = Order::where('payment_method', 'online')
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if (count($inactiveOperationOrderIds) > 0) {
            $summaryQuery->whereNotIn('id', $inactiveOperationOrderIds);
        }

        $totalOnlineRevenue = $summaryQuery->sum('grand_total');
        $totalRazorpayBaseFee = $summaryQuery->sum('razorpay_base_fee');
        $totalRazorpayGstFee = $summaryQuery->sum('razorpay_gst_fee');
        $totalRazorpayCharges = $summaryQuery->sum('razorpay_total_charge');
        $totalNetReceived = $summaryQuery->sum('razorpay_net_amount');

        if ($request->ajax() || $request->wantsJson()) {
            $desktopHtml = view('admin.reports.partials.razorpay_rows', compact('orders'))->render();

            return response()->json([
                'desktop_html' => $desktopHtml,
                'next_page_url' => $orders->nextPageUrl(),
                'has_more' => $orders->hasMorePages(),
                'total' => $orders->total(),
            ]);
        }

        return view('admin.reports.razorpay', compact(
            'orders',
            'startDate',
            'endDate',
            'feePct',
            'gstPct',
            'totalOnlineRevenue',
            'totalRazorpayBaseFee',
            'totalRazorpayGstFee',
            'totalRazorpayCharges',
            'totalNetReceived'
        ));
    }

}
