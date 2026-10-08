@extends('layouts.app')

@section('title', $seoTitle . ' - ' . $siteName)
@section('meta_description', $seoDescription)
@section('canonical_url', $canonicalUrl)

@section('styles')
<style>
    .minimum-builder-page { padding-bottom: 112px !important; }
    .minimum-builder-hero { background: linear-gradient(125deg, #171717 0%, #34302a 100%); }
    .minimum-builder-grid { --bs-gutter-x: .8rem; --bs-gutter-y: .8rem; }
    .minimum-product-card { display: flex; flex-direction: column; height: 100%; min-width: 0; transition: transform .18s ease, box-shadow .18s ease; }
    .minimum-product-card:hover { transform: translateY(-2px); box-shadow: 0 .6rem 1.25rem rgba(0,0,0,.12) !important; }
    .minimum-product-card > .qw-card-body { display: flex; flex: 1 1 auto; flex-direction: column; }
    .minimum-product-image { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .minimum-product-image-box { position: relative; width: 100%; padding-top: 125%; overflow: hidden; background: #f4f4f6; }
    .minimum-product-name-row { min-width: 0; display: flex; align-items: center; justify-content: space-between; gap: .35rem; }
    .minimum-product-name { min-width: 0; min-height: 2.2rem; }
    .minimum-card-details { min-height: 2.6rem; }
    .minimum-select-btn { min-height: 34px; margin-top: auto !important; padding: .3rem .65rem; font-size: .88rem; white-space: nowrap; }
    .minimum-buy-bar { position: fixed; z-index: 1030; left: 0; right: 0; bottom: 0; padding: .7rem 1rem calc(.7rem + env(safe-area-inset-bottom)); background: rgba(255,255,255,.97); border-top: 2px solid #d4af37; box-shadow: 0 -5px 22px rgba(0,0,0,.12); }
    .minimum-buy-inner { width: min(100%, 1100px); margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    .minimum-buy-summary { min-width: 0; }
    .minimum-buy-button { min-width: 112px; white-space: nowrap; }
    @media (max-width: 575.98px) {
        .minimum-builder-page { padding-top: 1rem !important; }
        .minimum-builder-hero { margin-bottom: 1.1rem !important; }
        .minimum-builder-hero .card-body { padding: 1rem !important; }
        .minimum-builder-hero h1 { font-size: 1.08rem; line-height: 1.35; }
        .minimum-builder-hero p { font-size: .82rem; }
        .minimum-builder-grid { --bs-gutter-x: .65rem; --bs-gutter-y: .65rem; }
        .minimum-product-card .card-body { padding: 9px 10px !important; }
        .minimum-product-name { font-size: .84rem; line-height: 1.3; }
        .minimum-product-price { font-size: 1rem; margin-bottom: .65rem !important; }
        .minimum-product-size-display { font-size: .76rem; }
        .minimum-select-btn { min-height: 31px; padding: 2px 7px; font-size: .74rem; }
        .minimum-buy-bar { padding-left: .8rem; padding-right: .8rem; }
        .minimum-buy-inner { gap: .6rem; }
        .minimum-buy-summary-title { font-size: .92rem; line-height: 1.25; }
        .minimum-buy-summary-hint { font-size: .72rem; line-height: 1.25; }
        .minimum-buy-button { min-width: 98px; padding: .55rem .85rem !important; font-size: .88rem; }
    }
</style>
@endsection

@section('content')
<div class="container py-4 minimum-builder-page">
    <div class="card border-0 rounded-4 shadow-sm mb-4 minimum-builder-hero text-white">
        <div class="card-body p-4 p-md-5">
            <span class="badge rounded-pill bg-warning text-dark mb-2">{{ $category->name }}</span>
            <h1 class="h3 fw-bold">Select at least {{ $category->minimum_purchase_count }} products from this category</h1>
            <p class="mb-0 text-white-50">Select the minimum quantity to continue. You can select more products if you like.</p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0">Select Products</h2>
        <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><span id="selectedCount">0</span> / {{ $category->minimum_purchase_count }}</span>
    </div>

    <div class="row minimum-builder-grid">
        @forelse($products as $product)
            @php
                $sizes = $product->sizes->filter(fn($size) => trim((string) $size->size) !== '' && $size->available_stock > 0);
                $hasNoSizeStock = $product->sizes->filter(fn($size) => trim((string) $size->size) === '')->sum(fn($size) => $size->available_stock) > 0;
                $available = !$product->is_out_of_stock && empty($product->booked_by) && ($sizes->isNotEmpty() || $hasNoSizeStock);
                $defaultSize = '';
                $allMeasurements = [];
                $isDownGarment = ($product->measurement_type ?? 'up') === 'down' || $product->sizes->contains(fn($size) => !empty($size->hip));
                foreach ($product->sizes as $size) {
                    if ($size->available_stock <= 0) continue;
                    $parts = $isDownGarment
                        ? array_filter([$size->hip ? 'H:'.$size->hip.'"' : null, $size->length ? 'L:'.$size->length.'"' : null])
                        : array_filter([$size->chest ? 'C:'.$size->chest.'"' : null, $size->waist ? 'W:'.$size->waist.'"' : null, $size->length ? 'L:'.$size->length.'"' : null]);
                    if ($parts) $allMeasurements[] = count($product->sizes) > 1 ? $size->size.': '.implode(' ', $parts) : implode(' · ', $parts);
                }
            @endphp
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <article class="card h-100 qw-product-card overflow-hidden minimum-product-card" data-product-id="{{ $product->id }}">
                    <div class="minimum-product-image-box">
                        <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="qw-product-img minimum-product-image" loading="lazy">
                        @if(!$available)<span class="badge bg-danger position-absolute top-0 start-0 m-2">SOLD OUT</span>@endif
                    </div>
                    <div class="qw-card-body p-2 p-sm-3 d-flex flex-column flex-grow-1">
                        <div class="minimum-product-name-row mb-1.5">
                            <h3 class="qw-product-title mb-0 flex-grow-1 minimum-product-name" title="{{ $product->name }}">{{ Str::limit($product->name, 22, '...') }}</h3>
                            <button type="button" class="btn btn-link p-0 border-0 d-flex justify-content-center align-items-center flex-shrink-0 text-warning" style="width:20px;height:20px;text-decoration:none" data-bs-toggle="modal" data-bs-target="#productInfo{{ $product->id }}" aria-label="View product details">
                                <i class="fa-solid fa-circle-info" style="font-size:.88rem"></i>
                            </button>
                        </div>
                        <div class="qw-product-price minimum-product-price mb-1.5">₹{{ number_format($product->final_price, 2) }}</div>
                        <div class="minimum-card-details">
                            @if($sizes->isNotEmpty())
                                <input type="hidden" class="minimum-product-size" value="{{ $defaultSize }}">
                                <div class="d-flex flex-wrap gap-1 align-items-center mb-1 minimum-product-size-display">
                                    @foreach($sizes as $size)<span class="badge bg-light text-dark border-0 px-2 py-1 rounded-2 fw-bold" style="font-size:.63rem">{{ $size->size }}</span>@endforeach
                                </div>
                            @elseif($hasNoSizeStock)
                                <input type="hidden" class="minimum-product-size" value="">
                            @else
                                <div class="small text-danger mb-2">Sold Out</div>
                            @endif
                            @if($allMeasurements)
                                <div class="text-muted fw-semibold w-100 mt-1.5 text-truncate" style="font-size:.63rem;line-height:1.3" title="{{ implode(' | ', $allMeasurements) }}"><i class="fa-solid fa-ruler text-warning me-1"></i>{{ implode(' / ', $allMeasurements) }}</div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-qw-gold rounded-pill w-100 minimum-select-btn" data-id="{{ $product->id }}" {{ !$available ? 'disabled' : '' }}>{{ $available ? 'Add' : 'Sold Out' }}</button>
                    </div>
                </article>
            </div>

            <div class="modal fade" id="productInfo{{ $product->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header"><h2 class="modal-title h5 fw-bold">{{ $product->name }}</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-5"><img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="img-fluid rounded-3 w-100" style="max-height:420px;object-fit:cover"></div>
                                <div class="col-md-7">
                                    <div class="h5 fw-bold text-warning mb-3">₹{{ number_format($product->final_price, 2) }}</div>
                                    <div class="text-secondary mb-3">{!! $product->description !!}</div>
                                    <h3 class="h6 fw-bold">Sizes and Measurements</h3>
                                    @if($product->sizes->isNotEmpty())
                                        <div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead><tr><th>Size</th><th>Chest</th><th>Waist</th><th>Hip</th><th>Length</th><th>Availability</th></tr></thead><tbody>
                                            @foreach($product->sizes as $size)<tr><td>{{ $size->size ?: 'Standard' }}</td><td>{{ $size->chest ? $size->chest.'″' : '—' }}</td><td>{{ $size->waist ? $size->waist.'″' : '—' }}</td><td>{{ $size->hip ? $size->hip.'″' : '—' }}</td><td>{{ $size->length ? $size->length.'″' : '—' }}</td><td>{{ $size->available_stock > 0 ? $size->available_stock.' available' : 'Sold Out' }}</td></tr>@endforeach
                                        </tbody></table></div>
                                    @else<div class="text-muted small">No size information.</div>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info">No products are available in this category.</div></div>
        @endforelse
    </div>

    <div class="minimum-buy-bar">
        <div class="minimum-buy-inner">
            <div class="minimum-buy-summary"><div class="fw-bold minimum-buy-summary-title"><span id="selectedCountBottom">0</span> / {{ $category->minimum_purchase_count }} selected</div><div id="minimumHint" class="text-muted minimum-buy-summary-hint">Select {{ $category->minimum_purchase_count }} more to reach the minimum.</div></div>
            <button id="minimumBuyButton" class="btn btn-warning rounded-pill fw-bold px-4 minimum-buy-button" disabled>Buy Now</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(() => {
    const MINIMUM = {{ (int) $category->minimum_purchase_count }};
    const CATEGORY_ID = {{ (int) $category->id }};
    const SUBMIT_URL = @json(route('cart.add_category_minimum'));
    const CSRF = @json(csrf_token());
    const CATEGORY_URL = @json(route('category.products', $category->slug));
    const selected = new Map();

    function render() {
        const count = selected.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('selectedCountBottom').textContent = count;
        document.getElementById('minimumBuyButton').disabled = count < MINIMUM;
        document.getElementById('minimumHint').textContent = count >= MINIMUM
            ? 'Minimum reached. You can continue to checkout.'
            : `Select ${MINIMUM - count} more to reach the minimum.`;
        document.querySelectorAll('.minimum-select-btn').forEach(button => {
            const active = selected.has(Number(button.dataset.id));
            button.textContent = active ? 'Added · Remove' : (button.disabled ? 'Sold Out' : 'Add');
        });
    }

    document.querySelectorAll('.minimum-select-btn').forEach(button => button.addEventListener('click', () => {
        const id = Number(button.dataset.id);
        if (selected.has(id)) selected.delete(id);
        else {
            const size = document.querySelector(`.minimum-product-card[data-product-id="${id}"] .minimum-product-size`)?.value ?? '';
            selected.set(id, { product_id: id, size });
        }
        render();
    }));

    const params = new URLSearchParams(location.search);
    const requestedProduct = Number(params.get('product_id') || 0);
    const requestedSize = params.get('size') || '';
    if (requestedProduct) {
        const button = document.querySelector(`.minimum-select-btn[data-id="${requestedProduct}"]:not(:disabled)`);
        if (button) {
            const sizeControl = document.querySelector(`.minimum-product-card[data-product-id="${requestedProduct}"] .minimum-product-size`);
            if (sizeControl && requestedSize) sizeControl.value = requestedSize;
            button.click();
        } else {
            alert('Another customer may have purchased this product. Please choose a different product from this category.');
        }
        history.replaceState({}, '', CATEGORY_URL);
    }

    document.getElementById('minimumBuyButton').addEventListener('click', async event => {
        const button = event.currentTarget;
        if (selected.size < MINIMUM) return;
        button.disabled = true;
        button.textContent = 'Please wait...';
        try {
            const response = await fetch(SUBMIT_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ category_id: CATEGORY_ID, items: [...selected.values()] })
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Unable to complete your purchase.');
            location.href = @json(route('checkout.index'));
        } catch (error) {
            alert(error.message);
            button.textContent = 'Buy Now';
            render();
        }
    });
    render();
})();
</script>
@endsection
