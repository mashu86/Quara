@extends('layouts.admin')

@section('title', 'Employee Master - ' . $siteName)

@section('content')
<style>
    @media (max-width: 767.98px) {
        .stat-card-title {
            font-size: 0.60rem !important;
            letter-spacing: 0.2px;
        }
        .stat-card-value {
            font-size: 0.92rem !important;
        }
        .stat-card-icon {
            padding: 0.35rem !important;
            font-size: 0.80rem !important;
        }
        .employee-mobile-card {
            border-radius: 10px !important;
        }
        .emp-name-mobile {
            font-size: 0.85rem !important;
        }
        .emp-sub-mobile {
            font-size: 0.68rem !important;
        }
        .emp-stat-box {
            padding: 0.30rem 0.35rem !important;
            border-radius: 6px !important;
        }
        .emp-stat-label {
            font-size: 0.55rem !important;
            text-transform: uppercase;
        }
        .emp-stat-val {
            font-size: 0.76rem !important;
            font-weight: 700;
        }
        .btn-emp-action {
            font-size: 0.68rem !important;
            padding: 0.20rem 0.45rem !important;
        }
        .spotlight-stat-box {
            padding: 0.35rem 0.2rem !important;
            border-radius: 6px !important;
        }
        .spotlight-stat-label {
            font-size: 0.55rem !important;
            letter-spacing: 0.2px;
        }
        .spotlight-stat-val {
            font-size: 0.78rem !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 mb-md-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-users-gear text-info me-2"></i> Employee Master</h4>
            <p class="text-muted small mb-0">Manage employees, salary types, and track financial summaries.</p>
        </div>
        <div>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-dark rounded-pill px-3 px-sm-4 shadow-sm text-nowrap w-100 w-sm-auto">
                <i class="fa-solid fa-plus me-1 text-warning"></i> Add Employee
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 mb-md-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.employees.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Box -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search name, designation, phone..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Select Employee Filter -->
                <div class="col-12 col-md-3">
                    <select name="employee_id" class="form-select bg-light fw-semibold" onchange="this.form.submit()">
                        <option value="">👤 All Employees</option>
                        @foreach($allEmployeesList as $empOpt)
                            <option value="{{ $empOpt->id }}" {{ request('employee_id') == $empOpt->id ? 'selected' : '' }}>
                                {{ $empOpt->name }} {{ $empOpt->designation ? '('.$empOpt->designation.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Salary Type Filter -->
                <div class="col-12 col-md-3">
                    <select name="salary_type" class="form-select bg-light">
                        <option value="">All Salary Types</option>
                        <option value="fixed" {{ request('salary_type') === 'fixed' ? 'selected' : '' }}>Fixed Salary</option>
                        <option value="non_fixed" {{ request('salary_type') === 'non_fixed' ? 'selected' : '' }}>Non-Fixed Salary</option>
                    </select>
                </div>

                <!-- Submit & Clear Buttons -->
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    @if(request()->hasAny(['search', 'salary_type', 'employee_id']))
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Selected Employee Spotlight Banner (If Employee Filter Active) -->
    @if($selectedEmployee)
        <div class="card border-0 shadow-sm rounded-4 mb-3 mb-md-4 bg-dark text-white overflow-hidden">
            <div class="card-body p-3 p-md-4">
                <!-- Header Info & Actions -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                            <span class="badge bg-warning text-dark font-monospace text-uppercase" style="font-size: 0.62rem; letter-spacing: 0.5px;">Selected Profile</span>
                            <span class="badge bg-secondary text-capitalize" style="font-size: 0.62rem;">{{ str_replace('_', ' ', $selectedEmployee->salary_type) }} Salary</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="bg-warning bg-opacity-25 text-warning rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px;">
                                <i class="fa-solid fa-user text-warning" style="font-size: 0.75rem;"></i>
                            </div>
                            <h5 class="fw-bold text-white mb-0 text-capitalize text-truncate" style="font-size: 1.05rem;">{{ $selectedEmployee->name }}</h5>
                        </div>
                        <div class="text-light small opacity-75 d-flex flex-wrap align-items-center gap-2.5 mt-1" style="font-size: 0.75rem;">
                            @if($selectedEmployee->designation)
                                <span><i class="fa-solid fa-briefcase text-warning me-1"></i>{{ $selectedEmployee->designation }}</span>
                            @endif
                            @if($selectedEmployee->phone)
                                <span><i class="fa-solid fa-phone text-info me-1"></i><a href="tel:{{ $selectedEmployee->phone }}" class="text-light text-decoration-none">{{ $selectedEmployee->phone }}</a></span>
                            @endif
                        </div>
                    </div>

                    <!-- Desktop Action Buttons -->
                    <div class="d-none d-md-flex align-items-center gap-2">
                        <a href="{{ route('admin.employees.show', $selectedEmployee->id) }}" class="btn btn-sm btn-warning fw-bold rounded-pill px-3.5 py-2 text-dark shadow-sm">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> History
                        </a>
                    </div>
                </div>

                <!-- 3 Financial Stat Boxes Grid -->
                <div class="row g-1.5 g-md-2">
                    <div class="col-4">
                        <div class="bg-white bg-opacity-10 spotlight-stat-box text-center h-100">
                            <div class="small text-light opacity-75 text-uppercase spotlight-stat-label">Total Earned</div>
                            <div class="fw-bold text-warning spotlight-stat-val text-nowrap mt-0.5">
                                ₹{{ number_format($selectedEmployee->total_earned, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="bg-white bg-opacity-10 spotlight-stat-box text-center h-100">
                            <div class="small text-light opacity-75 text-uppercase spotlight-stat-label">Total Paid</div>
                            <div class="fw-bold text-success spotlight-stat-val text-nowrap mt-0.5">
                                ₹{{ number_format($selectedEmployee->total_paid, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="bg-danger bg-opacity-25 border border-danger-subtle spotlight-stat-box text-center h-100">
                            <div class="small text-light opacity-75 text-uppercase spotlight-stat-label">Balance Due</div>
                            <div class="fw-bold text-danger spotlight-stat-val text-nowrap mt-0.5">
                                ₹{{ number_format($selectedEmployee->outstanding_salary, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Action Buttons (< 768px) -->
                <div class="d-flex d-md-none justify-content-center gap-2 mt-3 pt-2.5 border-top border-secondary border-opacity-50">
                    <a href="{{ route('admin.employees.show', $selectedEmployee->id) }}" class="btn btn-sm btn-warning fw-semibold rounded-pill py-1.5 px-3 text-dark shadow-sm text-center text-nowrap d-flex align-items-center justify-content-center gap-1" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 0.70rem;"></i> <span>History</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Salary Financial Summary Cards (2x2 Grid on Mobile) -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card border-0 shadow-sm rounded-4 h-100 bg-white p-2.5 p-md-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block stat-card-title">
                            {{ $selectedEmployee ? 'Selected Profile' : 'Total Employees' }}
                        </span>
                        <h3 class="fw-bold text-dark mb-0 mt-1 stat-card-value">{{ number_format($totalEmployeesCount) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-primary rounded-3 p-2 p-md-3 stat-card-icon">
                        <i class="fa-solid fa-id-card fs-5 fs-md-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card border-0 shadow-sm rounded-4 h-100 bg-white p-2.5 p-md-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block stat-card-title">Total Earned</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1 stat-card-value">₹{{ number_format($totalEarnedAll, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-info rounded-3 p-2 p-md-3 stat-card-icon">
                        <i class="fa-solid fa-briefcase fs-5 fs-md-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card border-0 shadow-sm rounded-4 h-100 bg-white p-2.5 p-md-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block stat-card-title">Total Paid</span>
                        <h3 class="fw-bold text-success mb-0 mt-1 stat-card-value">₹{{ number_format($totalPaidAll, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-success rounded-3 p-2 p-md-3 stat-card-icon">
                        <i class="fa-solid fa-money-check-dollar fs-5 fs-md-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card border-0 shadow-sm rounded-4 h-100 bg-white p-2.5 p-md-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block stat-card-title">
                            {{ $selectedEmployee ? 'Balance Due' : 'Total Balance' }}
                        </span>
                        <h3 class="fw-bold text-danger mb-0 mt-1 stat-card-value">₹{{ number_format($totalOutstandingAll, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-danger rounded-3 p-2 p-md-3 stat-card-icon">
                        <i class="fa-solid fa-scale-unbalanced-flip fs-5 fs-md-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Card View (< 768px) -->
    <div class="d-block d-md-none mb-3">
        @forelse($employees as $emp)
            <div class="card border-0 shadow-sm mb-2.5 employee-mobile-card {{ request('employee_id') == $emp->id ? 'border-start border-4 border-warning' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold text-dark emp-name-mobile">{{ $emp->name }}</div>
                            <div class="text-muted emp-sub-mobile mt-0.5">
                                @if($emp->designation) <span class="me-2"><i class="fa-solid fa-user-tag me-1"></i>{{ $emp->designation }}</span> @endif
                                @if($emp->phone) <a href="tel:{{ $emp->phone }}" class="text-decoration-none text-muted"><i class="fa-solid fa-phone me-1"></i>{{ $emp->phone }}</a> @endif
                            </div>
                        </div>
                        <div>
                            @if($emp->salary_type === 'fixed')
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size: 0.65rem;"><i class="fa-solid fa-lock me-1"></i> Fixed</span>
                            @else
                                <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1" style="font-size: 0.65rem;"><i class="fa-solid fa-sliders me-1"></i> Non-Fixed</span>
                            @endif
                        </div>
                    </div>

                    <!-- 3-Stats Grid -->
                    <div class="row g-1.5 my-2.5">
                        <div class="col-4">
                            <div class="bg-light emp-stat-box text-center">
                                <div class="text-muted emp-stat-label">Total Earned</div>
                                <div class="emp-stat-val text-dark text-nowrap">₹{{ number_format($emp->total_earned, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light emp-stat-box text-center">
                                <div class="text-muted emp-stat-label">Total Paid</div>
                                <div class="emp-stat-val text-success text-nowrap">₹{{ number_format($emp->total_paid, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-{{ $emp->outstanding_salary > 0 ? 'danger-subtle border border-danger-subtle' : 'light' }} emp-stat-box text-center">
                                <div class="text-muted emp-stat-label">Balance Due</div>
                                <div class="emp-stat-val text-{{ $emp->outstanding_salary > 0 ? 'danger' : 'success' }} text-nowrap">
                                    ₹{{ number_format($emp->outstanding_salary, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top flex-wrap gap-2">
                        <span class="small text-muted" style="font-size: 0.70rem;">
                            Monthly: {{ $emp->salary_type === 'fixed' && $emp->monthly_salary ? '₹'.number_format($emp->monthly_salary, 2) : 'N/A' }}
                        </span>
                        <div class="d-flex gap-2 ms-auto align-items-center">
                            <a href="{{ route('admin.employees.show', $emp->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1 btn-emp-action text-nowrap">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> History
                            </a>
                            <a href="{{ route('admin.employees.edit', $emp->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-2 py-1 btn-emp-action" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.employees.destroy', $emp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete employee {{ $emp->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 btn-emp-action" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">
                <i class="fa-solid fa-users-slash fs-3 d-block mb-2 text-secondary"></i>
                No employee records found.
            </div>
        @endforelse
    </div>

    <!-- Desktop Table View (>= 768px) -->
    <div class="card border-0 shadow-sm rounded-4 d-none d-md-block">
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
                            <th>Balance Due</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr class="{{ request('employee_id') == $emp->id ? 'table-warning' : '' }}">
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
                                            <i class="fa-solid fa-eye me-1"></i>History
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
                                    No employee records found matching your filters. <a href="{{ route('admin.employees.index') }}" class="text-decoration-none fw-bold">Reset filters</a> or <a href="{{ route('admin.employees.create') }}" class="text-decoration-none fw-bold">add a new employee.</a>
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
