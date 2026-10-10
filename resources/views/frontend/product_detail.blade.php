@extends('layouts.app')

@section('title', $seoTitle ?? ($product->name . ' - Buy Online | Quara Wardrobe'))
@section('meta_description', $seoDescription ?? strip_tags(Str::limit($product->description, 150)))
@section('canonical_url', $canonicalUrl ?? route('product.detail', $product->slug))
@section('og_image', $ogImage ?? $product->primary_image_url)

@section('json_ld')
<script type="application/ld+json">
{
  "\u0040context": "https://schema.org",
  "@graph": [
    {
      "@type": "Product",
      "@id": "{{ route('product.detail', $product->slug) }}#product",
      "name": "{{ addslashes($product->name) }}",
      "image": [
        @foreach($product->images as $idx => $img)
          "{{ $img->image_url }}"{{ !$loop->last ? ',' : '' }}
        @endforeach
      ],
      "description": "{{ addslashes(strip_tags(Str::limit($product->description, 300))) }}",
      "sku": "{{ $product->sku ?: ('QW-PROD-' . $product->id) }}",
      "brand": {
        "@type": "Brand",
        "name": "Quara Wardrobe"
      },
      "offers": {
        "@type": "Offer",
        "url": "{{ route('product.detail', $product->slug) }}",
        "priceCurrency": "INR",
        "price": "{{ number_format($product->final_price, 2, '.', '') }}",
        "priceValidUntil": "{{ date('Y-12-31') }}",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "{{ $product->total_stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "seller": {
          "@type": "Organization",
          "name": "Quara Wardrobe"
        }
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{ route('product.detail', $product->slug) }}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ route('home') }}"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Shop",
          "item": "{{ route('shop') }}"
        },
        @if($product->category)
        {
          "@type": "ListItem",
          "position": 3,
          "name": "{{ addslashes($product->category->name) }}",
          "item": "{{ route('category.products', $product->category->slug) }}"
        },
        @endif
        {
          "@type": "ListItem",
          "position": 4,
          "name": "{{ addslashes($product->name) }}",
          "item": "{{ route('product.detail', $product->slug) }}"
        }
      ]
    }
  ]
}
</script>
@endsection

@section('styles')
<style>
    .product-detail-page,
    .product-detail-page .row > * {
        min-width: 0;
    }

    .product-detail-page { max-width: 1320px; }

    .product-detail-nav {
        font-size: 0.82rem;
        color: #78756f;
    }

    .product-detail-nav a { color: #78756f; text-decoration: none; }
    .product-detail-nav a:hover { color: #a57a20; }

    .product-detail-breadcrumb {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 0.2rem;
        scrollbar-width: none;
        white-space: nowrap;
    }

    .product-detail-breadcrumb::-webkit-scrollbar {
        display: none;
    }

    .product-detail-breadcrumb .breadcrumb-item {
        flex-shrink: 0;
    }

    .product-main-image-wrap {
        aspect-ratio: 4 / 5;
        max-height: 620px;
        background: #f5f2ec;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-main-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        cursor: zoom-in;
    }

    .product-description {
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .product-description img,
    .product-description video,
    .product-description iframe,
    .product-description table {
        max-width: 100% !important;
        height: auto !important;
    }

    .product-size-option {
        max-width: 100%;
        min-width: 64px;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    .product-gallery-card,
    .product-info-card {
        border-color: #ece8df !important;
        box-shadow: 0 14px 40px rgba(38, 33, 24, 0.07) !important;
    }

    .product-gallery-card { overflow: hidden; }
    .product-info-card { border-radius: 1.15rem !important; }
    .product-info-card { min-width: 0; }
    .product-eyebrow {
        color: #92702b;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }
    .product-title { letter-spacing: -0.025em; line-height: 1.15; }
    .product-current-price { font-size: 1.9rem; letter-spacing: -0.035em; }
    .product-description { max-width: 62ch; line-height: 1.7; }
    .product-price-row { border-color: #ece8df !important; }
    .product-quantity-section .input-group {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0;
        overflow: visible;
        border: 0 !important;
        border-radius: 0;
        background: transparent;
    }
    .product-quantity-section .input-group .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        min-width: 34px;
        height: 34px;
        margin: 0 !important;
        padding: 0;
        border: 1px solid #e9e3d7 !important;
        border-radius: 50% !important;
        background: #fff;
        color: #6c6558;
        font-size: 0.72rem;
        box-shadow: 0 2px 5px rgba(38, 33, 24, 0.05);
        transition: border-color 0.18s ease, background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }
    .product-quantity-section .input-group .btn:hover { border-color: #d5b75b !important; background: #fbf8ef; color: #8b6508; }
    .product-quantity-section .input-group .btn:active { transform: scale(0.94); }
    .product-quantity-section input {
        flex: 0 0 38px;
        min-width: 38px;
        padding: 0;
        margin: 0 !important;
        border: 0 !important;
        background: transparent;
        box-shadow: none !important;
        color: #25231f;
        font-size: 1.05rem;
        appearance: textfield;
    }
    .product-quantity-section input::-webkit-inner-spin-button,
    .product-quantity-section input::-webkit-outer-spin-button { margin: 0; appearance: none; }
    .product-quantity-label {
        display: inline-flex;
        align-items: center;
        margin: 0;
        color: #514c42;
        font-size: 0.84rem;
        font-weight: 600;
        letter-spacing: 0;
    }
    .product-quantity-section { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    .product-purchase-actions .purchase-action { min-height: 48px; letter-spacing: 0.045em; }
    .product-share-actions .product-share-btn { min-height: 42px; }

    @media (max-width: 575.98px) {
        .product-detail-page {
            padding-top: 0.85rem !important;
            padding-bottom: 2rem !important;
        }

        .product-detail-page > .product-detail-nav {
            margin-bottom: 0.85rem !important;
        }

        .product-detail-nav { font-size: 0.74rem; }

        .product-detail-row {
            --bs-gutter-y: 0.9rem;
        }

        .product-gallery-card {
            padding: 0.5rem !important;
            border-radius: 0.85rem !important;
        }

        .product-main-image-wrap {
            max-height: 520px;
            aspect-ratio: 1 / 1.08;
            margin-bottom: 0.5rem !important;
            border-radius: 0.65rem !important;
        }

        .product-thumbnail-strip {
            gap: 0.4rem !important;
            margin-bottom: -0.1rem;
        }

        .product-thumbnail-strip .thumbnail-selector {
            width: 58px !important;
            height: 58px !important;
            flex: 0 0 58px;
        }

        .product-info-card {
            height: auto !important;
            padding: 1.1rem !important;
            border-radius: 1rem !important;
        }

        .product-title {
            font-size: 1.45rem !important;
            line-height: 1.3;
            margin-bottom: 0.5rem !important;
            overflow-wrap: anywhere;
        }

        .product-price-row {
            gap: 0.45rem !important;
            margin-bottom: 0.65rem !important;
            padding-bottom: 0.65rem !important;
        }

        .product-current-price {
            font-size: 1.7rem !important;
        }

        .product-original-price {
            font-size: 0.82rem !important;
        }

        .product-save-badge {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.65rem !important;
        }

        .product-description {
            margin-bottom: 0.85rem !important;
            font-size: 0.82rem !important;
            line-height: 1.5;
        }

        .product-size-section,
        .product-quantity-section {
            margin-bottom: 0.85rem !important;
        }

        .product-size-heading {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.78rem !important;
        }

        #stockStatusNotice {
            display: block;
            width: 100%;
            line-height: 1.35;
            white-space: normal;
        }

        #sizeButtonGroup {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 0.4rem !important;
        }

        .product-size-option {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.2rem;
            min-height: 38px;
            min-width: 0;
            padding: 0.35rem 0.45rem !important;
            font-size: 0.80rem !important;
        }

        /* Modern light size buttons without black background */
        .qw-size-btn-option {
            border: 1.5px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            color: #1e293b !important;
            transition: all 0.2s ease !important;
        }
        .qw-size-btn-option:hover {
            border-color: #d4af37 !important;
            background-color: #fdfbf7 !important;
            color: #aa7c11 !important;
        }
        .btn-check:checked + .qw-size-btn-option {
            border-color: #d4af37 !important;
            background-color: rgba(212, 175, 55, 0.12) !important;
            color: #8b6508 !important;
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.25) !important;
        }

        .product-size-option .badge {
            margin-left: 0 !important;
        }

        .product-purchase-actions {
            margin-bottom: 1rem !important;
        }

        .product-purchase-actions .purchase-action {
            width: 100%;
            min-height: 48px;
        }

        .product-share-actions {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.4rem !important;
            width: 100% !important;
        }

        .product-share-actions .product-share-btn {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            height: 42px !important;
            padding: 0 !important;
            border-radius: 50px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.15rem !important;
        }

        .product-share-actions .product-share-label {
            display: none !important;
        }

        .product-size-option { min-height: 42px; }

        @media (min-width: 576px) {
            .product-share-actions .product-share-btn {
                padding: 0.4rem 0.8rem !important;
                font-size: 0.82rem !important;
            }

            .product-share-actions .product-share-label {
                display: inline !important;
                white-space: nowrap;
            }
        }

        .product-related-section {
            margin-top: 2rem !important;
            padding-top: 0 !important;
        }

        .product-related-grid {
            --bs-gutter-x: 0.75rem;
            --bs-gutter-y: 0.75rem;
        }

        .product-related-card-body {
            padding: 0.65rem !important;
        }

        #imageZoomModal .modal-dialog {
            margin: 0.5rem;
        }
    }

    @media (max-width: 359.98px) {
        #sizeButtonGroup {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container py-4 product-detail-page">

    <nav class="product-detail-nav mb-3" aria-label="Breadcrumb">
        <ol class="breadcrumb product-detail-breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop') }}">Shop</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('category.products', $product->category->slug) }}">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5 product-detail-row">
        <!-- Image Gallery -->
        <div class="col-lg-6">
            <div class="bg-white p-3 rounded-4 shadow-sm border product-gallery-card">
                <div class="mb-3 overflow-hidden rounded-3 text-center position-relative product-main-image-wrap">
                    @if($discountPercentage > 0)
                        <span class="qw-discount-badge fs-6">{{ $discountPercentage }}% OFF</span>
                    @endif
                    @if($product->total_stock <= 0)
                        <div class="qw-out-of-stock-overlay">
                            <span class="qw-out-of-stock-badge fs-6 px-4 py-2">SOLD OUT</span>
                        </div>
                    @endif
                    <img id="mainProductImage" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="product-main-image" data-bs-toggle="modal" data-bs-target="#imageZoomModal">
                </div>

                @if($product->images->count() > 1)
                    <div class="d-flex gap-2 overflow-x-auto pb-2 product-thumbnail-strip">
                        @foreach($product->images as $img)
                            <img src="{{ $img->image_url }}" alt="Thumb" class="rounded-3 border thumbnail-selector" style="width: 75px; height: 75px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('mainProductImage').src='{{ $img->image_url }}'">
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-labelledby="imageZoomLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content bg-white border-0 shadow">
                        <div class="modal-header border-0 py-2">
                            <h2 class="modal-title text-dark fs-6 fw-bold" id="imageZoomLabel">{{ $product->name }}</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center p-2">
                            <img id="zoomedProductImage" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="img-fluid rounded-3" style="max-height: 80vh; object-fit: contain;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details & Buying Actions -->
        <div class="col-lg-6">
            <div class="bg-white p-3 p-md-4 rounded-4 shadow-sm border h-100 d-flex flex-column product-info-card">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="product-eyebrow">{{ $product->category?->name ?? $siteName }}</span>
                    @if($product->total_stock > 0)
                        <span class="small text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> In stock</span>
                    @endif
                </div>
                <h1 class="fw-bold mb-2 text-dark product-title">{{ $product->name }}</h1>

                <!-- Pricing Display -->
                <div class="d-flex flex-wrap align-items-baseline gap-2 gap-sm-3 mb-2.5 pb-2.5 border-bottom product-price-row">
                    @php
                        $hasDiscountDetail = ($product->discount_type !== 'none' && $product->price > $product->final_price);
                        $finalDetailFormatted = $product->final_price == floor($product->final_price) ? number_format($product->final_price, 0) : number_format($product->final_price, 2);
                        $origDetailFormatted = $product->price == floor($product->price) ? number_format($product->price, 0) : number_format($product->price, 2);
                    @endphp
                    <span class="fw-bold text-gold mb-0 product-current-price">₹{{ $finalDetailFormatted }}</span>
                    @if($hasDiscountDetail)
                        <span class="qw-cut-price mb-0 product-original-price">₹{{ $origDetailFormatted }}</span>
                        <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold product-save-badge" style="font-size: 0.65rem;">Save ₹{{ number_format($product->price - $product->final_price, 0) }}</span>
                    @endif
                </div>

                <!-- Product Description -->
                <div class="mb-3 text-secondary leading-relaxed product-description">
                    {!! $product->description !!}
                </div>

                <!-- Size Selection Form -->
                <form action="{{ route('cart.add') }}" method="POST" id="productForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @php
                        $isDownGarment = ($product->measurement_type ?? 'up') === 'down'
                            || $product->sizes->contains(fn ($sz) => !empty($sz->hip));
                        $hasMeasurements = $product->sizes->contains(fn ($sz) => $isDownGarment
                            ? (!empty($sz->hip) || !empty($sz->length))
                            : (!empty($sz->chest) || !empty($sz->waist) || !empty($sz->length)));
                        $noSizeAvailableStock = $product->sizes
                            ->filter(fn ($variant) => trim((string) $variant->size) === '')
                            ->sum(fn ($variant) => $variant->available_stock);
                        $quantityLimit = $product->selectableSizes->isEmpty()
                            ? max(1, (int) $noSizeAvailableStock)
                            : 50;
                    @endphp

                    @if($product->selectableSizes->isNotEmpty())
                    <div class="mb-3 product-size-section">
                        <label class="form-label font-bold text-uppercase d-flex justify-content-between align-items-center product-size-heading mb-1.5">
                            <span>
                                Select Size <span class="text-muted small fw-normal">(optional)</span>
                                @if($hasMeasurements)
                                    <button type="button" class="btn btn-link btn-sm text-warning p-0 ms-2 text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#sizeChartModal" style="font-size: 0.74rem;">
                                        <i class="fa-solid fa-ruler text-warning me-1"></i> Size Chart (inch)
                                    </button>
                                @endif
                            </span>
                            <span id="stockStatusNotice" class="text-muted fw-normal small">Size is optional</span>
                        </label>

                        <div class="d-flex flex-wrap gap-2" id="sizeButtonGroup">
                            @php
                                $firstInStockSelected = true;
                            @endphp

                            @forelse($product->selectableSizes as $pSize)
                                @php
                                    $effectiveStock = $product->is_out_of_stock ? 0 : $pSize->stock;
                                    $isAvailable = $effectiveStock > 0;
                                    $shouldCheck = false;
                                    if ($isAvailable && !$firstInStockSelected) {
                                        $shouldCheck = true;
                                        $firstInStockSelected = true;
                                    }
                                @endphp
                                <input type="radio"
                                       class="btn-check"
                                       name="size"
                                       id="size_{{ $pSize->id }}"
                                       value="{{ $pSize->size }}"
                                       data-stock="{{ $effectiveStock }}"
                                       data-chest="{{ $pSize->chest }}"
                                       data-waist="{{ $pSize->waist }}"
                                       data-hip="{{ $pSize->hip }}"
                                       data-length="{{ $pSize->length }}"
                                       onchange="updateStockNotice(this)"
                                       {{ $shouldCheck ? 'checked' : '' }}>
                                <label class="btn {{ $isAvailable ? 'qw-size-btn-option' : 'btn-light text-muted border opacity-50' }} px-3 py-2 rounded-3 fw-semibold product-size-option text-center" for="size_{{ $pSize->id }}">
                                    <div>
                                        <span class="fw-bold">{{ $pSize->size }}</span>
                                        @if($effectiveStock > 0)
                                            <span class="text-danger fw-semibold ms-1" style="font-size:0.68rem;">({{ $effectiveStock }} left)</span>
                                        @else
                                            <span class="text-muted fw-normal ms-1" style="font-size:0.68rem;">(Out)</span>
                                        @endif
                                    </div>
                                    @php
                                        $sizeMeasurementParts = $isDownGarment
                                            ? array_filter([$pSize->hip ? 'Hip: '.$pSize->hip.'"' : null, $pSize->length ? 'Length: '.$pSize->length.'"' : null])
                                            : array_filter([$pSize->chest ? 'Chest: '.$pSize->chest.'"' : null, $pSize->waist ? 'Waist: '.$pSize->waist.'"' : null, $pSize->length ? 'Length: '.$pSize->length.'"' : null]);
                                    @endphp
                                    @if(count($sizeMeasurementParts))
                                        <div class="small mt-1 text-secondary" style="font-size:0.68rem;">{{ implode(' · ', $sizeMeasurementParts) }}</div>
                                    @endif
                                </label>
                            @empty
                                <div class="alert alert-warning py-2 px-3 small">No size options available.</div>
                            @endforelse
                        </div>

                        <!-- Dynamic Size Measurement Display Box (Inches) -->
                        <div id="sizeMeasurementBox" class="mt-2 p-2.5 bg-light border rounded-3 small d-none">
                            <div class="fw-bold text-dark mb-1 d-flex align-items-center" style="font-size: 0.78rem;">
                                <i class="fa-solid fa-ruler-horizontal text-warning me-1.5"></i>
                                <span>Selected Size <strong id="selectedSizeNameText" class="text-warning fs-6"></strong> Fit Details:</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 text-secondary mb-1" id="selectedSizeMeasurementsBadges"></div>
                        </div>

                        <!-- Collapsible Dynamic Size Chart Section (Controlled by Admin Toggle) -->
                        @if(!empty($product->display_size_chart) && isset($displaySizeMaster) && $displaySizeMaster->rows->isNotEmpty())
                            <div class="card border-0 shadow-sm rounded-3 mt-3 overflow-hidden border" id="collapsibleSizeChartCard">
                                <div class="card-header bg-light border-0 py-2.5 px-3 d-flex align-items-center justify-content-between cursor-pointer" data-bs-toggle="collapse" data-bs-target="#collapseSizeChartBody" aria-expanded="true" style="cursor: pointer;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-ruler-combined text-warning fs-6"></i>
                                        <span class="fw-bold text-dark small" style="font-size: 0.82rem;">{{ $displaySizeMaster->name }} - Size Chart Guide</span>
                                    </div>
                                    <span class="badge bg-dark text-warning fw-bold px-2 py-1" style="font-size: 0.68rem;">
                                        SIZE MASTER <i class="fa-solid fa-chevron-down ms-1 text-white"></i>
                                    </span>
                                </div>
                                <div id="collapseSizeChartBody" class="collapse show">
                                    <div class="card-body p-2.5 bg-white">
                                        <!-- Unit Switcher Toggle (Inch / Cm) -->
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-muted small" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-circle-info text-primary me-1"></i> Body Measurement Reference
                                            </span>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Measurement Unit Switcher">
                                                <input type="radio" class="btn-check" name="unit_toggle" id="unit_inch" value="inch" checked onchange="toggleSizeChartUnit('inch')">
                                                <label class="btn btn-outline-dark py-0 px-2.5 small fw-bold" for="unit_inch" style="font-size: 0.72rem;">INCH</label>
                                                <input type="radio" class="btn-check" name="unit_toggle" id="unit_cm" value="cm" onchange="toggleSizeChartUnit('cm')">
                                                <label class="btn btn-outline-dark py-0 px-2.5 small fw-bold" for="unit_cm" style="font-size: 0.72rem;">CM</label>
                                            </div>
                                        </div>

                                        <div class="table-responsive rounded-3 border">
                                            <table class="table table-striped table-hover align-middle text-center small mb-0" id="storefrontSizeChartTable">
                                                <thead class="table-dark">
                                                    <tr style="font-size: 0.75rem;">
                                                        <th>Size</th>
                                                        <th>Chest (<span class="unit-label">in</span>)</th>
                                                        <th>Waist (<span class="unit-label">in</span>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size: 0.78rem;">
                                                    @foreach($displaySizeMaster->rows as $r)
                                                        <tr>
                                                            <td class="fw-bold text-dark">{{ $r->size_label }}</td>
                                                            <td class="chest-val" data-inch="{{ $r->chest }}">{{ $r->chest ?: '-' }}</td>
                                                            <td class="waist-val" data-inch="{{ $r->waist }}">{{ $r->waist ?: '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($hasMeasurements)
                            <div class="form-text small fw-bold text-dark mt-1.5" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-ruler me-1 text-warning"></i> <span>Note: All product & body measurements above are specified in finished garment dimensions.</span>
                            </div>
                        @endif
                    </div>
                    @else
                    <input type="hidden" name="size" value="">
                    @if($hasMeasurements)
                        <div class="mb-3 p-2.5 bg-light border rounded-3 small" id="sizeMeasurementBox">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.78rem;">
                                <i class="fa-solid fa-ruler-horizontal text-warning me-1.5"></i>
                                {{ $isDownGarment ? 'Size Measurements (Hip & Length)' : 'Size Measurements' }}
                            </div>
                            <div class="d-flex flex-column gap-1 text-secondary" id="selectedSizeMeasurementsBadges">
                                @foreach($product->sizes as $pSize)
                                    @php
                                        $measureParts = $isDownGarment
                                            ? array_filter([$pSize->hip ? 'Hip: '.$pSize->hip.'"' : null, $pSize->length ? 'Length: '.$pSize->length.'"' : null])
                                            : array_filter([$pSize->chest ? 'Chest: '.$pSize->chest.'"' : null, $pSize->waist ? 'Waist: '.$pSize->waist.'"' : null, $pSize->length ? 'Length: '.$pSize->length.'"' : null]);
                                    @endphp
                                    @if(count($measureParts))
                                        <span>{{ $pSize->size ? $pSize->size.': ' : '' }}{{ implode(' · ', $measureParts) }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @endif

                    <!-- Quantity Selector -->
                    @if(!$minimumPurchaseCategory)
                    <div class="mb-3 product-quantity-section">
                        <label class="product-quantity-label" for="quantityInput">Quantity</label>
                        <div class="input-group" style="max-width: 130px;">
                            <button type="button" class="btn btn-outline-secondary quantity-adjust-btn" onclick="adjustQty(-1)" aria-label="Decrease quantity"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" name="quantity" id="quantityInput" class="form-control text-center fw-bold" value="1" min="1" max="{{ $quantityLimit }}">
                            <button type="button" class="btn btn-outline-secondary quantity-adjust-btn" onclick="adjustQty(1)" aria-label="Increase quantity"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    @if($minimumPurchaseCategory && $product->total_stock > 0)
                        <div class="alert alert-info small rounded-3">
                            Select at least {{ $minimumPurchaseCategory->minimum_purchase_count }} products from this category. Add this product, then choose the next one.
                        </div>
                        <button type="button" id="selectAndContinueCategory" class="btn btn-qw-gold w-100 shadow-sm py-2 rounded-pill fw-bold">
                            <i class="fa-solid fa-list-check me-2"></i> Select this product and continue choosing
                        </button>
                        <script>
                            document.getElementById('selectAndContinueCategory')?.addEventListener('click', function () {
                                const selectedSize = document.querySelector('#productForm input[name="size"]:checked')?.value || '';
                                const target = new URL(@json(route('category.products', $minimumPurchaseCategory->slug)), window.location.origin);
                                target.searchParams.set('product_id', @json($product->id));
                                if (selectedSize) target.searchParams.set('size', selectedSize);
                                window.location.href = target.toString();
                            });
                        </script>
                    @elseif($product->total_stock > 0)
                        <div class="d-grid gap-2 gap-sm-3 d-sm-flex mb-3 product-purchase-actions">
                            <button type="submit" name="purchase_action" value="add" class="btn btn-qw-gold flex-grow-1 shadow-sm purchase-action py-2 rounded-pill fw-bold" style="font-size: 0.82rem;">
                                <i class="fa-solid fa-bag-shopping me-2"></i> ADD TO CART
                            </button>
                            <button type="submit" name="purchase_action" value="buy_now" class="btn btn-qw-outline-gold flex-grow-1 shadow-sm purchase-action py-2 rounded-pill fw-bold" style="font-size: 0.82rem;">
                                <i class="fa-solid fa-bolt me-2 text-warning"></i> BUY NOW
                            </button>
                        </div>
                    @else
                        <div class="alert alert-danger text-center fw-bold py-3 mb-4 rounded-3">
                            <i class="fa-solid fa-circle-xmark me-2"></i> SOLD OUT
                        </div>
                    @endif
                </form>



                <!-- Share Product & WhatsApp Inquiry / QR Scanner -->
                @php
                    $waNumber = $whatsapp ? $whatsapp->phone_number : '8078037591';
                    $productUrl = route('product.detail', $product->slug);
                    $waMsg = rawurlencode("Hi {$siteName}, I am interested in: " . $product->name . " (Price: ₹" . number_format($product->final_price, 2) . "). Link: " . $productUrl);
                    $waInquiryUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', ($whatsapp ? $whatsapp->country_code : '+91') . $waNumber) . "?text=" . $waMsg;
                    
                    $shareText = rawurlencode("Check out " . $product->name . " on {$siteName} (₹" . number_format($product->final_price, 2) . ")!");
                    $waShareUrl = "https://api.whatsapp.com/send?text=" . $shareText . "%20" . rawurlencode($productUrl);
                @endphp
                <div class="mt-1 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                        <span class="fw-bold small text-muted text-uppercase tracking-wider" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-share-nodes me-1 text-gold"></i> Share & Connect
                        </span>
                        <span id="copyToast" class="badge bg-success d-none small">
                            <i class="fa-solid fa-check me-1"></i> Link Copied!
                        </span>
                    </div>
                    
                    <!-- Responsive Share Action Bar (Icon-only on Mobile, Full Pills on Desktop) -->
                    <div class="d-flex align-items-center justify-content-between gap-2 product-share-actions">
                        <!-- 1. WhatsApp Share -->
                        <a href="{{ $waShareUrl }}" target="_blank" class="btn btn-success rounded-pill py-2 px-3 btn-sm text-white shadow-sm d-flex align-items-center justify-content-center text-center gap-1.5 flex-fill product-share-btn" title="Share on WhatsApp">
                            <i class="fa-brands fa-whatsapp fs-5"></i>
                            <span class="product-share-label d-none d-sm-inline">WhatsApp</span>
                        </a>

                        <!-- 2. Direct Copy Link -->
                        <button type="button" onclick="copyDirectProductLink('{{ $productUrl }}')" class="btn btn-outline-dark rounded-pill px-3 py-2 btn-sm font-semibold d-flex align-items-center justify-content-center text-center gap-1.5 flex-fill product-share-btn" title="Copy Link directly">
                            <i class="fa-solid fa-link text-gold fs-6"></i>
                            <span class="product-share-label d-none d-sm-inline">Copy Link</span>
                        </button>

                        <!-- 3. QR Code Scanner Modal Trigger -->
                        <button type="button" data-bs-toggle="modal" data-bs-target="#productQrModal" class="btn btn-dark rounded-pill px-3 py-2 btn-sm font-semibold d-flex align-items-center justify-content-center text-center gap-1.5 flex-fill product-share-btn" title="Product QR Code Scanner">
                            <i class="fa-solid fa-qrcode text-warning fs-6"></i>
                            <span class="product-share-label d-none d-sm-inline">QR Code</span>
                        </button>

                        <!-- 4. WhatsApp Inquiry -->
                        <a href="{{ $waInquiryUrl }}" target="_blank" class="btn btn-light text-muted border rounded-pill px-3 py-2 btn-sm font-semibold d-flex align-items-center justify-content-center text-center gap-1.5 flex-fill product-share-btn" title="Inquiry via WhatsApp">
                            <i class="fa-brands fa-whatsapp text-success fs-6"></i>
                            <span class="product-share-label d-none d-sm-inline">Inquiry</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product QR Code Modal -->
    <div class="modal fade" id="productQrModal" tabindex="-1" aria-labelledby="productQrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 text-center overflow-hidden">
                <div class="modal-header bg-dark text-white py-2.5 px-3">
                    <h5 class="modal-title font-serif fw-bold fs-6 d-flex align-items-center gap-2 mb-0" id="productQrModalLabel">
                        <i class="fa-solid fa-qrcode text-warning"></i> Product QR Code
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light d-flex flex-column align-items-center justify-content-center">
                    <div class="p-3 bg-white rounded-4 shadow-sm border mb-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($productUrl) }}" alt="Product QR Code" class="img-fluid rounded-3" style="width: 200px; height: 200px; object-fit: contain;">
                    </div>
                    <h6 class="fw-bold text-dark mb-1 text-truncate w-100 px-2" style="font-size: 0.90rem;">{{ $product->name }}</h6>
                    <div class="fw-bold text-gold small mb-2">₹{{ number_format($product->final_price, 2) }}</div>
                    <p class="text-muted extra-small mb-0" style="font-size: 0.72rem; max-width: 220px;">
                        Scan this QR code with any smartphone camera to view this product page directly.
                    </p>
                </div>
                <div class="modal-footer bg-white border-top py-2 px-3 justify-content-center">
                    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-4 fw-bold" onclick="copyDirectProductLink('{{ $productUrl }}')">
                        <i class="fa-solid fa-link me-1 text-gold"></i> Copy Product Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-5 pt-4 product-related-section">
            <h3 class="font-serif fw-bold mb-4">YOU MAY ALSO LIKE</h3>
            <div class="row g-2 g-sm-3 g-md-4 product-related-grid">
                @foreach($relatedProducts as $relProduct)
                    @if($relProduct->total_stock > 0 && !$relProduct->is_out_of_stock)
                        <div class="col-6 col-md-3">
                            <div class="qw-product-card h-100">
                                <a href="{{ route('product.detail', $relProduct->slug) }}">
                                    <div class="qw-product-img-wrapper">
                                        <img src="{{ $relProduct->primary_image_url }}" alt="{{ $relProduct->name }}" class="qw-product-img">
                                    </div>
                                </a>
                                <div class="p-3 product-related-card-body">
                                    <h6 class="font-serif fw-bold text-dark text-truncate mb-1">{{ $relProduct->name }}</h6>
                                    <div class="d-flex flex-wrap align-items-baseline gap-1.5 mb-1">
                                        @php
                                            $relHasDiscount = ($relProduct->discount_type !== 'none' && $relProduct->price > $relProduct->final_price);
                                            $relFinalFormatted = $relProduct->final_price == floor($relProduct->final_price) ? number_format($relProduct->final_price, 0) : number_format($relProduct->final_price, 2);
                                            $relOrigFormatted = $relProduct->price == floor($relProduct->price) ? number_format($relProduct->price, 0) : number_format($relProduct->price, 2);
                                        @endphp
                                        <span class="qw-product-price">₹{{ $relFinalFormatted }}</span>
                                        @if($relHasDiscount)
                                            <span class="qw-cut-price">₹{{ $relOrigFormatted }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
    <!-- Size Chart Modal (Inches) -->
    @if($hasMeasurements)
        <div class="modal fade" id="sizeChartModal" tabindex="-1" aria-labelledby="sizeChartModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-dark text-white py-2.5 px-3">
                        <h5 class="modal-title fs-6 fw-bold" id="sizeChartModalLabel">
                            <i class="fa-solid fa-ruler-combined text-warning me-2"></i> Size Chart & Fit Guide (Inches)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3 p-sm-4 bg-light">
                        <p class="small text-muted mb-3">All body & garment measurements below are specified in inches (in) for accurate fitting.</p>
                        <div class="table-responsive rounded-3 border bg-white shadow-sm">
                            <table class="table table-striped align-middle text-center small mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Size</th>
                                        @if($isDownGarment)
                                            <th>Hip (in)</th>
                                        @else
                                            <th>Chest (in)</th>
                                            <th>Waist (in)</th>
                                        @endif
                                        <th>Length (in)</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->sizes as $sz)
                                        <tr>
                                            <td class="fw-bold text-dark fs-6">{{ $sz->size }}</td>
                                            @if($isDownGarment)
                                                <td>{{ $sz->hip ? $sz->hip . '"' : '-' }}</td>
                                            @else
                                                <td>{{ $sz->chest ? $sz->chest . '"' : '-' }}</td>
                                                <td>{{ $sz->waist ? $sz->waist . '"' : '-' }}</td>
                                            @endif
                                            <td>{{ $sz->length ? $sz->length . '"' : '-' }}</td>
                                            <td>
                                                @if($sz->stock > 0)
                                                    <span class="badge bg-success">In Stock</span>
                                                @else
                                                    <span class="badge bg-secondary">Out</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
    function updateStockNotice(elem) {
        const stock = parseInt(elem.getAttribute('data-stock'));
        const notice = document.getElementById('stockStatusNotice');
        const input = document.getElementById('quantityInput');
        document.querySelectorAll('.purchase-action').forEach(button => button.disabled = stock <= 0);
        if (!elem.value) {
            input.max = Math.max(1, stock || 1);
            syncQuantityButtons();
            if (parseInt(input.value) > stock) input.value = Math.max(1, stock);
            notice.className = stock > 0 ? 'text-muted fw-normal small' : 'text-danger fw-normal small';
                            notice.textContent = stock > 0 ? `${stock} available` : 'No stock available';
            document.getElementById('sizeMeasurementBox')?.classList.add('d-none');
            return;
        }
        if (stock > 0) {
            input.max = stock;
            syncQuantityButtons();
            if (parseInt(input.value) > stock) {
                input.value = stock;
            }
            notice.className = 'text-success fw-semibold small';
            notice.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> In Stock (' + stock + ' available)';
        } else {
            input.max = 0;
            syncQuantityButtons();
            input.value = 1;
            notice.className = 'text-danger fw-semibold small';
            notice.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> This size is out of stock';
        }

        // Handle Size Measurements Display (Chest, Waist, Length in Inches)
        const chest = elem.getAttribute('data-chest');
        const waist = elem.getAttribute('data-waist');
        const hip = elem.getAttribute('data-hip');
        const length = elem.getAttribute('data-length');
        const sizeName = elem.value;
        const isDownGarment = @json($isDownGarment);

        const mBox = document.getElementById('sizeMeasurementBox');
        const mText = document.getElementById('selectedSizeNameText');
        const mBadges = document.getElementById('selectedSizeMeasurementsBadges');

        if (mBox && mBadges && (isDownGarment ? (hip || length) : (chest || waist || length))) {
            mText.innerText = sizeName;
            let badgesHtml = '';
            if (isDownGarment && hip) badgesHtml += `<span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-sm fw-semibold" style="font-size: 0.75rem;">Hip: <strong class="text-warning">${hip}"</strong></span>`;
            if (!isDownGarment && chest) badgesHtml += `<span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-sm fw-semibold" style="font-size: 0.75rem;">Chest: <strong class="text-warning">${chest}"</strong></span>`;
            if (!isDownGarment && waist) badgesHtml += `<span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-sm fw-semibold" style="font-size: 0.75rem;">Waist: <strong class="text-warning">${waist}"</strong></span>`;
            if (length) badgesHtml += `<span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-sm fw-semibold" style="font-size: 0.75rem;">Length: <strong class="text-warning">${length}"</strong></span>`;
            mBadges.innerHTML = badgesHtml;
            mBox.classList.remove('d-none');
        } else if (mBox) {
            mBox.classList.add('d-none');
        }
    }

    document.getElementById('mainProductImage').addEventListener('click', function () {
        document.getElementById('zoomedProductImage').src = this.src;
    });

    function adjustQty(amount) {
        const input = document.getElementById('quantityInput');
        const maxStock = parseInt(input.max) || 50;
        let current = parseInt(input.value) || 1;
        current += amount;
        if (current < 1) current = 1;
        if (current > maxStock) current = maxStock;
        input.value = current;
    }

    function syncQuantityButtons() {
        const input = document.getElementById('quantityInput');
        if (!input) return;
        const onlyOneAvailable = parseInt(input.max, 10) <= 1;
        document.querySelectorAll('.quantity-adjust-btn').forEach(button => {
            button.hidden = onlyOneAvailable;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkedSize = document.querySelector('input[name="size"]:checked');
        if (checkedSize) {
            updateStockNotice(checkedSize);
        } else {
            const stock = {{ (int) $product->total_stock }};
            const notice = document.getElementById('stockStatusNotice');
            const input = document.getElementById('quantityInput');
            if (input) input.max = Math.max(1, stock);
            syncQuantityButtons();
            if (notice) notice.textContent = stock > 0 ? 'Size is optional' : 'No stock available';
            document.querySelectorAll('.purchase-action').forEach(button => button.disabled = stock <= 0);
        }
    });

    function copyDirectProductLink(url) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => {
                showCopyToast();
            }).catch(() => {
                fallbackCopyText(url);
            });
        } else {
            fallbackCopyText(url);
        }
    }

    function fallbackCopyText(text) {
        try {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            showCopyToast();
        } catch(e) {}
    }

    function showCopyToast() {
        const toast = document.getElementById('copyToast');
        if (toast) {
            toast.classList.remove('d-none');
            setTimeout(() => toast.classList.add('d-none'), 2200);
        }
    }

    function convertInchStringToCm(str) {
        if (!str || str.trim() === '-' || str.trim() === '') return '-';
        let clean = str.replace(/["″]/g, '').trim();
        if (clean.includes('–') || clean.includes('-')) {
            let parts = clean.split(/[–-]/);
            if (parts.length === 2) {
                let n1 = parseFloat(parts[0]);
                let n2 = parseFloat(parts[1]);
                if (!isNaN(n1) && !isNaN(n2)) {
                    let cm1 = Math.round(n1 * 2.54);
                    let cm2 = Math.round(n2 * 2.54);
                    return `${cm1}–${cm2} cm`;
                }
            }
        }
        let num = parseFloat(clean);
        if (!isNaN(num)) {
            let cm = Math.round(num * 2.54);
            return `${cm} cm`;
        }
        return str;
    }

    function toggleSizeChartUnit(unit) {
        const table = document.getElementById('storefrontSizeChartTable');
        if (!table) return;

        const unitLabels = table.querySelectorAll('.unit-label');
        unitLabels.forEach(el => el.textContent = unit);

        const cells = table.querySelectorAll('.chest-val, .waist-val');
        cells.forEach(cell => {
            const inchVal = cell.getAttribute('data-inch');
            if (!inchVal || inchVal.trim() === '-' || inchVal.trim() === '') {
                cell.textContent = '-';
                return;
            }
            if (unit === 'cm') {
                cell.textContent = convertInchStringToCm(inchVal);
            } else {
                cell.textContent = inchVal.includes('"') ? inchVal : (inchVal + '"');
            }
        });
    }
</script>
@endsection
