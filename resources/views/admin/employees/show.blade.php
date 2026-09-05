@extends('layouts.admin')

@section('title', 'Employee Profile - ' . $employee->name)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-id-card text-info me-2"></i> {{ $employee->name }}</h4>
            <p class="text-muted small mb-0">
                @if($employee->designation) <span class="me-2"><i class="fa-solid fa-user-tag me-1"></i>{{ $employee->designation }}</span> @endif
                @if($employee->phone) <span class="me-2"><i class="fa-solid fa-phone me-1"></i>{{ $employee->phone }}</span> @endif
                @if($employee->email) <span><i class="fa-solid fa-envelope me-1"></i>{{ $employee->email }}</span> @endif
            </p>
        </div>
        <div class="d-flex gap-2 w-100 w-sm-auto">
            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-outline-dark rounded-pill px-3 flex-fill flex-sm-grow-0 text-nowrap">
                <i class="fa-solid fa-pen me-1"></i> Edit Profile
            </a>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3 flex-fill flex-sm-grow-0 text-nowrap">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Requirement #15: Employee Salary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Total Earned</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">₹{{ number_format($totalEarned, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-info rounded-3 p-3">
                        <i class="fa-solid fa-briefcase fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Total Paid</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">₹{{ number_format($totalPaid, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-success rounded-3 p-3">
                        <i class="fa-solid fa-money-bill-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Total Outstanding</span>
                        <h3 class="fw-bold text-danger mb-0 mt-1">₹{{ number_format($outstanding, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-danger rounded-3 p-3">
                        <i class="fa-solid fa-scale-unbalanced-flip fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-3" id="employeeTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold" id="salaries-tab" data-bs-toggle="tab" data-bs-target="#salaries-tab-pane" type="button" role="tab"><i class="fa-solid fa-calendar-day me-1"></i> Salary Work Entries ({{ $employee->salaries->count() }})</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-tab-pane" type="button" role="tab"><i class="fa-solid fa-receipt me-1"></i> Cash Payment History ({{ $employee->salaryPayments->count() }})</button>
        </li>
    </ul>

    <div class="tab-content" id="employeeTabsContent">
        <!-- Tab 1: Salary Work Entries -->
        <div class="tab-pane fade show active" id="salaries-tab-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Work / Salary Date</th>
                                    <th>Salary Earned</th>
                                    <th>Paid Amount</th>
                                    <th>Remaining Unpaid</th>
                                    <th>Payment Status</th>
                                    <th>Notes</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->salaries as $sal)
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">{{ $sal->date->format('M d, Y') }}</td>
                                        <td><span class="fw-bold text-dark">₹{{ number_format($sal->amount, 2) }}</span></td>
                                        <td><span class="fw-semibold text-success">₹{{ number_format($sal->paid_amount, 2) }}</span></td>
                                        <td>
                                            @if($sal->unpaid_amount > 0)
                                                <span class="fw-bold text-danger">₹{{ number_format($sal->unpaid_amount, 2) }}</span>
                                            @else
                                                <span class="text-muted">₹0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sal->payment_status === 'paid')
                                                <span class="badge bg-success text-white rounded-pill px-3"><i class="fa-solid fa-check me-1"></i> Paid</span>
                                            @elseif($sal->payment_status === 'partial')
                                                <span class="badge bg-warning text-dark rounded-pill px-3"><i class="fa-solid fa-clock me-1"></i> Partial</span>
                                            @else
                                                <span class="badge bg-danger text-white rounded-pill px-3"><i class="fa-solid fa-circle-exclamation me-1"></i> Unpaid</span>
                                            @endif
                                        </td>
                                        <td><span class="small text-muted">{{ $sal->notes ?: '-' }}</span></td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('admin.salary-master.edit', $sal->id) }}" class="btn btn-sm btn-outline-dark rounded-pill">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No salary work entries recorded for this employee yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Requirement #17 Cash Payment History -->
        <div class="tab-pane fade" id="payments-tab-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Payment Date</th>
                                    <th>Transaction Type</th>
                                    <th>Amount Paid</th>
                                    <th>Payment Method</th>
                                    <th>Linked Expense</th>
                                    <th>Notes / Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->salaryPayments as $pmt)
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">{{ $pmt->payment_date->format('M d, Y') }}</td>
                                        <td>
                                            @if($pmt->transaction_type === 'initial_payment')
                                                <span class="badge bg-info text-dark rounded-pill px-3">Initial Payment</span>
                                            @else
                                                <span class="badge bg-purple text-white rounded-pill px-3" style="background-color: #6f42c1;">Unpaid Settlement</span>
                                            @endif
                                        </td>
                                        <td><span class="fw-bold text-success">₹{{ number_format($pmt->amount, 2) }}</span></td>
                                        <td><span class="badge bg-light text-dark border">{{ strtoupper($pmt->payment_method) }}</span></td>
                                        <td>
                                            @if($pmt->expense)
                                                <span class="small text-muted"><i class="fa-solid fa-file-invoice-dollar text-success me-1"></i> Expense #{{ $pmt->expense_id }} (₹{{ number_format($pmt->expense->amount, 2) }})</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td><span class="small text-muted">{{ $pmt->notes ?: '-' }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No cash payments recorded for this employee yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
