@extends('layouts.admin')

@section('title', 'Order Operations & Returns - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 768px) {
        .op-title { font-size: 1.15rem !important; }
        .op-subtitle { font-size: 0.72rem !important; }
        .op-table th, .op-table td { font-size: 0.73rem !important; padding: 0.5rem 0.35rem !important; }
        .op-table .btn { font-size: 0.68rem !important; padding: 0.25rem 0.45rem !important; }
        .op-table .badge { font-size: 0.63rem !important; padding: 0.2em 0.45em !important; }
        .op-prod-img { width: 32px !important; height: 40px !important; }
    }
</style>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 op-title">Order Operations & Returns</h3>
        <p class="text-muted small mb-0 op-subtitle">Record product returns, damaged items, customer refunds, and post-order financial adjustments.</p>
    </div>
</div>

@php
    $activeFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('has_operation') ? 1 : 0)
        + (request()->filled('operation_status') ? 1 : 0);
@endphp

<!-- Mobile Filter Button Bar (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#orderOpsFilterModal">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-semibold small">Filter Operations</span>
            @if($activeFilterCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $activeFilterCount }}</span>
            @endif
        </button>
        @if($activeFilterCount > 0)
            <a href="{{ route('admin.order-operations.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Search & Filters (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body p-3 p-sm-4">
        <form action="{{ route('admin.order-operations.index') }}" method="GET" class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold text-dark">Search Order / Customer / Product</label>
                <input type="text" name="search" class="form-control rounded-3" placeholder="Order #, Customer Name, Phone..." value="{{ $search }}">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-dark">Operation Filter</label>
                <select name="has_operation" class="form-select rounded-3">
                    <option value="">All Orders</option>
                    <option value="with_ops" {{ $hasOperationFilter === 'with_ops' ? 'selected' : '' }}>Orders with Operations</option>
                    <option value="without_ops" {{ $hasOperationFilter === 'without_ops' ? 'selected' : '' }}>Orders without Operations</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-dark">Operation Status</label>
                <select name="operation_status" class="form-select rounded-3">
                    <option value="">All Statuses (Active & Inactive)</option>
                    <option value="active" {{ $operationStatusFilter === 'active' ? 'selected' : '' }}>ACTIVE Only</option>
                    <option value="inactive" {{ $operationStatusFilter === 'inactive' ? 'selected' : '' }}>INACTIVE Only</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-dark w-100 rounded-pill fw-semibold py-2 shadow-sm">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Mobile Filter Modal (d-lg-none) -->
<div class="modal fade d-lg-none" id="orderOpsFilterModal" tabindex="-1" aria-labelledby="orderOpsFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold fs-6" id="orderOpsFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Operations
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.order-operations.index') }}" method="GET">
                <div class="modal-body p-3.5">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Search Order # / Customer / Product</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Order #, Name, Phone..." value="{{ $search }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Operation Filter</label>
                        <select name="has_operation" class="form-select rounded-3">
                            <option value="">All Orders</option>
                            <option value="with_ops" {{ $hasOperationFilter === 'with_ops' ? 'selected' : '' }}>Orders with Operations</option>
                            <option value="without_ops" {{ $hasOperationFilter === 'without_ops' ? 'selected' : '' }}>Orders without Operations</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Operation Status</label>
                        <select name="operation_status" class="form-select rounded-3">
                            <option value="">All Statuses (Active & Inactive)</option>
                            <option value="active" {{ $operationStatusFilter === 'active' ? 'selected' : '' }}>ACTIVE Only</option>
                            <option value="inactive" {{ $operationStatusFilter === 'inactive' ? 'selected' : '' }}>INACTIVE Only</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 py-2.5">
                    <a href="{{ route('admin.order-operations.index') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">Reset</a>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark btn-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Orders & Operations Table -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table op-table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Order ID</th>
                        <th>Customer</th>
                        <th>Product & Photo</th>
                        <th>Qty</th>
                        <th>Order Amount</th>
                        <th>Order Status</th>
                        <th>Operations Recorded</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $firstItem = $order->items->first();
                            $prod = $firstItem ? $firstItem->product : null;
                            $imageUrl = $prod ? $prod->primary_image_url : \App\Models\Setting::logoUrl();
                            $prodName = $firstItem ? $firstItem->product_name : 'N/A';
                            $sizeName = $firstItem ? $firstItem->size : '';
                            $opsCount = $order->operations->count();
                            $activeOpsCount = $order->operations->where('status', 'active')->count();
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-warning text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                                <div class="small text-muted" style="font-size: 0.7rem;">{{ $order->created_at->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
                                <div class="small text-muted" style="font-size: 0.7rem;"><i class="fa-solid fa-phone me-1"></i>{{ $order->customer_phone }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $imageUrl }}" alt="{{ $prodName }}" 
                                         class="rounded border shadow-sm flex-shrink-0 op-prod-img" 
                                         style="width: 36px; height: 44px; object-fit: cover; cursor: pointer;" 
                                         onclick="openImagePreviewModal('{{ addslashes($imageUrl) }}', '{{ addslashes($prodName) }}')" 
                                         title="Click to view image">
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 160px;">{{ $prodName }}</div>
                                        @if($sizeName)
                                            <span class="badge bg-secondary" style="font-size: 0.63rem;">Size: {{ $sizeName }}</span>
                                        @endif
                                        @if($order->items->count() > 1)
                                            <span class="badge bg-light text-dark border" style="font-size: 0.63rem;">+{{ $order->items->count() - 1 }} item(s)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="fw-semibold">{{ $firstItem ? $firstItem->quantity : 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">₹{{ number_format($order->grand_total, 2) }}</div>
                                <div class="small text-muted" style="font-size: 0.68rem;">Delivery: ₹{{ number_format($order->shipping, 2) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }} text-capitalize">
                                    {{ $order->order_status }}
                                </span>
                            </td>
                            <td>
                                @if($opsCount > 0)
                                    <div>
                                        @foreach($order->operations as $op)
                                            <div class="mb-1 text-nowrap">
                                                <span class="badge bg-{{ $op->status === 'active' ? 'success' : 'secondary' }} text-uppercase" style="font-size: 0.62rem;">
                                                    {{ $op->status }}
                                                </span>
                                                <span class="fw-semibold ms-1" style="font-size: 0.72rem;">{{ $op->operation_type_label }}</span>
                                                @if($op->total_financial_adjustment > 0 && $op->status === 'active')
                                                    <span class="text-danger fw-bold ms-1" style="font-size: 0.7rem;">(-₹{{ number_format($op->total_financial_adjustment, 2) }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">No Operations</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                    <a href="{{ route('admin.order-operations.create', $order->id) }}" 
                                       class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-2.5 py-1 shadow-sm d-inline-flex align-items-center gap-1" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Record Operation for this Order">
                                        <i class="fa-solid fa-rotate-left"></i> <span class="d-none d-sm-inline">Operation</span>
                                    </a>
                                    @if($opsCount === 1)
                                        @php $singleOp = $order->operations->first(); @endphp
                                        <a href="{{ route('admin.order-operations.show', $singleOp->id) }}" 
                                           class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px;" title="View Operation Details">
                                            <i class="fa-solid fa-eye" style="font-size: 0.7rem;"></i>
                                        </a>
                                        <a href="{{ route('admin.order-operations.edit', $singleOp->id) }}" 
                                           class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px;" title="Edit Operation">
                                            <i class="fa-solid fa-pen-to-square" style="font-size: 0.7rem;"></i>
                                        </a>
                                    @elseif($opsCount > 1)
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-outline-dark dropdown-toggle rounded-pill px-2.5 py-1 fw-bold shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.72rem;">
                                                Ops ({{ $opsCount }})
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2" style="min-width: 220px; font-size: 0.78rem;">
                                                <li class="dropdown-header text-uppercase fw-bold text-dark border-bottom pb-1 mb-1" style="font-size: 0.68rem;">Recorded Operations</li>
                                                @foreach($order->operations as $op)
                                                    <li class="mb-1">
                                                        <div class="d-flex justify-content-between align-items-center p-1.5 rounded bg-light">
                                                            <div class="pe-2 text-truncate" style="max-width: 130px;">
                                                                <span class="badge bg-{{ $op->status === 'active' ? 'success' : 'secondary' }}" style="font-size: 0.6rem;">{{ strtoupper($op->status) }}</span>
                                                                <div class="fw-bold text-dark text-truncate" title="{{ $op->operation_type_label }}">{{ $op->operation_type_label }}</div>
                                                            </div>
                                                            <div class="d-flex gap-1">
                                                                <a href="{{ route('admin.order-operations.show', $op->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" title="View"><i class="fa-solid fa-eye" style="font-size: 0.65rem;"></i></a>
                                                                <a href="{{ route('admin.order-operations.edit', $op->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" title="Edit"><i class="fa-solid fa-pen-to-square" style="font-size: 0.65rem;"></i></a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No orders matching criteria found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $orders->links() }}
        </div>
    @endif
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <h5 class="modal-title fs-6 fw-bold text-truncate" id="imagePreviewModalTitle">Product Image Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-light">
                <img id="imagePreviewModalImg" src="" alt="Product Large Image" class="img-fluid rounded-3 border shadow-sm" style="max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openImagePreviewModal(imageUrl, title) {
        document.getElementById('imagePreviewModalImg').src = imageUrl;
        document.getElementById('imagePreviewModalTitle').textContent = title || 'Product Image Preview';
        const modalEl = document.getElementById('imagePreviewModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endsection
