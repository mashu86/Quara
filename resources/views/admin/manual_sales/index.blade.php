@extends('layouts.admin')

@section('title', 'Manual Sales - QUARA WALDROP Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Manual / Offline Sales</h3>
        <p class="text-muted small mb-0">Record offline store purchases, counter sales & direct customer orders.</p>
    </div>
    <a href="{{ route('admin.manual-sales.create') }}" class="btn btn-warning rounded-pill fw-bold px-4">
        <i class="fa-solid fa-plus me-1"></i> Record New Offline Sale
    </a>
</div>

@php
    $activeManualFilterCount = request()->filled('search') ? 1 : 0;
@endphp

<!-- Mobile / Tablet Filter Button Bar (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#manualSalesFilterModal">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-semibold">Search Offline Sales</span>
            @if($activeManualFilterCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $activeManualFilterCount }}</span>
            @endif
        </button>
        @if($activeManualFilterCount > 0)
            <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Filter / Search (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body p-3">
        <form action="{{ route('admin.manual-sales.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="Search by Order #, Customer Name, Phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-pill w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Search</button>
                <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Manual Sales Mobile Filter Modal (d-lg-none) -->
<div class="modal fade d-lg-none" id="manualSalesFilterModal" tabindex="-1" aria-labelledby="manualSalesFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold" id="manualSalesFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Search Offline Sales
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.manual-sales.index') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Search Order # / Customer Name / Phone</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Search by Order #, Customer Name, Phone..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Purchased Item(s)</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($manualOrders as $order)
                        <tr>
                            <td class="fw-bold text-warning">{{ $order->order_number }}</td>
                            <td class="fw-semibold">{{ $order->customer_name }}</td>
                            <td>{{ $order->customer_phone }}</td>
                            <td>
                                @foreach($order->items as $item)
                                    <div><span class="fw-bold">{{ $item->product_name }}</span> (Size: {{ $item->size }}) &times; {{ $item->quantity }}</div>
                                @endforeach
                            </td>
                            <td class="fw-bold text-dark">₹{{ number_format($order->grand_total, 2) }}</td>
                            <td>
                                <span class="badge bg-uppercase bg-{{ $order->payment_method === 'cash' ? 'success' : 'info' }} me-1">{{ $order->payment_method }}</span>
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($order->payment_status) }}</span>
                            </td>
                            <td class="small text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.manual-sales.edit', $order->id) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fs-2 mb-2 d-block text-warning"></i>
                                No manual offline sales recorded yet. Click "Record New Offline Sale" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($manualOrders->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $manualOrders->links() }}
        </div>
    @endif
</div>
@endsection
