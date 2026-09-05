<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('designation', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('salary_type')) {
            $query->where('salary_type', $request->salary_type);
        }

        $employees = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        // Calculate aggregate metrics across all employees
        $allEmployees = Employee::all();
        $totalEmployeesCount = $allEmployees->count();
        $totalEarnedAll = $allEmployees->sum(fn($emp) => $emp->total_earned);
        $totalPaidAll = $allEmployees->sum(fn($emp) => $emp->total_paid);
        $totalOutstandingAll = max(0.00, round($totalEarnedAll - $totalPaidAll, 2));

        return view('admin.employees.index', compact(
            'employees',
            'totalEmployeesCount',
            'totalEarnedAll',
            'totalPaidAll',
            'totalOutstandingAll'
        ));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        return view('admin.employees.create');
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'designation' => 'nullable|string|max:255',
            'salary_type' => 'required|in:fixed,non_fixed',
            'monthly_salary' => 'required_if:salary_type,fixed|nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'monthly_salary.required_if' => 'Monthly Salary is required for Fixed Salary employees.',
        ]);

        if ($validated['salary_type'] === 'non_fixed') {
            $validated['monthly_salary'] = null;
        }

        Employee::create($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully!');
    }

    /**
     * Display the specified employee details & salary summary.
     */
    public function show(Employee $employee)
    {
        $employee->load([
            'salaries' => fn($q) => $q->orderBy('date', 'desc'),
            'salaryPayments' => fn($q) => $q->orderBy('payment_date', 'desc'),
        ]);

        $totalEarned = $employee->total_earned;
        $totalPaid = $employee->total_paid;
        $outstanding = $employee->outstanding_salary;

        return view('admin.employees.show', compact('employee', 'totalEarned', 'totalPaid', 'outstanding'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'designation' => 'nullable|string|max:255',
            'salary_type' => 'required|in:fixed,non_fixed',
            'monthly_salary' => 'required_if:salary_type,fixed|nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'monthly_salary.required_if' => 'Monthly Salary is required for Fixed Salary employees.',
        ]);

        if ($validated['salary_type'] === 'non_fixed') {
            $validated['monthly_salary'] = null;
        }

        $employee->update($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee details updated successfully!');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee record deleted successfully!');
    }
}
