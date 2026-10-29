@extends('layouts.admin')
@section('title','Article / Tracking History')
@section('content')
@php
    $filterFields = ['start_date'=>'Start Date','end_date'=>'End Date','client_name'=>'Client Name','order_id'=>'Order ID','article_number'=>'Article Number','tracking_number'=>'Tracking Number','courier_name'=>'Courier','status'=>'Status'];
    $activeFilters = collect($filterFields)->keys()->filter(fn ($key) => filled(request($key)))->count();
    $indiaPostUrl = 'https://www.indiapost.gov.in/';
    $returnTypes = ['product_returned','customer_return','wrong_product_sent','product_damaged','product_lost'];
    // Same item shape as My Sales, so the shared image carousel modal shows the same details.
    $modalItemsFor = function ($s) use ($returnTypes) {
        return ($s->order?->items ?? collect())->map(function ($item) use ($s, $returnTypes) {
            $product = $item->product;
            $itemOps = $s->order?->operations?->where('order_item_id', $item->id)->where('status', 'active') ?? collect();
            $sizeDetails = $item->productSize ?: $product?->sizes?->firstWhere('size', $item->size);
            return [
                'id' => $item->id,
                'product_name' => $item->product_name ?: ($product?->name ?? 'Product Item'),
                'size' => $item->size ?: ($item->productSize?->size ?? 'N/A'),
                'quantity' => $item->quantity ?: 1,
                'unit_price' => $item->unit_price,
                'final_unit_price' => $item->final_unit_price,
                'image_url' => $product?->primary_image_url ?? \App\Models\Setting::logoUrl(),
                'category' => $product?->category?->name,
                'description' => $product?->description,
                'catalog_price' => $product?->price,
                'discount_amount' => $item->discount_amount,
                'subtotal' => $item->subtotal,
                'item_status' => $item->item_status,
                'is_returned' => $item->item_status === 'returned' || $itemOps->contains(fn ($op) => in_array($op->operation_type, $returnTypes, true)),
                'is_combo_offer' => $item->is_combo_offer,
                'measurements' => array_filter(['Chest' => $sizeDetails?->chest, 'Waist' => $sizeDetails?->waist, 'Hip' => $sizeDetails?->hip, 'Length' => $sizeDetails?->length], fn ($value) => filled($value)),
            ];
        })->values();
    };
@endphp
<style>
    .th-thumbs{display:inline-flex;align-items:center}
    .th-thumbs img{width:36px;height:36px;object-fit:cover;border:2px solid #fff!important;cursor:pointer;transition:transform .15s ease;position:relative}
    .th-thumbs img:hover{transform:scale(1.12);z-index:20!important}
    .th-thumbs img.is-returned{border-color:#dc3545!important}
    .th-track{font-family:var(--bs-font-monospace);font-weight:600}
    .th-card{border-radius:.75rem}
    .th-card .th-label{font-size:.68rem;color:#6c757d;text-transform:uppercase;letter-spacing:.02em}
</style>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">Article / Tracking History</h3>
        <button type="button" class="btn btn-outline-dark btn-sm d-md-none position-relative" data-bs-toggle="modal" data-bs-target="#historyFilterModal">
            <i class="fa-solid fa-filter me-1"></i> Filters
            @if($activeFilters)<span class="badge bg-warning text-dark ms-1">{{ $activeFilters }}</span>@endif
        </button>
    </div>

    {{-- Desktop filters --}}
    <form class="card card-body mb-3 row g-2 flex-row d-none d-md-flex">
        @foreach($filterFields as $key => $label)
            <div class="col-md"><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $key }}" value="{{ request($key) }}" @if(str_ends_with($key,'date')) type="date" @endif></div>
        @endforeach
        <div class="col-md-auto align-self-end d-flex gap-1"><button class="btn btn-primary">Filter</button>@if($activeFilters)<a href="{{ route('admin.bulk-article.history') }}" class="btn btn-outline-secondary">Clear</a>@endif</div>
    </form>

    {{-- Mobile filters --}}
    <div class="modal fade" id="historyFilterModal" tabindex="-1" aria-labelledby="historyFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down"><form class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="historyFilterModalLabel">Filters</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><div class="row g-2">
                @foreach($filterFields as $key => $label)
                    <div class="{{ str_ends_with($key,'date') ? 'col-6' : 'col-12' }}"><label class="form-label small mb-1">{{ $label }}</label><input class="form-control" name="{{ $key }}" value="{{ request($key) }}" @if(str_ends_with($key,'date')) type="date" @endif></div>
                @endforeach
            </div></div>
            <div class="modal-footer"><a href="{{ route('admin.bulk-article.history') }}" class="btn btn-outline-secondary">Clear</a><button class="btn btn-primary">Apply Filters</button></div>
        </form></div>
    </div>

    <form id="bulkShipmentDeleteForm" method="post" action="{{ route('admin.bulk-article.bulk-destroy') }}" class="mb-2 d-flex justify-content-between align-items-center" onsubmit="return confirm('Delete all selected article and tracking records?')">
        @csrf @method('DELETE')
        <label class="form-check d-md-none mb-0 small"><input type="checkbox" class="form-check-input" id="selectAllShipmentsMobile"> Select all</label>
        <button id="bulkShipmentDeleteButton" type="submit" class="btn btn-danger btn-sm ms-auto" disabled><i class="fa-solid fa-trash me-1"></i> Delete selected (<span id="selectedShipmentCount">0</span>)</button>
    </form>

    {{-- Desktop table --}}
    <div class="card d-none d-md-block"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th><input type="checkbox" class="form-check-input" id="selectAllShipments" aria-label="Select all shipments"></th><th>Order ID</th><th>Client</th><th>Products</th><th>Article</th><th>Tracking</th><th>Weight</th><th>Charge</th><th>Courier</th><th>Shipment Date</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($shipments as $s)
            @php
                $modalItems = $modalItemsFor($s);
                $trackingNo = $s->tracking_number ?: $s->article_number;
            @endphp
            <tr>
                <td><input type="checkbox" class="form-check-input shipment-select" form="bulkShipmentDeleteForm" name="shipment_ids[]" value="{{ $s->id }}" aria-label="Select shipment for order {{ $s->order?->order_number }}"></td>
                <td class="text-nowrap">
                    @if($s->order)
                        <a href="{{ route('admin.orders.show', $s->order) }}" class="fw-semibold text-decoration-none" title="Open this order in My Sales">{{ $s->order->order_number }} <i class="fa-solid fa-arrow-up-right-from-square small"></i></a>
                    @else — @endif
                </td>
                <td>{{ $s->receiver_name }}@if($s->order?->customer_phone)<div class="small text-muted text-nowrap"><i class="fa-solid fa-phone me-1"></i>{{ $s->order->customer_phone }}</div>@endif @if($s->order)<div class="small text-muted text-nowrap">Order Date: {{ optional($s->order->sale_date ?? $s->order->created_at)->format('d-m-Y') }}</div>@endif</td>
                <td>@include('admin.bulk_article.partials.history_thumbs', ['modalItems' => $modalItems])</td>
                <td>{{ $s->article_number }}</td>
                <td>@include('admin.bulk_article.partials.history_tracking', ['trackingNo' => $trackingNo, 'indiaPostUrl' => $indiaPostUrl])</td>
                <td class="text-nowrap">{{ $s->weight }} {{ $s->weight_unit }}</td><td>₹{{ $s->courier_charge }}</td><td>{{ $s->courier_name }}</td><td class="text-nowrap">{{ $s->shipment_date?->format('d-m-Y') }}</td><td>{{ $s->shipment_status }}</td>
                <td><div class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.bulk-article.edit',$s) }}" title="View / Edit" aria-label="View / Edit"><i class="fa-solid fa-pen-to-square"></i></a><form method="post" action="{{ route('admin.bulk-article.destroy',$s) }}" onsubmit="return confirm('Delete this article and tracking record?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete"><i class="fa-solid fa-trash"></i></button></form></div></td>
            </tr>
        @empty
            <tr><td colspan="12" class="text-center">No shipments found.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>

    {{-- Mobile cards --}}
    <div class="d-md-none">
        @forelse($shipments as $s)
            @php
                $modalItems = $modalItemsFor($s);
                $trackingNo = $s->tracking_number ?: $s->article_number;
            @endphp
            <div class="card th-card shadow-sm mb-2"><div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div class="d-flex align-items-start gap-2">
                        <input type="checkbox" class="form-check-input mt-1 shipment-select" form="bulkShipmentDeleteForm" name="shipment_ids[]" value="{{ $s->id }}" aria-label="Select shipment for order {{ $s->order?->order_number }}">
                        <div>
                            @if($s->order)
                                <a href="{{ route('admin.orders.show', $s->order) }}" class="fw-bold text-decoration-none small">{{ $s->order->order_number }} <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                            @endif
                            <div class="fw-semibold" style="font-size:.85rem">{{ $s->receiver_name }}</div>
                            @if($s->order?->customer_phone)<a href="tel:{{ $s->order->customer_phone }}" class="text-muted text-decoration-none" style="font-size:.75rem"><i class="fa-solid fa-phone text-success me-1"></i>{{ $s->order->customer_phone }}</a>@endif
                            @if($s->order)<div class="text-muted" style="font-size:.75rem">Order Date: {{ optional($s->order->sale_date ?? $s->order->created_at)->format('d-m-Y') }}</div>@endif
                        </div>
                    </div>
                    @include('admin.bulk_article.partials.history_thumbs', ['modalItems' => $modalItems])
                </div>
                <div class="border rounded-3 p-2 mb-2 bg-light">
                    <div class="th-label">Tracking</div>
                    @include('admin.bulk_article.partials.history_tracking', ['trackingNo' => $trackingNo, 'indiaPostUrl' => $indiaPostUrl])
                </div>
                <div class="row g-1 small">
                    <div class="col-4"><div class="th-label">Weight</div>{{ $s->weight }} {{ $s->weight_unit }}</div>
                    <div class="col-4"><div class="th-label">Charge</div>₹{{ $s->courier_charge }}</div>
                    <div class="col-4"><div class="th-label">Shipped</div>{{ $s->shipment_date?->format('d-m-Y') ?: '—' }}</div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="badge bg-secondary">{{ $s->shipment_status }}</span>
                    <div class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.bulk-article.edit',$s) }}" title="View / Edit" aria-label="View / Edit"><i class="fa-solid fa-pen-to-square"></i></a><form method="post" action="{{ route('admin.bulk-article.destroy',$s) }}" onsubmit="return confirm('Delete this article and tracking record?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete"><i class="fa-solid fa-trash"></i></button></form></div>
                </div>
            </div></div>
        @empty
            <div class="card card-body text-center">No shipments found.</div>
        @endforelse
    </div>
    <div class="text-muted small mt-2">Showing {{ $shipments->count() }} shipment record(s).</div>
</div>

@include('admin.orders.partials.image_carousel_modal')
<script>
async function copyTrackingNumber(button, text) {
    let ok = false;
    try { await navigator.clipboard.writeText(text); ok = true; }
    catch (e) { const t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); ok = document.execCommand('copy'); t.remove(); }
    if (!button) return;
    const original = button.innerHTML;
    button.innerHTML = ok ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-xmark"></i>';
    setTimeout(() => { button.innerHTML = original; }, 1500);
}
// Desktop and mobile render the same records; only the visible checkboxes are used for selection.
const shipmentCheckboxes = () => Array.from(document.querySelectorAll('.shipment-select')).filter(c => c.offsetParent !== null);
const selectAllBoxes = ['selectAllShipments', 'selectAllShipmentsMobile'].map(id => document.getElementById(id)).filter(Boolean);
const bulkDeleteButton = document.getElementById('bulkShipmentDeleteButton');
const selectedShipmentCount = document.getElementById('selectedShipmentCount');
function updateShipmentSelection() {
    const boxes = shipmentCheckboxes(), selectedCount = boxes.filter(c => c.checked).length;
    selectedShipmentCount.textContent = selectedCount;
    bulkDeleteButton.disabled = selectedCount === 0;
    selectAllBoxes.forEach(all => { all.checked = boxes.length > 0 && selectedCount === boxes.length; all.indeterminate = selectedCount > 0 && selectedCount < boxes.length; });
}
document.querySelectorAll('.shipment-select').forEach(c => c.addEventListener('change', updateShipmentSelection));
selectAllBoxes.forEach(all => all.addEventListener('change', () => { shipmentCheckboxes().forEach(c => c.checked = all.checked); updateShipmentSelection(); }));
// Hidden duplicates (the other layout) must not be submitted.
document.getElementById('bulkShipmentDeleteForm').addEventListener('submit', () => {
    document.querySelectorAll('.shipment-select').forEach(c => { if (c.offsetParent === null) c.checked = false; });
});
window.addEventListener('resize', updateShipmentSelection);
updateShipmentSelection();
</script>
@endsection
