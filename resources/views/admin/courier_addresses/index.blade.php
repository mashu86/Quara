@extends('layouts.admin')

@section('title', 'Courier Addresses')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-1">Courier Addresses</h2>
            <div class="text-muted small">Addresses for products sold outside the website. Select recipients and print one label per page.</div>
        </div>
        <a href="{{ route('admin.courier-addresses.create') }}" class="btn btn-primary fw-bold"><i class="fa-solid fa-plus me-1"></i> Add Address</a>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.courier-addresses.index') }}" class="row g-3 align-items-end">
                <div class="col-sm-5 col-md-3"><label class="form-label fw-semibold" for="start_date">Start date</label><input class="form-control" id="start_date" name="start_date" type="date" value="{{ $startDate }}" required></div>
                <div class="col-sm-5 col-md-3"><label class="form-label fw-semibold" for="end_date">End date</label><input class="form-control" id="end_date" name="end_date" type="date" value="{{ $endDate }}" required></div>
                <div class="col-sm-2"><button class="btn btn-primary fw-bold px-4"><i class="fa-solid fa-display me-1"></i> Display</button></div>
            </form>
        </div>
    </div>

    <form id="labelForm" method="POST" action="{{ route('admin.courier-addresses.print') }}" target="_blank">
        @csrf
        <input type="hidden" name="layout" value="vertical">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-bold">{{ $addresses->count() }} address(es) found</div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    @if($addresses->isNotEmpty())
                        <input type="search" id="addressSearch" class="form-control form-control-sm" style="width:min(260px, 70vw)" placeholder="Search name or phone..." aria-label="Search name or phone">
                    @endif
                    <button class="btn btn-warning fw-bold" type="submit" {{ $addresses->isEmpty() ? 'disabled' : '' }}><i class="fa-solid fa-print me-1"></i> Print selected labels</button>
                </div>
            </div>
            <div class="card-body bg-light">
                @if($addresses->isEmpty())
                    <div class="text-center text-muted py-5">No addresses in this date range.</div>
                @else
                    <div class="d-flex align-items-center gap-2 mb-3 px-1">
                        <input type="checkbox" class="form-check-input m-0" id="selectAll">
                        <label for="selectAll" class="small fw-semibold mb-0">Select all</label>
                    </div>
                    <div class="row g-3">
                        @foreach($addresses as $address)
                            <div class="col-12 col-lg-6 address-row">
                                <article class="card h-100 shadow-sm border-0">
                                    <div class="card-body p-3 d-flex align-items-start gap-3">
                                        <input class="form-check-input address-check mt-1" type="checkbox" name="address_ids[]" value="{{ $address->id }}" aria-label="Select {{ $address->customer_name }}">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="address-search-text">
                                                <div class="fw-bold fs-6">{{ $address->customer_name }}</div>
                                                <div class="small mb-1"><i class="fa-solid fa-phone me-2 text-muted"></i>{{ $address->customer_phone }}</div>
                                            </div>
                                            <div class="small text-break"><i class="fa-solid fa-location-dot me-2 text-muted"></i>{{ collect([$address->house_building, $address->street, $address->city, $address->district, $address->state, $address->pin_code])->filter()->implode(', ') }}</div>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <span class="small text-muted">{{ $address->created_at->format('d M Y') }}</span>
                                                <span class="d-flex gap-2">
                                                    <a href="{{ route('admin.courier-addresses.edit', $address) }}" class="btn btn-sm btn-outline-dark" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                                    <button type="submit" form="delete-address-{{ $address->id }}" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                    <div id="noSearchMatches" class="text-center text-muted py-4" hidden>No addresses match your search.</div>
                @endif
            </div>
        </div>
    </form>

    @foreach($addresses as $address)
        <form id="delete-address-{{ $address->id }}" method="POST" action="{{ route('admin.courier-addresses.destroy', $address) }}" onsubmit="return confirm('Delete this address?')">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</div>
@endsection

@section('scripts')
<script>
(() => {
    const all = document.getElementById('selectAll');
    const checks = [...document.querySelectorAll('.address-check')];
    const rows = [...document.querySelectorAll('.address-row')];
    const visibleChecks = () => rows.filter(row => !row.hidden).map(row => row.querySelector('.address-check'));
    const syncAll = () => { const visible = visibleChecks(); if (all) all.checked = visible.length > 0 && visible.every(c => c.checked); };
    all?.addEventListener('change', () => visibleChecks().forEach(c => c.checked = all.checked));
    checks.forEach(c => c.addEventListener('change', syncAll));
    document.getElementById('addressSearch')?.addEventListener('input', event => {
        const query = event.target.value.trim().toLocaleLowerCase();
        let matches = 0;
        rows.forEach(row => {
            const text = row.querySelector('.address-search-text').textContent.toLocaleLowerCase();
            row.hidden = !text.includes(query);
            if (row.hidden) row.querySelector('.address-check').checked = false;
            else matches++;
        });
        document.getElementById('noSearchMatches').hidden = matches > 0;
        syncAll();
    });
    document.getElementById('labelForm').addEventListener('submit', event => {
        if (!checks.some(c => c.checked)) { event.preventDefault(); alert('Select at least one address.'); }
    });
})();
</script>
@endsection
