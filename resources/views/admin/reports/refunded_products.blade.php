@extends('layouts.admin')

@section('title', 'Product Refund Report - ' . $siteName . ' Admin')

@section('content')
<style>
    .prod-img-container {
        width: 44px;
        height: 52px;
        position: relative;
        cursor: pointer;
        border-radius: 8px;
        overflow: hidden;
        display: inline-block;
        flex-shrink: 0;
    }
    .prod-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .prod-img-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.85;
        transition: all 0.2s ease-in-out;
    }
    .prod-img-container:hover .prod-img-overlay {
        opacity: 1;
        background: rgba(0, 0, 0, 0.65);
    }
    .prod-title-mobile {
        display: block;
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.78rem;
    }

    @media (max-width: 767.98px) {
        .report-header-title { font-size: 1.15rem !important; }
        .report-header-subtitle { font-size: 0.72rem !important; }
        .report-top-btn { font-size: 0.78rem !important; padding: 0.35rem 0.6rem !important; border-radius: 8px !important; }
        .stat-card-title { font-size: 0.72rem !important; }
        .stat-card-val { font-size: 1.15rem !important; }

        /* Mobile Sticky 1st Column (Product Details) */
        .refund-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .refund-table th:first-child,
        .refund-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 5;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.12);
            min-width: 115px;
            max-width: 125px;
        }
        .refund-table th:first-child {
            background-color: #212529 !important;
            color: #ffffff !important;
        }
        .refund-table td:first-child {
            background-color: #ffffff !important;
        }
        .refund-table tr:hover td:first-child {
            background-color: #f8f9fa !important;
        }
        .prod-title-mobile {
            max-width: 100px;
            font-size: 0.74rem;
        }
        .refund-table {
            font-size: 0.78rem !important;
        }
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
        <div class="refund-table-wrapper">
            <table class="table table-hover align-middle mb-0 refund-table">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3 ps-md-4">Product Details</th>
                        <th>Date &amp; Order</th>
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
                            $prodName = $orderItem ? $orderItem->product_name : ($prod ? $prod->name : 'Product');
                            $prodSize = $orderItem ? $orderItem->size : 'N/A';
                        @endphp
                        <tr>
                            <!-- 1st Column: Fixed / Sticky Product Details on Mobile -->
                            <td class="ps-3 ps-md-4">
                                <div class="d-flex flex-column align-items-center align-items-md-start gap-1">
                                    <div class="prod-img-container shadow-sm border" onclick="openImageModal('{{ $imgUrl }}', '{{ e($prodName) }}', '{{ $prodSize }}', '{{ $op->quantity }}')" title="Click to view image design preview">
                                        <img src="{{ $imgUrl }}" alt="Product">
                                        <div class="prod-img-overlay">
                                            <i class="fa-solid fa-eye text-white fs-6"></i>
                                        </div>
                                    </div>
                                    <div class="text-center text-md-start w-100">
                                        <div class="fw-bold text-dark prod-title-mobile" title="{{ $prodName }}">
                                            {{ $prodName }}
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.70rem;">
                                            <span class="badge bg-secondary" style="font-size: 0.62rem;">{{ $prodSize }}</span> | <strong>{{ $op->quantity }}pcs</strong>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 2nd Column: Date & Order -->
                            <td>
                                <div class="fw-bold text-dark small text-nowrap">{{ $op->created_at->format('d-m-Y') }}</div>
                                @if($order)
                                    <a href="{{ route('admin.order-operations.create', $order->id) }}" class="badge bg-dark text-decoration-none text-nowrap" title="View Order Adjustments">
                                        #{{ $order->order_number }}
                                    </a>
                                @endif
                            </td>

                            <!-- 3rd Column: Customer -->
                            <td>
                                @if($order)
                                    <div class="fw-semibold text-dark small text-nowrap">{{ $order->customer_name }}</div>
                                    <div class="small text-muted text-nowrap" style="font-size: 0.72rem;">{{ $order->customer_phone }}</div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>

                            <!-- 4th Column: Stock Condition -->
                            <td class="text-center">
                                @if($op->inventory_condition === 'return_to_stock')
                                    <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1 text-nowrap" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-box-archive me-1"></i> Restocked
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2.5 py-1 text-nowrap" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-snowflake me-1"></i> Frozen
                                    </span>
                                @endif
                            </td>

                            <!-- 5th Column: Original Price -->
                            <td class="text-end fw-semibold text-dark text-nowrap">
                                ₹{{ number_format($orderItem ? $orderItem->subtotal : 0, 2) }}
                            </td>

                            <!-- 6th Column: Refund Given -->
                            <td class="text-end pe-4 text-nowrap">
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

<!-- Image Design Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-3">
                <h6 class="modal-title fw-bold text-white small" id="imagePreviewModalLabel">
                    <i class="fa-solid fa-image me-1 text-warning"></i> Product Design Preview
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-light">
                <img id="imageModalImg" src="" alt="Product Design Preview" class="img-fluid w-100" style="max-height: 380px; object-fit: contain;">
            </div>
            <div class="modal-footer border-top bg-white p-2.5 d-flex justify-content-between align-items-center">
                <div class="text-start pe-2" style="max-width: 200px;">
                    <span id="imageModalName" class="fw-bold text-dark small d-block text-truncate"></span>
                    <span id="imageModalMeta" class="small text-muted d-block" style="font-size: 0.72rem;"></span>
                </div>
                <button type="button" class="btn btn-sm btn-dark rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openImageModal(url, name, size, qty) {
    document.getElementById('imageModalImg').src = url;
    document.getElementById('imageModalName').innerText = name;
    document.getElementById('imageModalMeta').innerText = 'Size: ' + size + ' | Quantity: ' + qty + ' pcs';
    var modalElement = document.getElementById('imagePreviewModal');
    var modal = new bootstrap.Modal(modalElement);
    modal.show();
}
</script>
@endsection
