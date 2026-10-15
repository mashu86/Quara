@extends('layouts.admin')

@section('title', 'Bulk Mini Labels')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-1">Bulk Mini Labels</h2>
            <div class="text-muted small">Find paid orders by date, select recipients, and print one address label per page.</div>
        </div>
        <span class="badge rounded-pill text-bg-dark px-3 py-2"><i class="fa-solid fa-receipt me-1"></i> 25 mm label</span>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bulk-labels.index') }}" class="row g-3 align-items-end">
                <div class="col-sm-5 col-md-3"><label class="form-label fw-semibold" for="start_date">Start date</label><input class="form-control" id="start_date" name="start_date" type="date" value="{{ $startDate }}" required></div>
                <div class="col-sm-5 col-md-3"><label class="form-label fw-semibold" for="end_date">End date</label><input class="form-control" id="end_date" name="end_date" type="date" value="{{ $endDate }}" required></div>
                <div class="col-sm-2"><button class="btn btn-primary fw-bold px-4"><i class="fa-solid fa-display me-1"></i> Display</button></div>
            </form>
        </div>
    </div>

    @if($startDate && $endDate)
        <form id="labelForm" method="POST" action="{{ route('admin.bulk-labels.print') }}" target="_blank">
            @csrf
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <input type="hidden" name="layout" value="vertical">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="fw-bold">{{ $orders->count() }} paid order(s) found <span class="text-muted fw-normal small">({{ $startDate }} to {{ $endDate }})</span></div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @if($orders->isNotEmpty())
                            <input type="search" id="customerNameSearch" class="form-control form-control-sm" style="width:min(260px, 70vw)" placeholder="Search customer name..." aria-label="Search customer name">
                        @endif
                        <button class="btn btn-warning fw-bold" type="submit" {{ $orders->isEmpty() ? 'disabled' : '' }}><i class="fa-solid fa-print me-1"></i> Prepare selected labels</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-3"><input type="checkbox" class="form-check-input" id="selectAll" aria-label="Select all orders" {{ $orders->isNotEmpty() ? 'checked' : '' }}></th><th>Order</th><th>Date</th><th>Customer</th><th>Phone</th><th>Delivery address</th><th class="text-end pe-3">Amount</th></tr></thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ps-3"><input class="form-check-input order-check" type="checkbox" name="order_ids[]" value="{{ $order->id }}" aria-label="Select {{ $order->order_number }}" checked></td>
                                <td class="fw-bold text-nowrap">{{ $order->order_number }}</td>
                                <td class="text-nowrap">{{ $order->effective_date?->format('d M Y') }}</td>
                                <td class="customer-name-cell">{{ $order->customer_name }}</td>
                                <td class="text-nowrap">{{ $order->customer_phone }}</td>
                                <td class="small" style="min-width:240px">{{ collect([$order->house_building, $order->street, $order->area, $order->city, $order->district, $order->state, $order->pin_code])->filter()->implode(', ') }}</td>
                                <td class="text-end pe-3">₹{{ number_format((float)$order->grand_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No paid, non-cancelled orders in this date range.</td></tr>
                        @endforelse
                        @if($orders->isNotEmpty())<tr id="noSearchMatches" hidden><td colspan="7" class="text-center text-muted py-4">No customer names match your search.</td></tr>@endif
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@section('scripts')
<script>
(() => {
    const all = document.getElementById('selectAll');
    const checks = [...document.querySelectorAll('.order-check')];
    const rows = [...document.querySelectorAll('tbody tr')].filter(row => row.querySelector('.order-check'));
    const visibleChecks = () => rows.filter(row => !row.hidden).map(row => row.querySelector('.order-check'));
    const syncAll = () => { const visible = visibleChecks(); if (all) all.checked = visible.length > 0 && visible.every(c => c.checked); };
    all?.addEventListener('change', () => visibleChecks().forEach(c => c.checked = all.checked));
    checks.forEach(c => c.addEventListener('change', syncAll));
    document.getElementById('customerNameSearch')?.addEventListener('input', event => {
        const query = event.target.value.trim().toLocaleLowerCase();
        let matches = 0;
        rows.forEach(row => {
            const name = row.querySelector('.customer-name-cell')?.textContent.trim().toLocaleLowerCase() || '';
            row.hidden = !name.includes(query);
            if (!row.hidden) matches++;
        });
        const emptyState = document.getElementById('noSearchMatches');
        if (emptyState) emptyState.hidden = matches > 0;
        syncAll();
    });
    document.getElementById('labelForm')?.addEventListener('submit', event => {
        if (!checks.some(c => c.checked)) { event.preventDefault(); alert('Select at least one order.'); return; }
    });
})();
</script>
@endsection
