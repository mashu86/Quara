@extends('layouts.admin')

@section('title', 'Employee Master - ' . $siteName)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-users-gear text-info me-2"></i> Employee Master</h4>
            <p class="text-muted small mb-0">Manage employees, salary types (fixed vs non-fixed), and track financial summaries.</p>
        </div>
        <div>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-dark rounded-pill px-3 px-sm-4 shadow-sm text-nowrap w-100 w-sm-auto">
                <i class="fa-solid fa-plus me-1 text-warning"></i> Add Employee
            </a>
        </div>
    </div>

    <!-- Overall Salary Financial Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Total Employees</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalEmployeesCount) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-primary rounded-3 p-3">
                        <i class="fa-solid fa-id-card fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Total Salary Earned</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">₹{{ number_format($totalEarnedAll, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-info rounded-3 p-3">
                        <i class="fa-solid fa-briefcase fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Total Salary Paid</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">₹{{ number_format($totalPaidAll, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-success rounded-3 p-3">
                        <i class="fa-solid fa-money-check-dollar fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Total Outstanding</span>
                        <h3 class="fw-bold text-danger mb-0 mt-1">₹{{ number_format($totalOutstandingAll, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-danger rounded-3 p-3">
                        <i class="fa-solid fa-scale-unbalanced-flip fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.employees.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search by name, designation, phone..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select name="salary_type" class="form-select bg-light">
                        <option value="">All Salary Types</option>
                        <option value="fixed" {{ request('salary_type') === 'fixed' ? 'selected' : '' }}>Fixed Salary</option>
                        <option value="non_fixed" {{ request('salary_type') === 'non_fixed' ? 'selected' : '' }}>Non-Fixed Salary</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    @if(request()->hasAny(['search', 'salary_type']))
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Employees List Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Employee</th>
                            <th>Salary Type</th>
                            <th>Monthly Salary</th>
                            <th>Total Earned</th>
                            <th>Total Paid</th>
                            <th>Outstanding</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark">{{ $emp->name }}</div>
                                    <div class="small text-muted">
                                        @if($emp->designation) <span class="me-2"><i class="fa-solid fa-user-tag me-1"></i>{{ $emp->designation }}</span> @endif
                                        @if($emp->phone) <span><i class="fa-solid fa-phone me-1"></i>{{ $emp->phone }}</span> @endif
                                    </div>
                                </td>
                                <td>
                                    @if($emp->salary_type === 'fixed')
                                        <span class="badge bg-primary text-white rounded-pill px-3 py-1"><i class="fa-solid fa-lock me-1"></i> Fixed</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-pill px-3 py-1"><i class="fa-solid fa-sliders me-1"></i> Non-Fixed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($emp->salary_type === 'fixed' && $emp->monthly_salary)
                                        <span class="fw-semibold text-dark">₹{{ number_format($emp->monthly_salary, 2) }}</span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td><span class="fw-semibold text-dark">₹{{ number_format($emp->total_earned, 2) }}</span></td>
                                <td><span class="fw-semibold text-success">₹{{ number_format($emp->total_paid, 2) }}</span></td>
                                <td>
                                    @if($emp->outstanding_salary > 0)
                                        <span class="fw-bold text-danger">₹{{ number_format($emp->outstanding_salary, 2) }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Paid</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.employees.show', $emp->id) }}" class="btn btn-sm btn-outline-info rounded-start-pill text-nowrap" title="View Summary & History">
                                            <i class="fa-solid fa-eye"></i> <span class="d-none d-sm-inline">History</span>
                                        </a>
                                        <a href="{{ route('admin.employees.edit', $emp->id) }}" class="btn btn-sm btn-outline-dark" title="Edit Employee">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.employees.destroy', $emp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-end-pill" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-users-slash fs-3 d-block mb-2 text-secondary"></i>
                                    No employee records found. <a href="{{ route('admin.employees.create') }}" class="text-decoration-none fw-bold">Click here to add one.</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="p-3 border-top">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
