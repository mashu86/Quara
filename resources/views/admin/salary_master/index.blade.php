@extends('layouts.admin')

@section('title', 'Salary Master - ' . $siteName)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Header & Action Buttons -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-money-bill-transfer text-warning me-2"></i> Salary Master</h4>
            <p class="text-muted small mb-0">Record daily employee salary/work entries, settle unpaid balances, and manage financial payouts.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap w-100 w-sm-auto justify-content-start justify-content-sm-end">
            <button type="button" class="btn btn-dark rounded-pill px-3 px-sm-4 shadow-sm flex-fill flex-sm-grow-0 text-nowrap" data-bs-toggle="modal" data-bs-target="#addSalaryModal">
                <i class="fa-solid fa-plus me-1 text-warning"></i> Add Entry
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-3 px-sm-4 shadow-sm flex-fill flex-sm-grow-0 text-nowrap" data-bs-toggle="modal" data-bs-target="#settleSalaryModal" style="background-color: #6f42c1; border-color: #6f42c1;">
                <i class="fa-solid fa-hand-holding-dollar me-1"></i> Settle Unpaid
            </button>
        </div>
    </div>

    <!-- Overall Salary Financial Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
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

        <div class="col-12 col-sm-6 col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold d-block">Total Salary Paid</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">₹{{ number_format($totalPaidAll, 2) }}</h3>
                    </div>
                    <div class="stat-icon bg-light text-success rounded-3 p-3">
                        <i class="fa-solid fa-money-bill-wave fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4">
            <div class="stat-card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
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
            <form action="{{ route('admin.salary-master.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-sm-6 col-md-3">
                    <select name="employee_id" class="form-select bg-light">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-sm-6 col-md-3">
                    <input type="date" name="start_date" class="form-control bg-light" value="{{ request('start_date') }}" placeholder="Start Date">
                </div>

                <div class="col-6 col-sm-6 col-md-3">
                    <input type="date" name="end_date" class="form-control bg-light" value="{{ request('end_date') }}" placeholder="End Date">
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                    <select name="payment_status" class="form-select bg-light">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill"><i class="fa-solid fa-filter me-1"></i> <span class="d-md-none">Filter</span></button>
                    @if(request()->hasAny(['employee_id', 'start_date', 'end_date', 'payment_status']))
                        <a href="{{ route('admin.salary-master.index') }}" class="btn btn-outline-secondary rounded-pill px-3"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Records Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Work Date</th>
                            <th>Employee</th>
                            <th>Salary Amount</th>
                            <th>Amount Paid</th>
                            <th>Remaining Unpaid</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaries as $sal)
                            <tr>
                                <td class="ps-3 fw-bold text-dark">{{ $sal->date->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.employees.show', $sal->employee_id) }}" class="fw-bold text-decoration-none text-dark">
                                        {{ $sal->employee->name ?? 'Unknown' }}
                                    </a>
                                </td>
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
                                    <div class="btn-group">
                                        <a href="{{ route('admin.salary-master.edit', $sal->id) }}" class="btn btn-sm btn-outline-dark rounded-start-pill" title="Edit Entry">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.salary-master.destroy', $sal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this salary entry? Linked expense will also be removed.');">
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
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open fs-3 d-block mb-2 text-secondary"></i>
                                    No salary entries found. Click <strong>Add Salary Entry</strong> above to add one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($salaries->hasPages())
                <div class="p-3 border-top">
                    {{ $salaries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal 1: Add Daily Salary Entry -->
<div class="modal fade" id="addSalaryModal" tabindex="-1" aria-labelledby="addSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold" id="addSalaryModalLabel">
                    <i class="fa-solid fa-plus text-warning me-2"></i> Add Daily Salary Entry
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.salary-master.store') }}" method="POST" onsubmit="return handleAdminFormSubmit(this);">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} (Outstanding: ₹{{ number_format($emp->outstanding_salary, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Work / Salary Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        <div class="form-text">Each employee can have only ONE salary record per date.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Salary Amount (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">₹</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="e.g. 500" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Payment Status <span class="text-danger">*</span></label>
                        <select name="payment_status" class="form-select" required>
                            <option value="paid">Paid (Cash expense recorded immediately)</option>
                            <option value="unpaid">Unpaid (Kept as outstanding liability)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer / UPI</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Notes / Remarks</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional notes...">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1 text-warning"></i> Save Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Requirement #7 & #16 Settle Unpaid Amount -->
<div class="modal fade" id="settleSalaryModal" tabindex="-1" aria-labelledby="settleSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header text-white rounded-top-4 py-3" style="background-color: #6f42c1;">
                <h5 class="modal-title font-serif fw-bold" id="settleSalaryModalLabel">
                    <i class="fa-solid fa-hand-holding-dollar text-warning me-2"></i> Settle Unpaid Salary
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.salary-master.settle') }}" method="POST" onsubmit="return handleAdminFormSubmit(this);">
                @csrf
                <div class="modal-body p-4">
                    <!-- Employee Selection (Mandatory) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Select Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" id="settleEmployeeSelect" class="form-select" required>
                            <option value="">-- Select Employee to Settle --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" data-earned="{{ $emp->total_earned }}" data-paid="{{ $emp->total_paid }}" data-unpaid="{{ $emp->outstanding_salary }}">
                                    {{ $emp->name }} (Unpaid: ₹{{ number_format($emp->outstanding_salary, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Requirement #16 Employee Live Outstanding Card -->
                    <div id="settleEmployeeCard" class="bg-light p-3 rounded-4 mb-3" style="display: none;">
                        <h6 class="fw-bold text-dark mb-2" id="cardEmpName">Employee Summary</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border">
                                    <span class="extra-small text-muted d-block" style="font-size: 0.72rem;">Total Earned</span>
                                    <span class="fw-bold text-dark small" id="cardTotalEarned">₹0.00</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border">
                                    <span class="extra-small text-muted d-block" style="font-size: 0.72rem;">Already Paid</span>
                                    <span class="fw-bold text-success small" id="cardTotalPaid">₹0.00</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border border-danger-subtle">
                                    <span class="extra-small text-danger d-block fw-semibold" style="font-size: 0.72rem;">Current Unpaid</span>
                                    <span class="fw-bold text-danger small" id="cardCurrentUnpaid">₹0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Settlement Amount (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">₹</span>
                            <input type="number" step="0.01" min="0.01" name="settlement_amount" id="settlementAmountInput" class="form-control" placeholder="Enter amount to pay" required>
                        </div>
                        <div id="settlementTypeBadge" class="mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        <div class="form-text">Expense will be recorded on this actual payment date.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer / UPI</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Notes / Remarks</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Partial salary settlement for August">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="settleSubmitBtn" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #6f42c1; border-color: #6f42c1;">
                        <i class="fa-solid fa-check me-1"></i> Confirm Settlement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const settleEmpSelect = document.getElementById('settleEmployeeSelect');
        const settleCard = document.getElementById('settleEmployeeCard');
        const cardEmpName = document.getElementById('cardEmpName');
        const cardTotalEarned = document.getElementById('cardTotalEarned');
        const cardTotalPaid = document.getElementById('cardTotalPaid');
        const cardCurrentUnpaid = document.getElementById('cardCurrentUnpaid');
        const settlementAmountInput = document.getElementById('settlementAmountInput');
        const settlementTypeBadge = document.getElementById('settlementTypeBadge');
        const settleSubmitBtn = document.getElementById('settleSubmitBtn');

        let currentMaxUnpaid = 0;

        settleEmpSelect.addEventListener('change', function() {
            const selectedOpt = settleEmpSelect.options[settleEmpSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const earned = parseFloat(selectedOpt.getAttribute('data-earned')) || 0;
                const paid = parseFloat(selectedOpt.getAttribute('data-paid')) || 0;
                const unpaid = parseFloat(selectedOpt.getAttribute('data-unpaid')) || 0;

                cardEmpName.textContent = selectedOpt.textContent.split('(')[0].trim();
                cardTotalEarned.textContent = '₹' + earned.toFixed(2);
                cardTotalPaid.textContent = '₹' + paid.toFixed(2);
                cardCurrentUnpaid.textContent = '₹' + unpaid.toFixed(2);
                currentMaxUnpaid = unpaid;

                settleCard.style.display = 'block';
                checkSettlementAmount();
            } else {
                settleCard.style.display = 'none';
                settlementTypeBadge.innerHTML = '';
                currentMaxUnpaid = 0;
            }
        });

        function checkSettlementAmount() {
            const val = parseFloat(settlementAmountInput.value) || 0;

            if (!settleEmpSelect.value) {
                settlementTypeBadge.innerHTML = '';
                settleSubmitBtn.disabled = false;
                return;
            }

            if (val <= 0) {
                settlementTypeBadge.innerHTML = '';
                settleSubmitBtn.disabled = false;
                return;
            }

            if (val > currentMaxUnpaid + 0.001) {
                settlementTypeBadge.innerHTML = '<span class="badge bg-danger text-white p-2 rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i> Amount exceeds current unpaid (₹' + currentMaxUnpaid.toFixed(2) + ')</span>';
                settleSubmitBtn.disabled = true;
            } else if (Math.abs(val - currentMaxUnpaid) < 0.01) {
                settlementTypeBadge.innerHTML = '<span class="badge bg-success text-white p-2 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i> Full Payment (Remaining Unpaid: ₹0.00)</span>';
                settleSubmitBtn.disabled = false;
            } else {
                const rem = currentMaxUnpaid - val;
                settlementTypeBadge.innerHTML = '<span class="badge bg-warning text-dark p-2 rounded-pill"><i class="fa-solid fa-circle-info me-1"></i> Partial Payment (Remaining Unpaid: ₹' + rem.toFixed(2) + ')</span>';
                settleSubmitBtn.disabled = false;
            }
        }

        settlementAmountInput.addEventListener('input', checkSettlementAmount);
    });
</script>
@endsection
