<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Setting;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpenseController extends Controller
{
    /**
     * Unified calculation helper for Total Business Expenses & Costs across all modules
     */
    public static function getBusinessExpensesSummary($startDate = null, $endDate = null)
    {
        $inactiveOrderIds = \App\Models\OrderOperation::where('status', 'inactive')->pluck('order_id')->toArray();

        $ordersQuery = Order::whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            });

        if (count($inactiveOrderIds) > 0) {
            $ordersQuery->whereNotIn('id', $inactiveOrderIds);
        }

        if ($startDate && $endDate) {
            $ordersQuery->whereBetween(DB::raw('COALESCE(sale_date, created_at)'), [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $ordersQuery->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '>=', $startDate);
        } elseif ($endDate) {
            $ordersQuery->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '<=', $endDate);
        }

        $paidOrderIds = (clone $ordersQuery)->pluck('id');

        $productCost = 0.00;
        if (Schema::hasColumn('products', 'cost_price') && count($paidOrderIds) > 0) {
            $productCost = (float) DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('order_items.order_id', $paidOrderIds)
                ->where(function ($q) {
                    $q->where('order_items.item_status', 'active')
                      ->orWhere('order_items.inventory_condition', 'do_not_restock');
                })
                ->sum(DB::raw('COALESCE(products.cost_price, 0) * order_items.quantity'));
        }

        $shippingCost = (float) (clone $ordersQuery)->sum('shipping');

        $onlinePaidOrders = (clone $ordersQuery)->where('payment_method', 'online')->get();
        $feePct = (float) Setting::get('razorpay_fee_percent', 2.00);
        $gstPct = (float) Setting::get('razorpay_gst_percent', 18.00);
        foreach ($onlinePaidOrders as $o) {
            if ($o->razorpay_total_charge <= 0) {
                $o->calculateRazorpayCharge(null, $feePct, $gstPct);
            }
        }
        $razorpayCharges = (float) (clone $ordersQuery)->where('payment_method', 'online')->sum('razorpay_total_charge');

        $generalExpensesQuery = Expense::query();
        if ($startDate && $endDate) {
            $generalExpensesQuery->whereBetween('expense_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $generalExpensesQuery->where('expense_date', '>=', $startDate);
        } elseif ($endDate) {
            $generalExpensesQuery->where('expense_date', '<=', $endDate);
        }
        $generalExpenses = (float) $generalExpensesQuery->sum('amount');

        $opsQuery = \App\Models\OrderOperation::where('status', 'active');
        if ($startDate && $endDate) {
            $opsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $opsQuery->where('created_at', '>=', $startDate . ' 00:00:00');
        } elseif ($endDate) {
            $opsQuery->where('created_at', '<=', $endDate . ' 23:59:59');
        }
        $operationExpenses = (float) (clone $opsQuery)->sum('additional_expense_total');

        $refundsQuery = \App\Models\OrderRefund::whereHas('orderOperation', function ($q) {
            $q->where('status', 'active');
        });
        if ($startDate && $endDate) {
            $refundsQuery->whereBetween('refund_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $refundsQuery->where('refund_date', '>=', $startDate);
        } elseif ($endDate) {
            $refundsQuery->where('refund_date', '<=', $endDate);
        }
        $operationRefunds = (float) $refundsQuery->sum('refund_amount');

        $totalCombinedExpenses = $productCost + $razorpayCharges + $generalExpenses + $operationExpenses + $operationRefunds;

        return [
            'total' => $totalCombinedExpenses,
            'product_cost' => $productCost,
            'shipping_cost' => 0.00,
            'shipping_revenue' => (float) (clone $ordersQuery)->sum('shipping'),
            'razorpay_charges' => $razorpayCharges,
            'general_expenses' => $generalExpenses,
            'operation_expenses' => $operationExpenses,
            'operation_refunds' => $operationRefunds,
        ];
    }

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
        $todayStr = $today->toDateString();
        $startOfMonth = $today->copy()->startOfMonth()->toDateString();
        $endOfMonth = $today->copy()->endOfMonth()->toDateString();
        $startOfWeek = $today->copy()->startOfWeek()->toDateString();
        $endOfWeek = $today->copy()->endOfWeek()->toDateString();

        $allTimeBusinessExpenses = self::getBusinessExpensesSummary();
        $thisMonthBusinessExpenses = self::getBusinessExpensesSummary($startOfMonth, $endOfMonth);
        $thisWeekBusinessExpenses = self::getBusinessExpensesSummary($startOfWeek, $endOfWeek);
        $todayBusinessExpenses = self::getBusinessExpensesSummary($todayStr, $todayStr);

        $totalExpenses = $allTimeBusinessExpenses['total'];
        $totalGeneralExpenses = $allTimeBusinessExpenses['general_expenses'];
        $totalRefundsExpense = (float) \App\Models\OrderOperation::where('status', 'active')->sum('total_refund_amount');
        $thisMonthExpenses = $thisMonthBusinessExpenses['total'];
        $thisWeekExpenses = $thisWeekBusinessExpenses['total'];
        $todayExpenses = $todayBusinessExpenses['total'];

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
            'totalGeneralExpenses',
            'allTimeBusinessExpenses',
            'thisMonthBusinessExpenses',
            'totalRefundsExpense',
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
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:12288',
            'receipt_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:12288',
        ]);

        $imagePaths = [];
        if ($request->hasFile('receipt_images')) {
            foreach ($request->file('receipt_images') as $file) {
                if ($file && $file->isValid()) {
                    $savedPath = ImageOptimizerService::optimizeAndStore($file, 'expenses', 'public');
                    $imagePaths[] = 'storage/' . $savedPath;
                }
            }
        } elseif ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            if ($file && $file->isValid()) {
                $savedPath = ImageOptimizerService::optimizeAndStore($file, 'expenses', 'public');
                $imagePaths[] = 'storage/' . $savedPath;
            }
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
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:12288',
            'receipt_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:12288',
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
                    $savedPath = ImageOptimizerService::optimizeAndStore($file, 'expenses', 'public');
                    $newImagePaths[] = 'storage/' . $savedPath;
                }
            }
        } elseif ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            if ($file && $file->isValid()) {
                $savedPath = ImageOptimizerService::optimizeAndStore($file, 'expenses', 'public');
                $newImagePaths[] = 'storage/' . $savedPath;
            }
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
        $ordersQuery = Order::whereBetween(DB::raw('COALESCE(sale_date, created_at)'), [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
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

        // Calculate Product Cost Price for sold/frozen items (excluding restocked returns)
        $paidOrderIds = (clone $ordersQuery)->pluck('id');
        $totalProductCost = 0.00;
        if (Schema::hasColumn('products', 'cost_price') && count($paidOrderIds) > 0) {
            $totalProductCost = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('order_items.order_id', $paidOrderIds)
                ->where(function ($q) {
                    $q->where('order_items.item_status', 'active')
                      ->orWhere('order_items.inventory_condition', 'do_not_restock');
                })
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
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('return_date', [$startDate, $endDate])
                  ->orWhereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            });

        $activeOperationsList = (clone $activeOperationsQuery)->orderBy('created_at', 'desc')->get();
        $totalOperationRefunds = (float) \App\Models\OrderRefund::whereBetween('refund_date', [$startDate, $endDate])->sum('refund_amount');
        $totalOperationExpenses = (float) (clone $activeOperationsQuery)->sum('additional_expense_total');
        $totalOperationAdjustment = $totalOperationRefunds + $totalOperationExpenses;

        // Fetch ACTIVE Incomes (Wholesale & Manual Additional Incomes) in the selected date range
        $activeIncomesQuery = \App\Models\Income::where('status', 'active')
            ->whereBetween('income_date', [$startDate, $endDate]);

        $activeIncomesList = (clone $activeIncomesQuery)->orderBy('income_date', 'desc')->get();
        $totalAdditionalIncome = (float) (clone $activeIncomesQuery)->sum('total_income_amount');

        // Original P&L Base
        $originalExpenses = $totalProductCost + $totalRazorpayCharges + $otherExpenses;
        $originalNetProfitLoss = ($totalGrossRevenue + $totalAdditionalIncome) - $originalExpenses;

        // Adjusted P&L including ACTIVE Order Operations & Active Incomes
        $totalCombinedRevenue = $totalGrossRevenue + $totalAdditionalIncome;
        $adjustedGrossRevenue = max(0, $totalCombinedRevenue - $totalOperationRefunds);
        $totalExpenses = $originalExpenses + $totalOperationExpenses + $totalOperationRefunds;
        $netProfitLoss = $totalCombinedRevenue - $totalExpenses;
        $isProfit = $netProfitLoss >= 0;

        // ALL-TIME (Overall Business) Profit & Loss Calculation
        $allTimeOrdersQuery = Order::whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            });

        if (count($inactiveOperationOrderIds) > 0) {
            $allTimeOrdersQuery->whereNotIn('id', $inactiveOperationOrderIds);
        }

        $allTimeGrossRevenue = (clone $allTimeOrdersQuery)->sum('grand_total');
        $allTimeShippingRevenue = (clone $allTimeOrdersQuery)->sum('shipping');
        $allTimePaidOrderIds = (clone $allTimeOrdersQuery)->pluck('id');

        $allTimeProductCost = 0.00;
        if (Schema::hasColumn('products', 'cost_price') && count($allTimePaidOrderIds) > 0) {
            $allTimeProductCost = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('order_items.order_id', $allTimePaidOrderIds)
                ->where(function ($q) {
                    $q->where('order_items.item_status', 'active')
                      ->orWhere('order_items.inventory_condition', 'do_not_restock');
                })
                ->sum(DB::raw('COALESCE(products.cost_price, 0) * order_items.quantity'));
        }

        $allTimeRazorpayCharges = (clone $allTimeOrdersQuery)->where('payment_method', 'online')->sum('razorpay_total_charge');
        $allTimeOtherExpenses = (float) Expense::sum('amount');

        $allTimeActiveOps = \App\Models\OrderOperation::where('status', 'active');
        $allTimeOperationRefunds = (float) \App\Models\OrderRefund::sum('refund_amount');
        $allTimeOperationExpenses = (float) (clone $allTimeActiveOps)->sum('additional_expense_total');

        $allTimeActiveIncomes = \App\Models\Income::where('status', 'active');
        $allTimeAdditionalIncome = (float) (clone $allTimeActiveIncomes)->sum('total_income_amount');

        $allTimeCombinedRevenue = $allTimeGrossRevenue + $allTimeAdditionalIncome;
        $allTimeTotalExpenses = $allTimeProductCost + $allTimeRazorpayCharges + $allTimeOtherExpenses + $allTimeOperationExpenses + $allTimeOperationRefunds;
        $allTimeNetProfitLoss = $allTimeCombinedRevenue - $allTimeTotalExpenses;
        $allTimeIsProfit = $allTimeNetProfitLoss >= 0;

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
            'allTimeCombinedRevenue',
            'allTimeTotalExpenses',
            'allTimeOperationRefunds',
            'allTimeNetProfitLoss',
            'allTimeIsProfit',
            'allTimeGrossRevenue',
            'allTimeAdditionalIncome',
            'allTimeProductCost',
            'allTimeOtherExpenses',
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
            ->whereBetween(DB::raw('COALESCE(sale_date, created_at)'), [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
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
            ->whereBetween(DB::raw('COALESCE(sale_date, created_at)'), [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if (count($inactiveOperationOrderIds) > 0) {
            $summaryQuery->whereNotIn('id', $inactiveOperationOrderIds);
        }

        $totalOnlineRevenue = $summaryQuery->sum('grand_total');
        $totalRazorpayBaseFee = $summaryQuery->sum('razorpay_base_fee');
        $totalRazorpayGstFee = $summaryQuery->sum('razorpay_gst_fee');
        $totalRazorpayCharges = $summaryQuery->sum('razorpay_total_charge');
        $totalNetReceived = $summaryQuery->sum('razorpay_net_amount');

        // Today's Razorpay Charges (Asia/Kolkata timezone)
        $todayDate = Carbon::now('Asia/Kolkata')->toDateString();
        $todayRazorpayQuery = Order::where('payment_method', 'online')
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('customer_phone')
                  ->orWhere('customer_phone', 'NOT LIKE', '%9544832975%');
            })
            ->whereDate(DB::raw('COALESCE(sale_date, created_at)'), $todayDate);

        if (count($inactiveOperationOrderIds) > 0) {
            $todayRazorpayQuery->whereNotIn('id', $inactiveOperationOrderIds);
        }

        $todayOnlineOrders = (clone $todayRazorpayQuery)->get();
        foreach ($todayOnlineOrders as $to) {
            if ($to->razorpay_total_charge <= 0) {
                $to->calculateRazorpayCharge(null, $feePct, $gstPct);
            }
        }
        $todayRazorpayCharges = (float) (clone $todayRazorpayQuery)->sum('razorpay_total_charge');

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
            'todayRazorpayCharges',
            'totalOnlineRevenue',
            'totalRazorpayBaseFee',
            'totalRazorpayGstFee',
            'totalRazorpayCharges',
            'totalNetReceived'
        ));
    }

    /**
     * Dedicated Refunded Products & Customer Refund Expense Report.
     */
    public function refundedProductsReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $search = $request->get('search');
        $invConditionFilter = $request->get('inventory_condition'); // all, return_to_stock, do_not_restock

        // Active operations that involve returns, cancellations or refunds
        $query = \App\Models\OrderOperation::with(['order', 'product.images', 'orderItem', 'replacementProduct', 'replacementProductSize', 'refunds'])
            ->where('status', 'active')
            ->whereBetween(DB::raw('COALESCE(return_date, DATE(created_at))'), [$startDate, $endDate]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($oq) use ($search) {
                    $oq->where('order_number', 'LIKE', "%{$search}%")
                       ->orWhere('customer_name', 'LIKE', "%{$search}%")
                       ->orWhere('customer_phone', 'LIKE', "%{$search}%");
                })->orWhereHas('orderItem', function ($iq) use ($search) {
                    $iq->where('product_name', 'LIKE', "%{$search}%");
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        if (!empty($invConditionFilter) && $invConditionFilter !== 'all') {
            $query->where('inventory_condition', $invConditionFilter);
        }

        // Summary Stats across active operations in date range
        $summaryQuery = \App\Models\OrderOperation::where('status', 'active')
            ->whereBetween(DB::raw('COALESCE(return_date, DATE(created_at))'), [$startDate, $endDate]);

        // Aggregate actual money refunded based on refund_date from OrderRefund table
        $totalRefundedAmount = (float) \App\Models\OrderRefund::whereHas('orderOperation', function ($q) {
                $q->where('status', 'active');
            })
            ->whereBetween('refund_date', [$startDate, $endDate])
            ->sum('refund_amount');

        $totalRefundedItemsCount = (int) (clone $summaryQuery)->count();
        $restockedItemsCount = (int) (clone $summaryQuery)->where('inventory_condition', 'return_to_stock')->count();
        $frozenItemsCount = (int) (clone $summaryQuery)->where('inventory_condition', 'do_not_restock')->count();

        $refundOperations = $query->orderBy(DB::raw('COALESCE(return_date, DATE(created_at))'), 'desc')->paginate(15)->withQueryString();

        return view('admin.reports.refunded_products', compact(
            'refundOperations',
            'startDate',
            'endDate',
            'search',
            'invConditionFilter',
            'totalRefundedAmount',
            'totalRefundedItemsCount',
            'restockedItemsCount',
            'frozenItemsCount'
        ));
    }
}
