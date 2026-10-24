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
                <div class="card-body bg-light">
                    @if($orders->isEmpty())
                        <div class="text-center text-muted py-5">No paid, non-cancelled orders in this date range.</div>
                    @else
                        <div class="d-flex align-items-center gap-2 mb-3 px-1">
                            <input type="checkbox" class="form-check-input m-0" id="selectAll" aria-label="Select all orders" checked>
                            <label for="selectAll" class="small fw-semibold mb-0">Select all orders</label>
                        </div>
                        <div class="row g-3" id="orderCards">
                            @foreach($orders as $order)
                                @php($deliveryAddress = collect([$order->house_building, $order->street, $order->area, $order->city, $order->district, $order->state, $order->pin_code])->filter()->implode(', '))
                                <div class="col-12 col-lg-6 order-row">
                                    <article class="card h-100 shadow-sm border-0">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start gap-3">
                                                <input class="form-check-input order-check mt-1" type="checkbox" name="order_ids[]" value="{{ $order->id }}" aria-label="Select {{ $order->order_number }}" checked>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                                        <div>
                                                            <div class="customer-name-cell fw-bold fs-6">{{ $order->customer_name }}</div>
                                                            <div class="small text-muted">{{ $order->order_number }} · {{ $order->effective_date?->format('d M Y') }}</div>
                                                        </div>
                                                        <span class="badge text-bg-success">&#8377;{{ number_format((float)$order->grand_total, 2) }}</span>
                                                    </div>
                                                    <div class="small mb-1"><i class="fa-solid fa-phone me-2 text-muted"></i>{{ $order->customer_phone }}</div>
                                                    <div class="small text-break"><i class="fa-solid fa-location-dot me-2 text-muted"></i>{{ $deliveryAddress }}</div>
                                                    @if($order->items->isNotEmpty())
                                                        <div class="bulk-product-images d-inline-flex align-items-center mt-3" style="padding-left:10px">
                                                            @foreach($order->items as $itemIndex => $item)
                                                                @php($productImage = $item->product?->primary_image_url ?? \App\Models\Setting::logoUrl())
                                                                <button type="button" class="btn p-0 border-0 bg-transparent bulk-product-image {{ $itemIndex > 2 ? 'd-none' : '' }}" data-image="{{ $productImage }}" data-product-name="{{ $item->product_name ?: ($item->product?->name ?? 'Order product') }}" title="Click to preview: {{ $item->product_name ?: ($item->product?->name ?? 'product image') }}" style="margin-left:{{ $itemIndex > 0 ? '-14px' : '0' }};z-index:{{ 10 - $itemIndex }}">
                                                                    <img src="{{ $productImage }}" alt="{{ $item->product_name ?: ($item->product?->name ?? 'Order product') }}" class="rounded-2 border shadow-sm" style="width:38px;height:38px;object-fit:cover;border:2px solid #fff!important;cursor:pointer;transition:transform .15s ease" onmouseover="this.style.transform='scale(1.15) translateY(-2px)';this.parentElement.style.zIndex='20'" onmouseout="this.style.transform='scale(1) translateY(0)';this.parentElement.style.zIndex='{{ 10 - $itemIndex }}'">
                                                                </button>
                                                            @endforeach
                                                            @if($order->items->count() > 3)
                                                                <button type="button" class="btn badge bg-dark text-warning rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm bulk-product-more" data-index="3" style="width:24px;height:24px;font-size:.65rem;margin-left:-10px;z-index:15" title="View all {{ $order->items->count() }} products">+{{ $order->items->count() - 3 }}</button>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    <div class="text-end mt-3">
                                                        <button type="button" class="btn btn-sm btn-outline-dark single-label-action" data-order-id="{{ $order->id }}" title="Print or download this order label">
                                                            <i class="fa-solid fa-print me-1"></i> Print / Download
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                        <div id="noSearchMatches" class="text-center text-muted py-4" hidden>No customer names match your search.</div>
                    @endif
                </div>
            </div>
        </form>
        <div class="modal fade" id="bulkProductImageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6" id="bulkProductImageTitle">Product image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-3">
                        <div id="bulkProductImageCount" class="small text-muted mb-2"></div>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button type="button" class="btn btn-dark rounded-circle" id="bulkImagePrevious" aria-label="Previous product"><i class="fa-solid fa-chevron-left"></i></button>
                            <img id="bulkProductImagePreview" src="" alt="" class="img-fluid rounded" style="max-height:70vh;max-width:80%;object-fit:contain">
                            <button type="button" class="btn btn-dark rounded-circle" id="bulkImageNext" aria-label="Next product"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
(() => {
    const all = document.getElementById('selectAll');
    const checks = [...document.querySelectorAll('.order-check')];
    const rows = [...document.querySelectorAll('.order-row')];
    const visibleChecks = () => rows.filter(row => !row.hidden).map(row => row.querySelector('.order-check'));
    const syncAll = () => { const visible = visibleChecks(); if (all) all.checked = visible.length > 0 && visible.every(c => c.checked); };
    all?.addEventListener('change', () => visibleChecks().forEach(c => c.checked = all.checked));
    checks.forEach(c => c.addEventListener('change', syncAll));
    let imageItems = [];
    let imageIndex = 0;
    const showBulkImage = () => {
        if (!imageItems.length) return;
        imageIndex = (imageIndex + imageItems.length) % imageItems.length;
        const item = imageItems[imageIndex];
        document.getElementById('bulkProductImageTitle').textContent = item.dataset.productName;
        document.getElementById('bulkProductImageCount').textContent = `Item ${imageIndex + 1} of ${imageItems.length}`;
        const image = document.getElementById('bulkProductImagePreview');
        image.src = item.dataset.image;
        image.alt = item.dataset.productName;
    };
    document.querySelectorAll('.bulk-product-image').forEach(button => button.addEventListener('click', () => {
        imageItems = [...button.closest('.bulk-product-images').querySelectorAll('.bulk-product-image')];
        imageIndex = imageItems.indexOf(button);
        showBulkImage();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('bulkProductImageModal')).show();
    }));
    document.querySelectorAll('.bulk-product-more').forEach(button => button.addEventListener('click', () => {
        const buttons = button.closest('.bulk-product-images').querySelectorAll('.bulk-product-image');
        imageItems = [...buttons];
        imageIndex = Number(button.dataset.index) || 0;
        showBulkImage();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('bulkProductImageModal')).show();
    }));
    document.getElementById('bulkImagePrevious')?.addEventListener('click', () => { imageIndex--; showBulkImage(); });
    document.getElementById('bulkImageNext')?.addEventListener('click', () => { imageIndex++; showBulkImage(); });
    document.querySelectorAll('.single-label-action').forEach(button => button.addEventListener('click', () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = document.getElementById('labelForm').action;
        form.target = '_blank';
        const values = {
            _token: document.querySelector('#labelForm input[name="_token"]').value,
            start_date: document.querySelector('#labelForm input[name="start_date"]').value,
            end_date: document.querySelector('#labelForm input[name="end_date"]').value,
            layout: document.querySelector('#labelForm input[name="layout"]').value,
            'order_ids[]': button.dataset.orderId,
        };
        Object.entries(values).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
        form.remove();
    }));
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
