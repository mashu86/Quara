<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\SalaryPayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryMasterController extends Controller
{
    /**
     * Display Salary Master list & summary.
     */
    public function index(Request $request)
    {
        $query = Salary::with(['employee', 'payments']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $salaries = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $employees = Employee::orderBy('name', 'asc')->get();

        // Financial Totals
        $allEmployees = Employee::all();
        $totalEarnedAll = $allEmployees->sum(fn($e) => $e->total_earned);
        $totalPaidAll = $allEmployees->sum(fn($e) => $e->total_paid);
        $totalOutstandingAll = max(0.00, round($totalEarnedAll - $totalPaidAll, 2));

        return view('admin.salary_master.index', compact(
            'salaries',
            'employees',
            'totalEarnedAll',
            'totalPaidAll',
            'totalOutstandingAll'
        ));
    }

    /**
     * Store a newly created salary entry (One entry per employee per date).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_status' => 'required|in:paid,unpaid',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $salaryDate = Carbon::parse($validated['date'])->toDateString();

        // Enforce Rule #9: Employee + Date = One Salary Entry
        $existing = Salary::where('employee_id', $employee->id)
            ->whereDate('date', $salaryDate)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', "A salary entry for {$employee->name} on {$salaryDate} already exists. Please edit the existing entry instead.");
        }

        DB::transaction(function () use ($validated, $employee, $salaryDate) {
            $amount = (float) $validated['amount'];
            $isPaid = ($validated['payment_status'] === 'paid');
            $paymentMethod = $validated['payment_method'] ?? 'cash';
            $notes = $validated['notes'] ?? null;
            $adminName = auth()->check() ? auth()->user()->name : 'Admin';

            // 1. Create Salary Entry
            $salary = Salary::create([
                'employee_id' => $employee->id,
                'date' => $salaryDate,
                'amount' => $amount,
                'paid_amount' => $isPaid ? $amount : 0.00,
                'payment_status' => $isPaid ? 'paid' : 'unpaid',
                'notes' => $notes,
                'created_by' => $adminName,
            ]);

            // 2. If Paid, create Expense & SalaryPayment on salaryDate
            if ($isPaid) {
                $expense = Expense::create([
                    'title' => "Salary Payment - {$employee->name}",
                    'amount' => $amount,
                    'category' => 'Salary',
                    'expense_date' => $salaryDate,
                    'notes' => $notes ?? "Initial salary payment for {$employee->name} on {$salaryDate}",
                ]);

                SalaryPayment::create([
                    'employee_id' => $employee->id,
                    'salary_id' => $salary->id,
                    'amount' => $amount,
                    'payment_date' => $salaryDate,
                    'payment_method' => $paymentMethod,
                    'transaction_type' => 'initial_payment',
                    'expense_id' => $expense->id,
                    'notes' => $notes,
                    'created_by' => $adminName,
                ]);
            }
        });

        return redirect()->route('admin.salary-master.index')
            ->with('success', 'Salary entry added successfully!');
    }

    /**
     * Settle unpaid salary for a selected employee.
     */
    public function settle(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'settlement_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $settlementAmount = (float) $validated['settlement_amount'];
        $outstanding = $employee->outstanding_salary;
        $paymentDate = Carbon::parse($validated['payment_date'])->toDateString();
        $paymentMethod = $validated['payment_method'] ?? 'cash';
        $notes = $validated['notes'] ?? null;
        $adminName = auth()->check() ? auth()->user()->name : 'Admin';

        // Validation Rule #6: Max settlement cannot exceed current outstanding
        if ($settlementAmount > $outstanding + 0.001) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Settlement amount (₹" . number_format($settlementAmount, 2) . ") cannot exceed employee's current outstanding balance (₹" . number_format($outstanding, 2) . ").");
        }

        DB::transaction(function () use ($employee, $settlementAmount, $paymentDate, $paymentMethod, $notes, $adminName) {
            // 1. Create Cash Expense record on paymentDate
            $expense = Expense::create([
                'title' => "Salary Settlement - {$employee->name}",
                'amount' => $settlementAmount,
                'category' => 'Salary',
                'expense_date' => $paymentDate,
                'notes' => $notes ?? "Unpaid salary settlement for {$employee->name}",
            ]);

            // 2. Create SalaryPayment transaction
            $salaryPayment = SalaryPayment::create([
                'employee_id' => $employee->id,
                'salary_id' => null,
                'amount' => $settlementAmount,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'transaction_type' => 'settlement',
                'expense_id' => $expense->id,
                'notes' => $notes,
                'created_by' => $adminName,
            ]);

            // 3. Allocate settlement amount to unpaid salary records (oldest date first)
            $unpaidSalaries = Salary::where('employee_id', $employee->id)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->orderBy('date', 'asc')
                ->get();

            $remainingToAllocate = $settlementAmount;
            foreach ($unpaidSalaries as $sal) {
                if ($remainingToAllocate <= 0) break;

                $unpaidOnRecord = (float) $sal->amount - (float) $sal->paid_amount;
                if ($unpaidOnRecord <= 0) continue;

                $allocate = min($remainingToAllocate, $unpaidOnRecord);
                $newPaidAmount = round((float)$sal->paid_amount + $allocate, 2);
                $newStatus = ($newPaidAmount >= (float)$sal->amount - 0.001) ? 'paid' : 'partial';

                $sal->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $newStatus,
                ]);

                $remainingToAllocate -= $allocate;
            }
        });

        return redirect()->route('admin.salary-master.index')
            ->with('success', "Salary settlement of ₹" . number_format($settlementAmount, 2) . " recorded successfully!");
    }

    /**
     * Show edit form for a salary record.
     */
    public function edit(Salary $salary)
    {
        $salary->load('employee');
        return view('admin.salary_master.edit', compact('salary'));
    }

    /**
     * Update an existing salary entry safely without duplicate expenses.
     */
    public function update(Request $request, Salary $salary)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_status' => 'required|in:paid,unpaid',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $newDate = Carbon::parse($validated['date'])->toDateString();
        $newAmount = (float) $validated['amount'];
        $newStatus = $validated['payment_status'];
        $adminName = auth()->check() ? auth()->user()->name : 'Admin';

        // Check unique constraint if date changed
        if ($newDate !== $salary->date->format('Y-m-d')) {
            $existing = Salary::where('employee_id', $salary->employee_id)
                ->whereDate('date', $newDate)
                ->where('id', '!=', $salary->id)
                ->exists();

            if ($existing) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Another salary entry for this employee on {$newDate} already exists.");
            }
        }

        DB::transaction(function () use ($salary, $newAmount, $newStatus, $newDate, $validated, $adminName) {
            $initialPayment = SalaryPayment::where('salary_id', $salary->id)
                ->where('transaction_type', 'initial_payment')
                ->first();

            if ($newStatus === 'paid') {
                if ($initialPayment) {
                    // Update existing initial payment and linked expense
                    $initialPayment->update([
                        'amount' => $newAmount,
                        'payment_date' => $newDate,
                        'notes' => $validated['notes'] ?? $salary->notes,
                    ]);

                    if ($initialPayment->expense) {
                        $initialPayment->expense->update([
                            'amount' => $newAmount,
                            'expense_date' => $newDate,
                            'notes' => $validated['notes'] ?? $salary->notes,
                        ]);
                    }
                } else {
                    // Create new initial payment & expense
                    $expense = Expense::create([
                        'title' => "Salary Payment - {$salary->employee->name}",
                        'amount' => $newAmount,
                        'category' => 'Salary',
                        'expense_date' => $newDate,
                        'notes' => $validated['notes'] ?? "Salary payment for {$salary->employee->name}",
                    ]);

                    SalaryPayment::create([
                        'employee_id' => $salary->employee_id,
                        'salary_id' => $salary->id,
                        'amount' => $newAmount,
                        'payment_date' => $newDate,
                        'payment_method' => 'cash',
                        'transaction_type' => 'initial_payment',
                        'expense_id' => $expense->id,
                        'notes' => $validated['notes'] ?? null,
                        'created_by' => $adminName,
                    ]);
                }

                $salary->update([
                    'date' => $newDate,
                    'amount' => $newAmount,
                    'paid_amount' => $newAmount,
                    'payment_status' => 'paid',
                    'notes' => $validated['notes'] ?? null,
                ]);
            } else {
                // Changing to Unpaid
                if ($initialPayment) {
                    if ($initialPayment->expense) {
                        $initialPayment->expense->delete();
                    }
                    $initialPayment->delete();
                }

                $salary->update([
                    'date' => $newDate,
                    'amount' => $newAmount,
                    'paid_amount' => 0.00,
                    'payment_status' => 'unpaid',
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.salary-master.index')
            ->with('success', 'Salary record updated successfully!');
    }

    /**
     * Delete salary entry and cleanup linked payments/expenses.
     */
    public function destroy(Salary $salary)
    {
        DB::transaction(function () use ($salary) {
            $payments = SalaryPayment::where('salary_id', $salary->id)->get();
            foreach ($payments as $p) {
                if ($p->expense) {
                    $p->expense->delete();
                }
                $p->delete();
            }

            $salary->delete();
        });

        return redirect()->route('admin.salary-master.index')
            ->with('success', 'Salary record deleted successfully!');
    }
}
