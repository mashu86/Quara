@extends('layouts.admin')

@section('title', 'Contractual Post & Wallet Management - ' . $siteName)

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .stat-card-gold {
        background: linear-gradient(135deg, #FFFDF0 0%, #FFFFFF 100%);
        border: 1px solid rgba(212, 175, 55, 0.3) !important;
    }
    .stat-card-gold .stat-icon {
        background-color: rgba(212, 175, 55, 0.15);
        color: var(--qw-gold-dark);
    }
    .stat-card-blue {
        background: linear-gradient(135deg, #F0F7FF 0%, #FFFFFF 100%);
        border: 1px solid rgba(13, 110, 253, 0.2) !important;
    }
    .stat-card-blue .stat-icon {
        background-color: rgba(13, 110, 253, 0.12);
        color: #0d6efd;
    }
    .stat-card-orange {
        background: linear-gradient(135deg, #FFF8F0 0%, #FFFFFF 100%);
        border: 1px solid rgba(253, 126, 20, 0.2) !important;
    }
    .stat-card-orange .stat-icon {
        background-color: rgba(253, 126, 20, 0.12);
        color: #fd7e14;
    }
    .stat-card-purple {
        background: linear-gradient(135deg, #F9F5FF 0%, #FFFFFF 100%);
        border: 1px solid rgba(111, 66, 193, 0.2) !important;
    }
    .stat-card-purple .stat-icon {
        background-color: rgba(111, 66, 193, 0.12);
        color: #6f42c1;
    }
    .nav-pills .nav-link.active {
        background-color: var(--qw-gold) !important;
        color: #000000 !important;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.25);
    }
    .nav-pills .nav-link {
        color: #444444;
        font-weight: 600;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link:hover {
        border-color: var(--qw-gold);
    }
    @media (max-width: 575.98px) {
        .nav-pills .nav-link {
            padding: 5px 8px !important;
            font-size: 0.72rem !important;
        }
        .contractual-header-actions {
            width: 100%;
            margin-top: 6px;
        }
        .contractual-header-actions .btn {
            width: 100% !important;
            font-size: 0.75rem !important;
            padding: 6px 12px !important;
        }
        .modal-footer .btn {
            font-size: 0.75rem !important;
            padding: 6px 10px !important;
        }
        .btn-sm, .btn {
            font-size: 0.75rem !important;
        }
        .stat-card {
            padding: 10px 10px !important;
        }
        .stat-card .stat-title {
            font-size: 0.65rem !important;
            line-height: 1.15 !important;
            letter-spacing: -0.1px;
        }
        .stat-card h3 {
            font-size: 1.05rem !important;
            margin-top: 2px !important;
            font-weight: 800 !important;
        }
        .stat-card .stat-icon {
            width: 26px !important;
            height: 26px !important;
            font-size: 0.75rem !important;
            flex-shrink: 0 !important;
            border-radius: 8px !important;
        }
        .stat-card small {
            font-size: 0.65rem !important;
            line-height: 1.1 !important;
        }
        .table th, .table td {
            font-size: 0.75rem !important;
            padding: 6px 8px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Page Title & Main Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="font-serif fw-bold text-dark mb-1 fs-4 fs-md-3">
                <i class="fa-solid fa-envelopes-bulk text-warning me-2"></i> Contractual Post & Wallet Management
            </h3>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <p class="text-muted small mb-0">Prepaid India Post wallet tracking, contractual courier operations & expense synchronization.</p>
                <span class="badge bg-dark text-white rounded-pill px-3 py-1.5 small shadow-sm" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-id-card text-warning me-1.5"></i> Customer ID: <strong class="text-warning font-monospace">198300613</strong>
                </span>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="bg-white border rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-id-card text-gold"></i>
                <span class="small text-muted">Customer ID:</span>
                <span class="fw-bold text-dark font-monospace">198300613</span>
            </div>
            <a href="https://app.indiapost.gov.in/customer-selfservice/login" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark rounded-pill px-3 py-2 fw-semibold small shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-up-right-from-square text-warning"></i>
                <span>Open India Post Self Service</span>
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Current Wallet Balance -->
        <div class="col-6 col-md-3">
            <div class="stat-card stat-card-gold h-100">
                <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                    <span class="stat-title text-muted fw-semibold text-uppercase">Wallet Balance</span>
                    <div class="stat-icon">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-0 fs-4 fs-md-3">₹{{ number_format($currentWalletBalance, 2) }}</h3>
                <small class="text-muted extra-small">Available prepaid balance</small>
            </div>
        </div>

        <!-- Total Recharged -->
        <div class="col-6 col-md-3">
            <div class="stat-card stat-card-blue h-100">
                <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                    <span class="stat-title text-muted fw-semibold text-uppercase">Total Recharged</span>
                    <div class="stat-icon">
                        <i class="fa-solid fa-circle-plus"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-0 fs-4 fs-md-3">₹{{ number_format($totalRecharged, 2) }}</h3>
                <small class="text-muted extra-small">Lifetime wallet recharges</small>
            </div>
        </div>

        <!-- Total Usage -->
        <div class="col-6 col-md-3">
            <div class="stat-card stat-card-orange h-100">
                <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                    <span class="stat-title text-muted fw-semibold text-uppercase">Contractual Used</span>
                    <div class="stat-icon">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-0 fs-4 fs-md-3">₹{{ number_format($totalUsage, 2) }}</h3>
                <small class="text-muted extra-small">Total deducted for couriers</small>
            </div>
        </div>

        <!-- Total Couriers -->
        <div class="col-6 col-md-3">
            <div class="stat-card stat-card-purple h-100">
                <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                    <span class="stat-title text-muted fw-semibold text-uppercase">Total Couriers</span>
                    <div class="stat-icon">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-0 fs-4 fs-md-3">{{ number_format($totalCouriers) }}</h3>
                <small class="text-muted extra-small">Total contractual posts sent</small>
            </div>
        </div>
    </div>

    <!-- Date Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.contractual-posts.index') }}" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="tab" value="{{ $activeTab }}">

                <div class="col-6 col-md-4 col-lg-3">
                    <label class="form-label small fw-bold text-uppercase mb-1 text-dark">From Date <span class="text-muted extra-small font-monospace">(dd-mm-yyyy)</span></label>
                    <input type="text" name="start_date" class="form-control rounded-3 flatpickr-date bg-white" value="{{ $startDate }}" placeholder="DD-MM-YYYY">
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <label class="form-label small fw-bold text-uppercase mb-1 text-dark">To Date <span class="text-muted extra-small font-monospace">(dd-mm-yyyy)</span></label>
                    <input type="text" name="end_date" class="form-control rounded-3 flatpickr-date bg-white" value="{{ $endDate }}" placeholder="DD-MM-YYYY">
                </div>
                <div class="col-12 col-md-4 col-lg-6 d-flex gap-2">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold btn-sm py-2">
                        <i class="fa-solid fa-filter text-warning me-1"></i> Filter Records
                    </button>
                    @if($startDate || $endDate)
                        <a href="{{ route('admin.contractual-posts.index', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm py-2" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3">
                <ul class="nav nav-pills fw-semibold gap-2 w-100 w-sm-auto d-flex flex-row" id="contractualTab" role="tablist">
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link rounded-pill w-100 px-2 px-md-4 py-2 small {{ $activeTab === 'wallet' ? 'active' : '' }}" id="wallet-tab" data-bs-toggle="tab" data-bs-target="#wallet-pane" type="button" role="tab">
                            <i class="fa-solid fa-wallet me-1"></i> <span class="d-none d-md-inline">Wallet Recharges</span><span class="d-md-none">Recharge</span> ({{ $recharges->total() }})
                        </button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link rounded-pill w-100 px-2 px-md-4 py-2 small {{ $activeTab === 'courier' ? 'active' : '' }}" id="courier-tab" data-bs-toggle="tab" data-bs-target="#courier-pane" type="button" role="tab">
                            <i class="fa-solid fa-paper-plane me-1"></i> <span class="d-none d-md-inline">Contractual Couriers</span><span class="d-md-none">Send Courier</span> ({{ $couriers->total() }})
                        </button>
                    </li>
                </ul>

                <div class="contractual-header-actions text-end">
                    <button type="button" id="tabActionRechargeBtn" class="btn btn-dark rounded-pill px-3 px-md-4 py-2 fw-bold text-white btn-sm shadow-sm {{ $activeTab === 'courier' ? 'd-none' : '' }}" data-bs-toggle="modal" data-bs-target="#addRechargeModal">
                        <i class="fa-solid fa-plus text-warning me-1"></i> Recharge Wallet
                    </button>
                    <button type="button" id="tabActionCourierBtn" class="btn btn-warning rounded-pill px-3 px-md-4 py-2 fw-bold text-dark btn-sm shadow-sm {{ $activeTab === 'courier' ? '' : 'd-none' }}" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" data-bs-toggle="modal" data-bs-target="#addCourierModal">
                        <i class="fa-solid fa-paper-plane me-1"></i> Record Courier Usage
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="contractualTabContent">

                <!-- TAB 1: WALLET RECHARGES -->
                <div class="tab-pane fade {{ $activeTab === 'wallet' ? 'show active' : '' }}" id="wallet-pane" role="tabpanel">
                    <div class="p-3 p-md-4 bg-light border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-circle-info text-primary me-1"></i> Wallet Recharge History</h6>
                            <small class="text-muted" style="font-size: 0.76rem;">Every recharge is automatically recorded as a Business Expense & impacts Profit & Loss.</small>
                        </div>
                        <div class="fw-bold text-dark small">
                            Filtered Total: <span class="text-success">₹{{ number_format($filteredRecharged, 2) }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase fs-7">
                                <tr>
                                    <th class="ps-3 ps-md-4">Date</th>
                                    <th>Recharge Amount</th>
                                    <th>Expense & P&L Status</th>
                                    <th>Note / Reference</th>
                                    <th class="text-end pe-3 pe-md-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recharges as $recharge)
                                    <tr>
                                        <td class="ps-3 ps-md-4 fw-semibold text-dark">
                                            <i class="fa-regular fa-calendar me-1 text-muted"></i> {{ $recharge->date->format('d-m-Y') }}
                                        </td>   </td>
                                        <td class="fw-bold text-success fs-6">
                                            ₹{{ number_format($recharge->amount, 2) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                                <i class="fa-solid fa-circle-check me-1"></i> Expense Recorded
                                            </span>
                                            @if($recharge->expense)
                                                <small class="text-muted ms-1" style="font-size: 0.7rem;">(#Exp-{{ $recharge->expense->id }})</small>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ $recharge->notes ?: '—' }}
                                        </td>
                                        <td class="text-end pe-3 pe-md-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-outline-dark btn-sm rounded-circle p-1.5" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#editRechargeModal{{ $recharge->id }}" title="Edit Recharge">
                                                    <i class="fa-solid fa-pen-to-square" style="font-size: 0.75rem;"></i>
                                                </button>
                                                <form action="{{ route('admin.contractual-posts.recharge.destroy', $recharge->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ₹{{ number_format($recharge->amount, 2) }} recharge? The linked Expense entry will also be permanently deleted.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-1.5" style="width: 32px; height: 32px;" title="Delete Recharge">
                                                        <i class="fa-solid fa-trash-can" style="font-size: 0.75rem;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Recharge Modal -->
                                    <div class="modal fade" id="editRechargeModal{{ $recharge->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                                                    <h5 class="modal-title font-serif fw-bold fs-6">
                                                        <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Wallet Recharge
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.contractual-posts.recharge.update', $recharge->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-dark small">Recharge Date <span class="text-muted extra-small font-monospace">(dd-mm-yyyy)</span> <span class="text-danger">*</span></label>
                                                            <input type="text" name="date" class="form-control rounded-3 flatpickr-date bg-white" value="{{ $recharge->date->format('d-m-Y') }}" placeholder="DD-MM-YYYY" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-dark small">Recharge Amount (₹) <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control rounded-3" value="{{ $recharge->amount }}" required>
                                                            <small class="text-muted extra-small">Updating this will automatically sync the corresponding Expense record.</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-dark small">Note / Reference (Optional)</label>
                                                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="Reference number or note...">{{ $recharge->notes }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 px-md-4 py-3">
                                                        <div class="d-flex w-100 gap-2">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill w-50 py-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-warning rounded-pill w-50 py-2 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                                                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-wallet text-secondary fs-2 mb-2 d-block opacity-50"></i>
                                            No wallet recharge records found. Click <strong>"Recharge Wallet"</strong> to add one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top">
                        {{ $recharges->appends(request()->except('recharge_page'))->links() }}
                    </div>
                </div>

                <!-- TAB 2: CONTRACTUAL COURIERS -->
                <div class="tab-pane fade {{ $activeTab === 'courier' ? 'show active' : '' }}" id="courier-pane" role="tabpanel">
                    <div class="p-3 p-md-4 bg-light border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-circle-info text-warning me-1"></i> Contractual Courier Usage History</h6>
                            <small class="text-muted" style="font-size: 0.76rem;">Deducts from wallet balance ONLY. Does <strong>NOT</strong> create an Expense or affect P&L (to prevent double counting).</small>
                        </div>
                        <div class="fw-bold text-dark small">
                            Filtered Usage: <span class="text-danger">₹{{ number_format($filteredUsage, 2) }}</span> ({{ number_format($filteredCouriers) }} Packets)
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase fs-7">
                                <tr>
                                    <th class="ps-3 ps-md-4">Date</th>
                                    <th>No. of Packets</th>
                                    <th>Total Courier Charge</th>
                                    <th>Average Charge per Packet</th>
                                    <th>Note / Reference</th>
                                    <th class="text-end pe-3 pe-md-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($couriers as $courier)
                                    <tr>
                                        <td class="ps-3 ps-md-4 fw-semibold text-dark">
                                            <i class="fa-regular fa-calendar me-1 text-muted"></i> {{ $courier->date->format('d-m-Y') }}
                                        </td>
                                        <td class="fw-bold text-dark">
                                            <span class="badge bg-dark rounded-pill px-2.5 py-1">{{ $courier->courier_count }} Packets</span>
                                        </td>
                                        <td class="fw-bold text-danger fs-6">
                                            ₹{{ number_format($courier->total_amount, 2) }}
                                        </td>
                                        <td class="fw-bold text-primary">
                                            ₹{{ number_format($courier->average_price, 2) }} <span class="small fw-normal text-muted">/ packet</span>
                                        </td>
                                        <td class="text-muted small">
                                            {{ $courier->notes ?: '—' }}
                                        </td>
                                        <td class="text-end pe-3 pe-md-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-outline-dark btn-sm rounded-circle p-1.5" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#editCourierModal{{ $courier->id }}" title="Edit Courier Record">
                                                    <i class="fa-solid fa-pen-to-square" style="font-size: 0.75rem;"></i>
                                                </button>
                                                <form action="{{ route('admin.contractual-posts.courier.destroy', $courier->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this courier usage record? ₹{{ number_format($courier->total_amount, 2) }} will be returned to your wallet balance.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-1.5" style="width: 32px; height: 32px;" title="Delete Courier Record">
                                                        <i class="fa-solid fa-trash-can" style="font-size: 0.75rem;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Courier Modal -->
                                    <div class="modal fade" id="editCourierModal{{ $courier->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                                                    <h5 class="modal-title font-serif fw-bold fs-6">
                                                        <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Contractual Courier Usage
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.contractual-posts.courier.update', $courier->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-dark small">Date <span class="text-muted extra-small font-monospace">(dd-mm-yyyy)</span> <span class="text-danger">*</span></label>
                                                            <input type="text" name="date" class="form-control rounded-3 flatpickr-date bg-white" value="{{ $courier->date->format('d-m-Y') }}" placeholder="DD-MM-YYYY" required>
                                                        </div>
                                                        <div class="row g-3 mb-3 align-items-end">
                                                            <div class="col-6">
                                                                <label class="form-label fw-semibold text-dark small mb-1">No. of Packets <span class="text-danger">*</span></label>
                                                                <input type="number" min="1" name="courier_count" id="edit_courier_count_{{ $courier->id }}" class="form-control rounded-3 courier-calc-input" data-target="edit_avg_{{ $courier->id }}" data-amount-id="edit_total_amount_{{ $courier->id }}" value="{{ $courier->courier_count }}" required>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label fw-semibold text-dark small mb-1">Total Charge (₹) <span class="text-danger">*</span></label>
                                                                <input type="number" step="0.01" min="0.01" name="total_amount" id="edit_total_amount_{{ $courier->id }}" class="form-control rounded-3 courier-calc-input" data-target="edit_avg_{{ $courier->id }}" data-count-id="edit_courier_count_{{ $courier->id }}" value="{{ $courier->total_amount }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="p-2.5 bg-light rounded-3 border mb-3 text-center">
                                                            <span class="small text-muted">Auto Calculated Average Charge:</span>
                                                            <div class="fw-bold text-primary fs-6" id="edit_avg_{{ $courier->id }}">₹{{ number_format($courier->average_price, 2) }} / packet</div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-dark small">Note / Reference (Optional)</label>
                                                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="Batch details or notes...">{{ $courier->notes }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 px-md-4 py-3">
                                                        <div class="d-flex w-100 gap-2">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill w-50 py-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-warning rounded-pill w-50 py-2 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                                                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-truck-fast text-secondary fs-2 mb-2 d-block opacity-50"></i>
                                            No contractual courier usage records found. Click <strong>"Add Contractual Courier"</strong> to add one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top">
                        {{ $couriers->appends(request()->except('courier_page'))->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- Add Wallet Recharge Modal -->
<div class="modal fade" id="addRechargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold fs-6">
                    <i class="fa-solid fa-wallet text-warning me-2"></i> Recharge India Post Wallet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.contractual-posts.recharge.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Recharge Date <span class="text-muted extra-small font-monospace">(dd-mm-yyyy)</span> <span class="text-danger">*</span></label>
                        <input type="text" name="date" class="form-control rounded-3 flatpickr-date bg-white" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Recharge Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control rounded-3" placeholder="e.g. 1000.00" required>
                        <small class="text-muted extra-small"><i class="fa-solid fa-circle-check text-success me-1"></i> Will automatically record a business Expense & sync with P&L.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Note / Reference (Optional)</label>
                        <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="e.g. UTR Number, Cash receipt or reference..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 px-md-4 py-3">
                    <div class="d-flex w-100 gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill w-50 py-1.5 px-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning rounded-pill w-50 py-1.5 px-2 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Recharge
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Contractual Courier Modal -->
<div class="modal fade" id="addCourierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold fs-6">
                    <i class="fa-solid fa-truck-fast text-warning me-2"></i> Record Contractual Courier Usage
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.contractual-posts.courier.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 bg-warning-subtle rounded-3 border border-warning mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-dark"><i class="fa-solid fa-wallet text-dark me-1"></i> Available Wallet Balance:</span>
                            <span class="fw-bold text-dark fs-6">₹{{ number_format($currentWalletBalance, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Date <span class="text-muted extra-small font-monospace">(dd-mm-yyyy)</span> <span class="text-danger">*</span></label>
                        <input type="text" name="date" class="form-control rounded-3 flatpickr-date bg-white" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" required>
                    </div>

                    <div class="row g-3 mb-3 align-items-end">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">No. of Packets <span class="text-danger">*</span></label>
                            <input type="number" min="1" name="courier_count" id="add_courier_count" class="form-control rounded-3" placeholder="e.g. 5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Total Charge (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="total_amount" id="add_total_amount" class="form-control rounded-3" placeholder="e.g. 500.00" required>
                        </div>
                    </div>

                    <div class="p-2.5 bg-light rounded-3 border mb-3 text-center">
                        <span class="small text-muted">Auto Calculated Average Charge:</span>
                        <div class="fw-bold text-primary fs-6" id="add_avg_preview">₹0.00 / packet</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Note / Reference (Optional)</label>
                        <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="Batch reference or dispatch notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 px-md-4 py-3">
                    <div class="d-flex w-100 gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill w-50 py-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning rounded-pill w-50 py-2 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Usage
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Initialize Flatpickr Date Picker forcing dd-mm-yyyy format across all devices
    function initFlatpickr(scope = document) {
        scope.querySelectorAll('.flatpickr-date').forEach(el => {
            if (!el._flatpickr) {
                flatpickr(el, {
                    dateFormat: "d-m-Y",
                    allowInput: true
                });
            }
        });
    }

    initFlatpickr();

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            initFlatpickr(this);
        });
        modal.addEventListener('show.bs.modal', function() {
            initFlatpickr(this);
        });
    });

    // Tab persistence & action button toggling
    const tabButtons = document.querySelectorAll('#contractualTab button[data-bs-toggle="tab"]');
    const tabActionRechargeBtn = document.getElementById('tabActionRechargeBtn');
    const tabActionCourierBtn = document.getElementById('tabActionCourierBtn');

    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(e) {
            const targetId = e.target.getAttribute('id');
            const tabName = targetId === 'courier-tab' ? 'courier' : 'wallet';

            if (tabName === 'courier') {
                tabActionRechargeBtn?.classList.add('d-none');
                tabActionCourierBtn?.classList.remove('d-none');
            } else {
                tabActionRechargeBtn?.classList.remove('d-none');
                tabActionCourierBtn?.classList.add('d-none');
            }

            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.history.replaceState(null, '', url.toString());
        });
    });

    // Auto Average Price Calculator for Add Modal
    const addCount = document.getElementById('add_courier_count');
    const addAmount = document.getElementById('add_total_amount');
    const addAvg = document.getElementById('add_avg_preview');

    function calculateAddAvg() {
        const count = parseFloat(addCount?.value) || 0;
        const amount = parseFloat(addAmount?.value) || 0;
        if (count > 0 && amount > 0) {
            const avg = (amount / count).toFixed(2);
            if (addAvg) addAvg.textContent = `₹${avg} / packet`;
        } else {
            if (addAvg) addAvg.textContent = '₹0.00 / packet';
        }
    }

    addCount?.addEventListener('input', calculateAddAvg);
    addAmount?.addEventListener('input', calculateAddAvg);

    // Auto Average Price Calculator for Edit Modals
    document.querySelectorAll('.courier-calc-input').forEach(input => {
        input.addEventListener('input', function() {
            const targetId = this.getAttribute('data-target');
            const countId = this.getAttribute('data-count-id') || this.id;
            const amountId = this.getAttribute('data-amount-id') || this.id;

            const countEl = document.getElementById(countId);
            const amountEl = document.getElementById(amountId);
            const targetEl = document.getElementById(targetId);

            const count = parseFloat(countEl?.value) || 0;
            const amount = parseFloat(amountEl?.value) || 0;

            if (count > 0 && amount > 0 && targetEl) {
                const avg = (amount / count).toFixed(2);
                targetEl.textContent = `₹${avg} / packet`;
            } else if (targetEl) {
                targetEl.textContent = '₹0.00 / packet';
            }
        });
    });
});
</script>
@endsection
