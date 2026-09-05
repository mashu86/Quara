@extends('layouts.admin')

@section('title', 'Product Refund Report - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .report-header-title { font-size: 1.15rem !important; }
        .report-header-subtitle { font-size: 0.72rem !important; }
        .report-top-btn { font-size: 0.78rem !important; padding: 0.35rem 0.6rem !important; border-radius: 8px !important; }
        .stat-card-title { font-size: 0.72rem !important; }
        .stat-card-val { font-size: 1.15rem !important; }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 report-header-title">Product Refund Report</h3>
        <p class="text-muted small mb-0 report-header-subtitle">Itemized breakdown of all returned/refunded products, refund amounts, and stock restock conditions.</p>
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 report-top-btn shadow-sm w-100 w-sm-auto text-center">
            <i class="fa-solid fa-chart-pie me-1"></i> Profit &amp; Loss Statement
        </a>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 report-top-btn shadow-sm w-100 w-sm-auto text-center">
            &larr; Expenses Management
        </a>
    </div>
</div>

<!-- 4 TOP STAT CARDS -->
<div class="row g-3 mb-4">
    <!-- 1. Total Refund Amount -->
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-danger text-white h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-bold text-uppercase opacity-75 stat-card-title">Total Customer Refunds</span>
                <i class="fa-solid fa-hand-holding-dollar fs-4"></i>
            </div>
            <h3 class="fw-bold mb-0 stat-card-val">₹{{ number_format($totalRefundedAmount, 2) }}</h3>
            <div class="small opacity-75 mt-1" style="font-size: 0.7rem;">Deducted from Sales Revenue</div>
        </div>
    </div>

    <!-- 2. Total Refunded Items Count -->
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-dark text-white h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-bold text-uppercase text-warning stat-card-title">Refunded Items</span>
                <i class="fa-solid fa-box-open fs-4 text-warning"></i>
            </div>
            <h3 class="fw-bold mb-0 stat-card-val text-warning">{{ $totalRefundedItemsCount }} <small class="fs-6">pcs</small></h3>
            <div class="small text-white-50 mt-1" style="font-size: 0.7rem;">Total adjustment records</div>
        </div>
    </div>

    <!-- 3. Restocked Items -->
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-success text-white h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-bold text-uppercase opacity-75 stat-card-title">Restocked to Inventory</span>
                <i class="fa-solid fa-box-archive fs-4"></i>
            </div>
            <h3 class="fw-bold mb-0 stat-card-val">{{ $restockedItemsCount }} <small class="fs-6">items</small></h3>
            <div class="small opacity-75 mt-1" style="font-size: 0.7rem;">Stock restored to website</div>
        </div>
    </div>

    <!-- 4. Frozen / Non-Restocked Items -->
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm bg-secondary text-white h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-bold text-uppercase opacity-75 stat-card-title">Frozen / Not Restocked</span>
                <i class="fa-solid fa-snowflake fs-4"></i>
            </div>
            <h3 class="fw-bold mb-0 stat-card-val">{{ $frozenItemsCount }} <small class="fs-6">items</small></h3>
            <div class="small opacity-75 mt-1" style="font-size: 0.7rem;">Kept by customer or non-restocked</div>
        </div>
    </div>
</div>

<!-- FILTERS CARD -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.reports.refunded-products') }}" method="GET" class="row g-2.5 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control rounded-pill px-3" value="{{ $startDate }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control rounded-pill px-3" value="{{ $endDate }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold mb-1">Stock Condition</label>
                <select name="inventory_condition" class="form-select rounded-pill px-3">
                    <option value="all" {{ ($invConditionFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Conditions</option>
                    <option value="return_to_stock" {{ ($invConditionFilter ?? '') === 'return_to_stock' ? 'selected' : '' }}>🟢 Restocked Only</option>
                    <option value="do_not_restock" {{ ($invConditionFilter ?? '') === 'do_not_restock' ? 'selected' : '' }}>🔒 Frozen Only</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-warning rounded-pill w-100 fw-bold shadow-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                    <i class="fa-solid fa-filter me-1"></i> Filter Refunds
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ITEMIZE REFUNDED PRODUCTS TABLE -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-rotate-left text-warning me-2"></i> Itemized Product Refunds List ({{ $refundOperations->total() }})
        </h5>
        <div class="badge bg-danger rounded-pill px-3 py-1.5 fs-6">
            Period Refunds: ₹{{ number_format($totalRefundedAmount, 2) }}
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4" style="width: 130px;">Date &amp; Order</th>
                        <th>Product Details</th>
                        <th>Customer</th>
                        <th class="text-center">Stock Condition</th>
                        <th class="text-end">Original Price</th>
                        <th class="text-end pe-4">Refund Given</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refundOperations as $op)
                        @php
                            $order = $op->order;
                            $orderItem = $op->orderItem;
                            $prod = $op->product;
                            $imgUrl = $prod ? $prod->primary_image_url : \App\Models\Setting::logoUrl();
                            $refundAmt = (float) ($op->total_refund_amount > 0 ? $op->total_refund_amount : ($orderItem ? $orderItem->refund_amount : 0));
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark small">{{ $op->created_at->format('d-m-Y') }}</div>
                                @if($order)
                                    <a href="{{ route('admin.order-operations.create', $order->id) }}" class="badge bg-dark text-decoration-none" title="View Order Adjustments">
                                        #{{ $order->order_number }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <img src="{{ $imgUrl }}" alt="Product" class="rounded-3 border flex-shrink-0" style="width: 44px; height: 52px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $orderItem ? $orderItem->product_name : ($prod ? $prod->name : 'Product') }}</div>
                                        <div class="small text-muted" style="font-size: 0.74rem;">
                                            Size: <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ $orderItem ? $orderItem->size : 'N/A' }}</span> | Qty: <strong>{{ $op->quantity }} pcs</strong>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($order)
                                    <div class="fw-semibold text-dark small">{{ $order->customer_name }}</div>
                                    <div class="small text-muted" style="font-size: 0.72rem;">{{ $order->customer_phone }}</div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($op->inventory_condition === 'return_to_stock')
                                    <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-box-archive me-1"></i> Restocked
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2.5 py-1" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-snowflake me-1"></i> Frozen
                                    </span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold text-dark">
                                ₹{{ number_format($orderItem ? $orderItem->subtotal : 0, 2) }}
                            </td>
                            <td class="text-end pe-4">
                                @if($refundAmt > 0)
                                    <span class="fw-bold fs-6 text-danger">-₹{{ number_format($refundAmt, 2) }}</span>
                                @else
                                    <span class="badge bg-light text-secondary border">No Refund (₹0.00)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-hand-holding-dollar fa-3x text-secondary mb-3"></i>
                                <h6 class="fw-bold">No Refunded Products Found</h6>
                                <p class="small mb-0">No product refund records match your selected date range or filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($refundOperations->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4">
            {{ $refundOperations->links() }}
        </div>
    @endif
</div>
@endsection
