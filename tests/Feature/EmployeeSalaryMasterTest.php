<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSalaryMasterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Test Employee Add/Edit fixed vs non-fixed salary validation and absence of manual paid status.
     */
    public function test_employee_creation_and_salary_type_validation()
    {
        // 1. Fixed Salary requires monthly_salary
        $response = $this->actingAs($this->admin)->post(route('admin.employees.store'), [
            'name' => 'Ramesh Kumar',
            'salary_type' => 'fixed',
            'monthly_salary' => '',
        ]);
        $response->assertSessionHasErrors(['monthly_salary']);

        // 2. Fixed Salary with monthly_salary succeeds
        $response = $this->actingAs($this->admin)->post(route('admin.employees.store'), [
            'name' => 'Ramesh Kumar',
            'designation' => 'Store Manager',
            'salary_type' => 'fixed',
            'monthly_salary' => 20000.00,
        ]);
        $response->assertRedirect(route('admin.employees.index'));
        $this->assertDatabaseHas('employees', [
            'name' => 'Ramesh Kumar',
            'salary_type' => 'fixed',
            'monthly_salary' => 20000.00,
        ]);

        // 3. Non-Fixed Salary allows null monthly_salary
        $response = $this->actingAs($this->admin)->post(route('admin.employees.store'), [
            'name' => 'Suresh Master',
            'designation' => 'Tailor',
            'salary_type' => 'non_fixed',
            'monthly_salary' => '',
        ]);
        $response->assertRedirect(route('admin.employees.index'));
        $this->assertDatabaseHas('employees', [
            'name' => 'Suresh Master',
            'salary_type' => 'non_fixed',
            'monthly_salary' => null,
        ]);
    }

    /**
     * Test Rule #9: One salary entry per employee per date constraint.
     */
    public function test_one_salary_entry_per_employee_per_day_constraint()
    {
        $employee = Employee::create([
            'name' => 'Anil Verma',
            'salary_type' => 'non_fixed',
        ]);

        // First entry on Sep 5
        $this->actingAs($this->admin)->post(route('admin.salary-master.store'), [
            'employee_id' => $employee->id,
            'date' => '2026-09-05',
            'amount' => 500.00,
            'payment_status' => 'paid',
        ])->assertRedirect(route('admin.salary-master.index'));

        // Second entry on same date (Sep 5) must be rejected
        $response = $this->actingAs($this->admin)->post(route('admin.salary-master.store'), [
            'employee_id' => $employee->id,
            'date' => '2026-09-05',
            'amount' => 300.00,
            'payment_status' => 'paid',
        ]);
        $response->assertSessionHas('error');
        $this->assertEquals(1, Salary::where('employee_id', $employee->id)->whereDate('date', '2026-09-05')->count());
    }

    /**
     * Test Paid vs Unpaid initial salary entry expense logic.
     */
    public function test_paid_vs_unpaid_initial_salary_entry_expense_logic()
    {
        $employee = Employee::create([
            'name' => 'Vijay',
            'salary_type' => 'non_fixed',
        ]);

        // 1. Paid Entry of ₹90 on Sep 1
        $this->actingAs($this->admin)->post(route('admin.salary-master.store'), [
            'employee_id' => $employee->id,
            'date' => '2026-09-01',
            'amount' => 90.00,
            'payment_status' => 'paid',
        ]);

        $employee->refresh();
        $this->assertEquals(90.00, $employee->total_earned);
        $this->assertEquals(90.00, $employee->total_paid);
        $this->assertEquals(0.00, $employee->outstanding_salary);

        // Expense created for ₹90 on Sep 1
        $this->assertDatabaseHas('expenses', [
            'category' => 'Salary',
            'amount' => 90.00,
        ]);

        // 2. Unpaid Entry of ₹10 on Sep 2
        $this->actingAs($this->admin)->post(route('admin.salary-master.store'), [
            'employee_id' => $employee->id,
            'date' => '2026-09-02',
            'amount' => 10.00,
            'payment_status' => 'unpaid',
        ]);

        $employee->refresh();
        $this->assertEquals(100.00, $employee->total_earned);
        $this->assertEquals(90.00, $employee->total_paid);
        $this->assertEquals(10.00, $employee->outstanding_salary);

        // Total cash expenses from salary remains ₹90 (Unpaid ₹10 generated 0 cash expense)
        $this->assertEquals(90.00, Expense::where('category', 'Salary')->sum('amount'));
    }

    /**
     * Test Partial and Full Settlement of unpaid salary with payment date & expense creation.
     */
    public function test_partial_and_full_unpaid_salary_settlement()
    {
        $employee = Employee::create([
            'name' => 'Karan',
            'salary_type' => 'non_fixed',
        ]);

        // Create ₹100 earned (₹90 paid, ₹10 unpaid)
        Salary::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-01',
            'amount' => 90.00,
            'paid_amount' => 90.00,
            'payment_status' => 'paid',
        ]);
        SalaryPayment::create([
            'employee_id' => $employee->id,
            'amount' => 90.00,
            'payment_date' => '2026-09-01',
            'transaction_type' => 'initial_payment',
        ]);
        Salary::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-02',
            'amount' => 10.00,
            'paid_amount' => 0.00,
            'payment_status' => 'unpaid',
        ]);

        $employee->refresh();
        $this->assertEquals(10.00, $employee->outstanding_salary);

        // 1. Attempting to settle ₹11 must be rejected
        $response = $this->actingAs($this->admin)->post(route('admin.salary-master.settle'), [
            'employee_id' => $employee->id,
            'settlement_amount' => 11.00,
            'payment_date' => '2026-09-05',
        ]);
        $response->assertSessionHas('error');

        // 2. Partial Settlement of ₹5 on Sep 5
        $this->actingAs($this->admin)->post(route('admin.salary-master.settle'), [
            'employee_id' => $employee->id,
            'settlement_amount' => 5.00,
            'payment_date' => '2026-09-05',
        ])->assertRedirect(route('admin.salary-master.index'));

        $employee->refresh();
        $this->assertEquals(100.00, $employee->total_earned);
        $this->assertEquals(95.00, $employee->total_paid);
        $this->assertEquals(5.00, $employee->outstanding_salary);

        // Expense generated for ₹5 on payment date Sep 5
        $this->assertDatabaseHas('expenses', [
            'category' => 'Salary',
            'amount' => 5.00,
        ]);

        // 3. Full Settlement of remaining ₹5 on Sep 6
        $this->actingAs($this->admin)->post(route('admin.salary-master.settle'), [
            'employee_id' => $employee->id,
            'settlement_amount' => 5.00,
            'payment_date' => '2026-09-06',
        ])->assertRedirect(route('admin.salary-master.index'));

        $employee->refresh();
        $this->assertEquals(100.00, $employee->total_earned);
        $this->assertEquals(100.00, $employee->total_paid);
        $this->assertEquals(0.00, $employee->outstanding_salary);

        // Total cash expenses generated from salary = ₹5 + ₹5 = ₹10 (matching total paid)
        $this->assertEquals(10.00, Expense::where('category', 'Salary')->sum('amount'));
    }

    /**
     * Test editing salary entry updates calculations without duplicate expenses.
     */
    public function test_editing_salary_entry_updates_calculations_without_duplicate_expenses()
    {
        $employee = Employee::create([
            'name' => 'Deepak',
            'salary_type' => 'non_fixed',
        ]);

        // Create unpaid entry of ₹100
        $salary = Salary::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-01',
            'amount' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'unpaid',
        ]);

        $this->assertEquals(0.00, Expense::where('category', 'Salary')->sum('amount'));

        // Edit salary entry to Paid status
        $this->actingAs($this->admin)->put(route('admin.salary-master.update', $salary->id), [
            'date' => '2026-09-01',
            'amount' => 100.00,
            'payment_status' => 'paid',
        ]);

        $employee->refresh();
        $this->assertEquals(100.00, $employee->total_paid);
        $this->assertEquals(0.00, $employee->outstanding_salary);
        $this->assertEquals(100.00, Expense::where('category', 'Salary')->sum('amount'));
        $this->assertEquals(1, Expense::where('category', 'Salary')->count());

        // Edit paid amount from 100 to 120
        $this->actingAs($this->admin)->put(route('admin.salary-master.update', $salary->id), [
            'date' => '2026-09-01',
            'amount' => 120.00,
            'payment_status' => 'paid',
        ]);

        $employee->refresh();
        $this->assertEquals(120.00, $employee->total_paid);
        $this->assertEquals(120.00, Expense::where('category', 'Salary')->sum('amount'));
        $this->assertEquals(1, Expense::where('category', 'Salary')->count()); // No duplicate expense created!
    }
}
