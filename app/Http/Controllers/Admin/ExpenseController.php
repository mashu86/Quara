<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::orderBy('expense_date', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        $expenses = $query->paginate(15)->withQueryString();
        $totalExpenseAmount = $query->sum('amount');

        return view('admin.expenses.index', compact('expenses', 'totalExpenseAmount'));
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
        ]);

        Expense::create([
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'] ?? 'General',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted successfully.');
    }

    public function profitLossReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Orders Revenue (Delivered / Paid orders)
        $ordersQuery = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('payment_status', ['paid', 'completed']);

        $totalSalesRevenue = $ordersQuery->sum('subtotal');
        $totalShippingRevenue = $ordersQuery->sum('shipping');
        $totalGrossRevenue = $ordersQuery->sum('grand_total');

        $totalOrdersCount = $ordersQuery->count();
        $onlineOrdersCount = (clone $ordersQuery)->where(function($q){
            $q->where('order_source', 'website')->orWhereNull('order_source');
        })->count();
        $manualOrdersCount = (clone $ordersQuery)->where('order_source', 'manual')->count();

        // Expenses
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $expensesList = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        // Profit / Loss calculation
        $netProfitLoss = $totalGrossRevenue - $totalExpenses;
        $isProfit = $netProfitLoss >= 0;

        return view('admin.expenses.profit_loss', compact(
            'startDate',
            'endDate',
            'totalSalesRevenue',
            'totalShippingRevenue',
            'totalGrossRevenue',
            'totalOrdersCount',
            'onlineOrdersCount',
            'manualOrdersCount',
            'totalExpenses',
            'expensesList',
            'netProfitLoss',
            'isProfit'
        ));
    }
}
