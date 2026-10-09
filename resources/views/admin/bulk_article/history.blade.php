@extends('layouts.admin')
@section('title','Article / Tracking History')
@section('content')
<div class="container-fluid py-3">
    <h3>Article / Tracking History</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <form class="card card-body mb-3 row g-2 flex-row">
        @foreach(['start_date'=>'Start Date','end_date'=>'End Date','client_name'=>'Client Name','order_id'=>'Order ID','article_number'=>'Article Number','tracking_number'=>'Tracking Number','courier_name'=>'Courier'] as $key=>$label)
            <div class="col-md"><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $key }}" value="{{ request($key) }}" @if(str_ends_with($key,'date')) type="date" @endif></div>
        @endforeach
        <div class="col-md"><label class="form-label">Status</label><input class="form-control" name="status" value="{{ request('status') }}"></div>
        <div class="col-md-auto align-self-end"><button class="btn btn-primary">Filter</button></div>
    </form>
    <form id="bulkShipmentDeleteForm" method="post" action="{{ route('admin.bulk-article.bulk-destroy') }}" class="mb-2 d-flex justify-content-end" onsubmit="return confirm('Delete all selected article and tracking records?')">
        @csrf @method('DELETE')
        <button id="bulkShipmentDeleteButton" type="submit" class="btn btn-danger btn-sm" disabled><i class="fa-solid fa-trash me-1"></i> Delete selected (<span id="selectedShipmentCount">0</span>)</button>
    </form>
    <div class="card"><div class="table-responsive"><table class="table align-middle">
        <thead><tr><th><input type="checkbox" class="form-check-input" id="selectAllShipments" aria-label="Select all shipments"></th><th>Order ID</th><th>Client</th><th>Products</th><th>Article</th><th>Tracking</th><th>Weight</th><th>Charge</th><th>Courier</th><th>Shipment Date</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @if($shipments->isEmpty())
            <tr><td colspan="12" class="text-center">No shipments found.</td></tr>
        @else
        @foreach($shipments as $s)
            <tr>
                <td><input type="checkbox" class="form-check-input shipment-select" form="bulkShipmentDeleteForm" name="shipment_ids[]" value="{{ $s->id }}" aria-label="Select shipment for order {{ $s->order?->order_number }}"></td>
                <td>{{ $s->order?->order_number }}</td><td>{{ $s->receiver_name }}</td>
                <td>
                    @php
                        $orderItems = $s->order?->items ?? collect();
                    @endphp
                    <div class="d-inline-flex align-items-center justify-content-center" style="padding-left:10px">
                        @foreach($orderItems->take(3) as $idx => $item)
                            @php
                                $product = $item->product;
                                $itemOps = $s->order?->operations?->where('order_item_id', $item->id)->where('status', 'active') ?? collect();
                                $isReturned = $item->item_status === 'returned' || $itemOps->contains(fn($op) => in_array($op->operation_type, ['product_returned','customer_return','wrong_product_sent','product_damaged','product_lost']));
                                $isRefunded = (float)($item->refund_amount ?? 0) > 0 || $itemOps->contains(fn($op) => $op->is_money_refunded && (float)$op->total_refund_amount > 0) || $s->order?->payment_status === 'refunded';
                                $itemImage = $product?->primary_image_url ?? \App\Models\Setting::logoUrl();
                                $itemTitle = $item->product_name ?: ($product?->name ?? 'Product');
                            @endphp
                            <button type="button" class="p-0 bg-white rounded-2 shadow-sm tracking-product-thumb {{ $isReturned || $isRefunded ? 'border border-danger border-2' : 'border border-white border-2' }}" style="width:42px;height:42px;object-fit:cover;margin-left:{{ $idx ? '-14px' : '0' }};z-index:{{ 10-$idx }};cursor:pointer" title="View {{ $itemTitle }} details"
                                data-image="{{ $itemImage }}" data-name="{{ $itemTitle }}" data-size="{{ $item->size ?: 'N/A' }}" data-quantity="{{ $item->quantity ?: 1 }}" data-price="{{ $item->final_unit_price ?? $item->unit_price ?? 0 }}" data-category="{{ $product?->category?->name ?? '' }}" data-description="{{ $product?->description ?? '' }}" data-status="{{ $isReturned ? 'Returned' : ($isRefunded ? 'Refunded' : ($item->item_status ?: 'Active')) }}" onclick="openTrackingProductDetails(this)">
                                <img src="{{ $itemImage }}" alt="{{ $itemTitle }}" class="rounded-2 d-block" style="width:38px;height:38px;object-fit:cover">
                            </button>
                        @endforeach
                        @foreach($orderItems->slice(3) as $item)
                            @php
                                $product = $item->product;
                                $itemOps = $s->order?->operations?->where('order_item_id', $item->id)->where('status', 'active') ?? collect();
                                $isReturned = $item->item_status === 'returned' || $itemOps->contains(fn($op) => in_array($op->operation_type, ['product_returned','customer_return','wrong_product_sent','product_damaged','product_lost']));
                                $isRefunded = (float)($item->refund_amount ?? 0) > 0 || $itemOps->contains(fn($op) => $op->is_money_refunded && (float)$op->total_refund_amount > 0) || $s->order?->payment_status === 'refunded';
                                $itemImage = $product?->primary_image_url ?? \App\Models\Setting::logoUrl();
                                $itemTitle = $item->product_name ?: ($product?->name ?? 'Product');
                            @endphp
                            <button type="button" class="d-none tracking-product-thumb" data-image="{{ $itemImage }}" data-name="{{ $itemTitle }}" data-size="{{ $item->size ?: 'N/A' }}" data-quantity="{{ $item->quantity ?: 1 }}" data-price="{{ $item->final_unit_price ?? $item->unit_price ?? 0 }}" data-category="{{ $product?->category?->name ?? '' }}" data-description="{{ $product?->description ?? '' }}" data-status="{{ $isReturned ? 'Returned' : ($isRefunded ? 'Refunded' : ($item->item_status ?: 'Active')) }}" onclick="openTrackingProductDetails(this)"></button>
                        @endforeach
                        @if($orderItems->count() > 3)
                            <button type="button" class="badge bg-dark text-warning rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm border-0" style="width:24px;height:24px;font-size:.65rem;margin-left:-10px;z-index:15;cursor:pointer" title="View next product details" onclick="this.closest('td').querySelector('.tracking-product-thumb.d-none').click()">+{{ $orderItems->count()-3 }}</button>
                        @endif
                        @if($orderItems->isEmpty())<span class="text-muted">—</span>@endif
                    </div>
                </td>
                <td>{{ $s->article_number }}</td><td>{{ $s->tracking_number }}</td><td>{{ $s->weight }} {{ $s->weight_unit }}</td><td>₹{{ $s->courier_charge }}</td><td>{{ $s->courier_name }}</td><td>{{ $s->shipment_date?->format('d-m-Y') }}</td><td>{{ $s->shipment_status }}</td>
                <td><div class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.bulk-article.edit',$s) }}" title="View / Edit" aria-label="View / Edit"><i class="fa-solid fa-pen-to-square"></i></a><form method="post" action="{{ route('admin.bulk-article.destroy',$s) }}" onsubmit="return confirm('Delete this article and tracking record?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete"><i class="fa-solid fa-trash"></i></button></form></div></td>
            </tr>
        @endforeach
        @endif
        </tbody>
    </table></div><div class="card-body text-muted">Showing {{ $shipments->count() }} shipment record(s).</div></div>
</div>

<div class="modal fade" id="trackingProductDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="trackingProductModalTitle">Product Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body"><div class="d-flex gap-3 align-items-start">
            <img id="trackingProductModalImage" src="" alt="Product" class="rounded border" style="width:120px;height:150px;object-fit:cover">
            <div><div class="mb-2"><strong>Category:</strong> <span id="trackingProductModalCategory"></span></div><div class="mb-2"><strong>Size:</strong> <span id="trackingProductModalSize"></span></div><div class="mb-2"><strong>Quantity:</strong> <span id="trackingProductModalQuantity"></span></div><div class="mb-2"><strong>Unit price:</strong> ₹<span id="trackingProductModalPrice"></span></div><div class="mb-2"><strong>Status:</strong> <span id="trackingProductModalStatus" class="badge"></span></div><div><strong>Description:</strong><div id="trackingProductModalDescription" class="text-muted" style="white-space:pre-wrap"></div></div></div>
        </div></div>
    </div></div>
</div>
<style>.tracking-product-thumb{flex-shrink:0;position:relative;transition:transform .15s ease}.tracking-product-thumb:hover{transform:scale(1.12);z-index:20!important}</style>
<script>
function openTrackingProductDetails(button) {
    const data = button.dataset;
    document.getElementById('trackingProductModalTitle').textContent = data.name || 'Product Details';
    document.getElementById('trackingProductModalImage').src = data.image || '';
    document.getElementById('trackingProductModalCategory').textContent = data.category || '—';
    document.getElementById('trackingProductModalSize').textContent = data.size || 'N/A';
    document.getElementById('trackingProductModalQuantity').textContent = `${data.quantity || 1} Pcs`;
    document.getElementById('trackingProductModalPrice').textContent = Number(data.price || 0).toFixed(2);
    document.getElementById('trackingProductModalDescription').textContent = data.description || '—';
    const status = document.getElementById('trackingProductModalStatus');
    status.textContent = data.status || 'Active';
    status.className = `badge ${['Returned','Refunded'].includes(data.status) ? 'bg-danger' : 'bg-secondary'}`;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('trackingProductDetailsModal')).show();
}
const shipmentCheckboxes = Array.from(document.querySelectorAll('.shipment-select'));
const selectAllShipments = document.getElementById('selectAllShipments');
const bulkDeleteButton = document.getElementById('bulkShipmentDeleteButton');
const selectedShipmentCount = document.getElementById('selectedShipmentCount');
function updateShipmentSelection() {
    const selectedCount = shipmentCheckboxes.filter(checkbox => checkbox.checked).length;
    if (selectedShipmentCount) selectedShipmentCount.textContent = selectedCount;
    if (bulkDeleteButton) bulkDeleteButton.disabled = selectedCount === 0;
    if (selectAllShipments) {
        selectAllShipments.checked = shipmentCheckboxes.length > 0 && selectedCount === shipmentCheckboxes.length;
        selectAllShipments.indeterminate = selectedCount > 0 && selectedCount < shipmentCheckboxes.length;
    }
}
shipmentCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateShipmentSelection));
if (selectAllShipments) selectAllShipments.addEventListener('change', () => {
    shipmentCheckboxes.forEach(checkbox => checkbox.checked = selectAllShipments.checked);
    updateShipmentSelection();
});
updateShipmentSelection();
</script>
@endsection
