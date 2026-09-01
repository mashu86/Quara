<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Income::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('income_name', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('income_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('income_date', '<=', $request->end_date);
        }

        $today = Carbon::now('Asia/Kolkata')->startOfDay();

        // 1. Today Active Income
        $todayIncome = Income::active()
            ->whereDate('income_date', $today->toDateString())
            ->sum('total_income_amount');

        // 2. This Month Active Income
        $thisMonthIncome = Income::active()
            ->whereBetween('income_date', [
                $today->copy()->startOfMonth()->toDateString(),
                $today->copy()->endOfMonth()->toDateString(),
            ])
            ->sum('total_income_amount');

        // 3. This Year Active Income
        $thisYearIncome = Income::active()
            ->whereBetween('income_date', [
                $today->copy()->startOfYear()->toDateString(),
                $today->copy()->endOfYear()->toDateString(),
            ])
            ->sum('total_income_amount');

        // 4. Selected Period Active Income ("ee periodilil njaan cheytha income")
        $periodQuery = Income::active();
        if ($request->filled('start_date')) {
            $periodQuery->whereDate('income_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $periodQuery->whereDate('income_date', '<=', $request->end_date);
        }
        $selectedPeriodIncome = $periodQuery->sum('total_income_amount');

        $filteredActiveTotal = (clone $query)->where('status', 'active')->sum('total_income_amount');

        $incomes = $query->orderBy('income_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $desktopHtml = view('admin.incomes.partials.desktop_rows', compact('incomes'))->render();

            return response()->json([
                'desktop_html' => $desktopHtml,
                'next_page_url' => $incomes->nextPageUrl(),
                'has_more' => $incomes->hasMorePages(),
                'total' => $incomes->total(),
            ]);
        }

        return view('admin.incomes.index', compact(
            'incomes',
            'todayIncome',
            'thisMonthIncome',
            'thisYearIncome',
            'selectedPeriodIncome',
            'filteredActiveTotal'
        ));
    }

    public function create()
    {
        return view('admin.incomes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'income_name' => 'required|string|max:255',
            'income_price' => 'required|numeric|min:0',
            'type' => 'required|in:wholesale_selling,other',
            'selling_pieces' => 'nullable|integer|min:1',
            'total_income_amount' => 'required|numeric|min:0',
            'income_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($validated['type'] === 'wholesale_selling' && empty($validated['selling_pieces'])) {
            $validated['selling_pieces'] = 1;
        }

        Income::create([
            'income_name' => $validated['income_name'],
            'income_price' => $validated['income_price'],
            'type' => $validated['type'],
            'selling_pieces' => $validated['selling_pieces'] ?? 1,
            'total_income_amount' => $validated['total_income_amount'],
            'income_date' => $validated['income_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.incomes.index')->with('success', 'Income record added successfully!');
    }

    public function show(Income $income)
    {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'income' => [
                    'id' => $income->id,
                    'income_name' => $income->income_name,
                    'income_price' => number_format($income->income_price, 2),
                    'type' => $income->type,
                    'type_label' => $income->type_label,
                    'selling_pieces' => $income->selling_pieces,
                    'total_income_amount' => number_format($income->total_income_amount, 2),
                    'income_date' => Carbon::parse($income->income_date)->format('M d, Y'),
                    'status' => $income->status,
                    'notes' => $income->notes,
                ]
            ]);
        }

        return view('admin.incomes.index', ['income' => $income]);
    }

    public function edit(Income $income)
    {
        return view('admin.incomes.edit', compact('income'));
    }

    public function update(Request $request, Income $income)
    {
        $validated = $request->validate([
            'income_name' => 'required|string|max:255',
            'income_price' => 'required|numeric|min:0',
            'type' => 'required|in:wholesale_selling,other',
            'selling_pieces' => 'nullable|integer|min:1',
            'total_income_amount' => 'required|numeric|min:0',
            'income_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($validated['type'] === 'wholesale_selling' && empty($validated['selling_pieces'])) {
            $validated['selling_pieces'] = 1;
        }

        $income->update([
            'income_name' => $validated['income_name'],
            'income_price' => $validated['income_price'],
            'type' => $validated['type'],
            'selling_pieces' => $validated['selling_pieces'] ?? 1,
            'total_income_amount' => $validated['total_income_amount'],
            'income_date' => $validated['income_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.incomes.index')->with('success', 'Income record updated successfully!');
    }

    public function destroy(Income $income)
    {
        $income->delete();
        return redirect()->route('admin.incomes.index')->with('success', 'Income record deleted successfully.');
    }

    public function toggleStatus(Income $income)
    {
        $newStatus = $income->status === 'active' ? 'inactive' : 'active';
        $income->update(['status' => $newStatus]);

        return redirect()->back()->with('success', "Income status updated to {$newStatus}.");
    }
}
