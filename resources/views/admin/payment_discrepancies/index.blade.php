@extends('layouts.admin')

@section('title', 'Razorpay Payment Reconciliation & Audit - ' . $siteName)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Header Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-scale-balanced text-warning me-2"></i> Razorpay Payment Reconciliation
            </h3>
            <p class="text-muted small mb-0">
                Compare order payment statuses with Razorpay to identify and resolve payment mismatches.
            </p>
        </div>
        <div>
            @if($totalDiscrepancyCount > 0)
                <form action="{{ route('admin.payment-discrepancies.reconcile-all') }}" method="POST" onsubmit="return confirm('Check pending orders against Razorpay and reconcile matching payments?');">
                    @csrf
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm">
                        <i class="fa-solid fa-arrows-rotate me-2"></i> Sync & Fix All Discrepancies
                    </button>
                </form>
            @else
                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 border border-success rounded-pill">
                    <i class="fa-solid fa-circle-check me-1"></i> No Payment Mismatches Found
                </span>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="stat-card border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold">Total Discrepancies</div>
                        <h2 class="fw-bold mb-0 text-dark mt-1">{{ $totalDiscrepancyCount }}</h2>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning rounded-circle p-3">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="stat-card border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold">Paid on Razorpay, Pending Here</div>
                        <h2 class="fw-bold mb-0 text-danger mt-1">{{ $capturedPendingCount }}</h2>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger rounded-circle p-3">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="stat-card border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted extra-small text-uppercase font-monospace fw-bold">Paid without Razorpay ID</div>
                        <h2 class="fw-bold mb-0 text-info mt-1">{{ $paidWithoutIdCount }}</h2>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info rounded-circle p-3">
                        <i class="fa-solid fa-circle-question"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Discrepancy Items Table / Cards -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-dark text-white p-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 font-serif fw-bold text-warning fs-6">
                <i class="fa-solid fa-list-check me-2"></i> Flagged Payment Mismatches
            </h5>
            <span class="badge bg-secondary rounded-pill">{{ count($discrepancies) }} Items</span>
        </div>

        <div class="card-body p-0">
            @if(count($discrepancies) === 0)
                <div class="text-center py-5">
                    <div class="display-6 text-success mb-3"><i class="fa-solid fa-shield-halved"></i></div>
                    <h5 class="fw-bold text-dark mb-1">No Discrepancies Found!</h5>
                    <p class="text-muted small mb-0">No payment mismatches were found in the available records.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Order Number & Date</th>
                                <th>Customer Details</th>
                                <th>Grand Total</th>
                                <th>Current Status</th>
                                <th>Discrepancy Details</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($discrepancies as $item)
                                @php
                                    $ord = $item['order'];
                                    $rzp = $item['rzp_details'];
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">
                                            <a href="{{ route('admin.orders.show', $ord->id) }}" class="text-decoration-none text-primary">
                                                #{{ $ord->order_number }}
                                            </a>
                                        </div>
                                        <div class="text-muted extra-small" style="font-size: 0.72rem;">
                                            {{ $ord->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $ord->customer_name }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-phone me-1"></i> {{ $ord->customer_phone }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">₹{{ number_format($ord->grand_total, 2) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $ord->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1">
                                            Payment: {{ strtoupper($ord->payment_status) }}
                                        </span>
                                        <div class="extra-small text-muted mt-1" style="font-size: 0.70rem;">
                                            Order: {{ ucfirst($ord->order_status) }}
                                        </div>
                                    </td>
                                    <td style="max-width: 320px;">
                                        <div class="alert alert-warning p-2 mb-0 border-0 rounded-3" style="font-size: 0.78rem;">
                                            <div class="fw-bold text-dark mb-0.5">
                                                <i class="fa-solid fa-circle-exclamation text-danger me-1"></i> {{ $item['issue_title'] }}
                                            </div>
                                            <div class="text-secondary" style="font-size: 0.72rem;">
                                                {{ $item['issue_description'] }}
                                            </div>
                                            @if($rzp)
                                                <div class="mt-1 font-monospace text-primary fw-bold" style="font-size: 0.70rem;">
                                                    Razorpay Pay ID: {{ $rzp['id'] }} (₹{{ number_format(($rzp['amount'] ?? 0) / 100, 2) }})
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-3 text-end">
                                        @if($item['type'] === 'paid_without_razorpay_id')
                                            <form action="{{ route('admin.payment-discrepancies.reconcile', $ord->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="force_reset_pending" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" onclick="return confirm('Check this payment and, if no captured payment is found, reset the order to Pending and reserve its stock?');">
                                                    <i class="fa-solid fa-lock-open me-1"></i> Reset & Reserve Stock
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.payment-discrepancies.reconcile', $ord->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                                                    <i class="fa-solid fa-sync me-1"></i> Sync & Fix
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
