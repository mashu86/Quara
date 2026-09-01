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
        {
          "@type": "ListItem",
          "position": 3,
          "name": "{{ addslashes($product->category->name) }}",
          "item": "{{ route('category.products', $product->category->slug) }}"
        },
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
        max-height: 520px;
        background-color: #f8f8f8;
    }

    .product-main-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
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

    @media (max-width: 575.98px) {
        .product-detail-page {
            padding-top: 0.85rem !important;
            padding-bottom: 2rem !important;
        }

        .product-detail-page > .product-detail-nav {
            margin-bottom: 0.85rem !important;
        }

        .product-detail-row {
            --bs-gutter-y: 0.9rem;
        }

        .product-gallery-card {
            padding: 0.5rem !important;
            border-radius: 0.85rem !important;
        }

        .product-main-image-wrap {
            max-height: none;
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
            padding: 1rem !important;
            border-radius: 0.85rem !important;
        }

        .product-title {
            font-size: 1.45rem !important;
            line-height: 1.25;
            margin-bottom: 0.75rem !important;
            overflow-wrap: anywhere;
        }

        .product-price-row {
            gap: 0.45rem !important;
            margin-bottom: 0.85rem !important;
            padding-bottom: 0.85rem !important;
        }

        .product-current-price {
            font-size: 1.65rem !important;
        }

        .product-original-price {
            font-size: 1rem !important;
        }

        .product-save-badge {
            padding: 0.4rem 0.65rem !important;
            font-size: 0.7rem !important;
        }

        .product-description {
            margin-bottom: 1rem !important;
            font-size: 0.9rem;
        }

        .product-size-section,
        .product-quantity-section {
            margin-bottom: 1rem !important;
        }

        .product-size-heading {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 0.25rem;
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
        }

        .product-size-option {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            min-height: 42px;
            min-width: 0;
            padding: 0.45rem 0.5rem !important;
        }

        .product-size-option .badge {
            margin-left: 0 !important;
        }

        .product-purchase-actions {
            margin-bottom: 1rem !important;
        }

        .product-purchase-actions .purchase-action {
            width: 100%;
            min-height: 44px;
        }

        .product-share-actions > * {
            flex: 1 1 0;
            min-width: 0;
        }

        .product-share-actions .product-share-label {
            display: inline !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        .product-share-actions {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="container py-4 product-detail-page">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4 product-detail-nav">
        <ol class="breadcrumb small product-detail-breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-decoration-none text-muted">Shop</a></li>
            <li class="breadcrumb-item"><a href="{{ route('category.products', $product->category->slug) }}" class="text-decoration-none text-muted">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 30) }}</li>
        </ol>
    </nav>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                            <span class="qw-out-of-stock-badge fs-6 px-4 py-2">OUT OF STOCK</span>
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
                    <div class="modal-content bg-dark border-0">
                        <div class="modal-header border-0 py-2">
                            <h2 class="modal-title text-white fs-6" id="imageZoomLabel">{{ $product->name }}</h2>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center p-2">
                            <img id="zoomedProductImage" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details & Buying Actions -->
        <div class="col-lg-6">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border h-100 d-flex flex-column product-info-card">
                <span class="text-gold text-uppercase fw-bold tracking-wider small mb-1">{{ $product->category->name }}</span>
                <h1 class="font-serif fw-bold h2 mb-3 text-dark product-title">{{ $product->name }}</h1>

                <!-- Pricing Display -->
                <div class="d-flex flex-wrap align-items-center gap-2 gap-sm-3 mb-3 pb-3 border-bottom product-price-row">
                    <span class="fs-2 fw-bold text-gold mb-0 product-current-price">₹{{ number_format($product->final_price, 2) }}</span>
                    @if($product->discount_type !== 'none' && $product->price > $product->final_price)
                        <span class="fs-5 text-muted text-decoration-line-through mb-0 product-original-price">₹{{ number_format($product->price, 2) }}</span>
                        <span class="badge bg-danger rounded-pill px-3 py-2 fw-semibold product-save-badge" style="font-size: 0.8rem;">Save ₹{{ number_format($product->price - $product->final_price, 2) }}</span>
                    @endif
                </div>

                <!-- Product Description -->
                <div class="mb-4 text-secondary leading-relaxed product-description">
                    {!! $product->description !!}
                </div>

                <!-- Size Selection Form -->
                <form action="{{ route('cart.add') }}" method="POST" id="productForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="mb-4 product-size-section">
                        @php
                            $hasMeasurements = $product->sizes->contains(fn($sz) => !empty($sz->chest) || !empty($sz->waist) || !empty($sz->length));
                        @endphp

                        <label class="form-label font-bold text-uppercase d-flex justify-content-between align-items-center product-size-heading">
                            <span>
                                Select Size <span class="text-danger">*</span>
                                @if($hasMeasurements)
                                    <button type="button" class="btn btn-link btn-sm text-warning p-0 ms-2 text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#sizeChartModal" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-ruler text-warning me-1"></i> Size Chart (inch)
                                    </button>
                                @endif
                            </span>
                            <span id="stockStatusNotice" class="text-muted fw-normal small">Select size to check availability</span>
                        </label>

                        <div class="d-flex flex-wrap gap-2" id="sizeButtonGroup">
                            @php
                                $totalProductStock = $product->total_stock;
                                $firstInStockSelected = false;
                            @endphp

                            @forelse($product->sizes as $pSize)
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
                                       data-length="{{ $pSize->length }}"
                                       onchange="updateStockNotice(this)"
                                       {{ $shouldCheck ? 'checked' : '' }}>
                                <label class="btn {{ $isAvailable ? 'btn-outline-dark' : 'btn-outline-secondary opacity-50' }} px-3 py-2 rounded-3 fw-semibold product-size-option text-center" for="size_{{ $pSize->id }}">
                                    <div>
                                        <span>{{ $pSize->size }}</span>
                                        @if($effectiveStock > 0 && $effectiveStock <= 3)
                                            <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Only {{ $effectiveStock }} left</span>
                                        @elseif($effectiveStock <= 0)
                                            <span class="badge bg-secondary text-white ms-1" style="font-size:0.65rem;">Out</span>
                                        @endif
                                    </div>
                                    @php
                                        $mParts = [];
                                        if(!empty($pSize->chest)) $mParts[] = 'Chest: ' . $pSize->chest . '"';
                                        if(!empty($pSize->waist)) $mParts[] = 'Waist: ' . $pSize->waist . '"';
                                        if(!empty($pSize->length)) $mParts[] = 'Length: ' . $pSize->length . '"';
                                    @endphp
                                    @if(count($mParts) > 0)
                                        <div class="small fw-normal mt-0.5 text-muted" style="font-size: 0.68rem; line-height: 1.2;">
                                            {{ implode(' • ', $mParts) }}
                                        </div>
                                    @endif
                                </label>
                            @empty
                                <div class="alert alert-warning py-2 px-3 small">No size options available.</div>
                            @endforelse
                        </div>

                        <!-- Dynamic Size Measurement Display Box (Inches) -->
                        <div id="sizeMeasurementBox" class="mt-2.5 p-3 bg-light border rounded-3 small d-none">
                            <div class="fw-bold text-dark mb-1.5 d-flex align-items-center" style="font-size: 0.8rem;">
                                <i class="fa-solid fa-ruler-horizontal text-warning me-1.5"></i>
                                <span>Selected Size <strong id="selectedSizeNameText" class="text-warning fs-6"></strong> Fit Details:</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 text-secondary mb-2" id="selectedSizeMeasurementsBadges"></div>
                        </div>

                        @if($hasMeasurements)
                            <div class="form-text small fw-bold text-dark mt-2" style="font-size: 0.74rem;">
                                <i class="fa-solid fa-ruler me-1 text-warning"></i> <span>Note: All product & body measurements above are in Inches (in).</span>
                            </div>
                        @endif
                    </div>

                    <!-- Quantity Selector -->
                    <div class="mb-4 product-quantity-section">
                        <label class="form-label font-bold text-uppercase">Quantity</label>
                        <div class="input-group" style="max-width: 140px;">
                            <button type="button" class="btn btn-outline-dark" onclick="adjustQty(-1)"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" name="quantity" id="quantityInput" class="form-control text-center fw-bold" value="1" min="1" max="50">
                            <button type="button" class="btn btn-outline-dark" onclick="adjustQty(1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($totalProductStock > 0)
                        <div class="d-grid gap-2 gap-sm-3 d-sm-flex mb-4 product-purchase-actions">
                            <button type="submit" formaction="{{ route('cart.add') }}" class="btn btn-qw-gold flex-grow-1 shadow-sm purchase-action py-2 py-md-3">
                                <i class="fa-solid fa-bag-shopping me-2"></i> ADD TO CART
                            </button>
                            <button type="submit" formaction="{{ route('cart.buy_now') }}" class="btn btn-dark flex-grow-1 shadow-sm purchase-action py-2 py-md-3">
                                <i class="fa-solid fa-bolt me-2 text-warning"></i> BUY NOW
                            </button>
                        </div>
                    @else
                        <div class="alert alert-danger text-center fw-bold py-3 mb-4 rounded-3">
                            <i class="fa-solid fa-circle-xmark me-2"></i> OUT OF STOCK
                        </div>
                    @endif
                </form>



                <!-- Share Product & WhatsApp Inquiry -->
                @php
                    $waNumber = $whatsapp ? $whatsapp->phone_number : '8078037591';
                    $productUrl = route('product.detail', $product->slug);
                    $waMsg = rawurlencode("Hi {$siteName}, I am interested in: " . $product->name . " (Price: ₹" . number_format($product->final_price, 2) . "). Link: " . $productUrl);
                    $waInquiryUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', ($whatsapp ? $whatsapp->country_code : '+91') . $waNumber) . "?text=" . $waMsg;
                    
                    $shareText = rawurlencode("Check out " . $product->name . " on {$siteName} (₹" . number_format($product->final_price, 2) . ")!");
                    $waShareUrl = "https://api.whatsapp.com/send?text=" . $shareText . "%20" . rawurlencode($productUrl);
                @endphp
                <div class="mt-auto pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold small text-muted text-uppercase tracking-wider">
                            <i class="fa-solid fa-share-nodes me-1 text-gold"></i> Share This Product
                        </span>
                        <span id="copyToast" class="badge bg-success d-none small">
                            <i class="fa-solid fa-check me-1"></i> Link Copied!
                        </span>
                    </div>
                    
                    <div class="d-flex gap-2 mb-2 product-share-actions">
                        <a href="{{ $waShareUrl }}" target="_blank" class="btn btn-success rounded-pill font-semibold py-2 px-3 btn-sm text-white shadow-sm d-flex align-items-center justify-content-center gap-1" title="Share on WhatsApp">
                            <i class="fa-brands fa-whatsapp fs-5"></i>
                            <span class="product-share-label"><span class="d-none d-sm-inline">Share on </span>WhatsApp</span>
                        </a>
                        <button type="button" onclick="shareProductLink('{{ $productUrl }}', '{{ addslashes($product->name) }}')" class="btn btn-outline-dark rounded-pill px-3 py-2 btn-sm font-semibold d-flex align-items-center gap-1" title="Copy Link">
                            <i class="fa-solid fa-link text-gold"></i>
                            <span class="product-share-label">Copy Link</span>
                        </button>
                    </div>

                    <a href="{{ $waInquiryUrl }}" target="_blank" class="btn btn-light text-muted border w-100 rounded-pill font-semibold py-2 btn-sm">
                        <i class="fa-brands fa-whatsapp text-success me-2"></i> Inquiry via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-5 pt-4 product-related-section">
            <h3 class="font-serif fw-bold mb-4">YOU MAY ALSO LIKE</h3>
            <div class="row g-4 product-related-grid">
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
                                    <span class="fs-6 fw-bold text-gold">₹{{ number_format($relProduct->final_price, 2) }}</span>
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
                                        <th>Chest (in)</th>
                                        <th>Waist (in)</th>
                                        <th>Length (in)</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->sizes as $sz)
                                        <tr>
                                            <td class="fw-bold text-dark fs-6">{{ $sz->size }}</td>
                                            <td>{{ $sz->chest ? $sz->chest . '"' : '-' }}</td>
                                            <td>{{ $sz->waist ? $sz->waist . '"' : '-' }}</td>
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
        if (stock > 0) {
            input.max = stock;
            if (parseInt(input.value) > stock) {
                input.value = stock;
            }
            notice.className = 'text-success fw-semibold small';
            notice.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> In Stock (' + stock + ' available)';
        } else {
            input.max = 0;
            input.value = 1;
            notice.className = 'text-danger fw-semibold small';
            notice.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> This size is out of stock';
        }

        // Handle Size Measurements Display (Chest, Waist, Length in Inches)
        const chest = elem.getAttribute('data-chest');
        const waist = elem.getAttribute('data-waist');
        const length = elem.getAttribute('data-length');
        const sizeName = elem.value;

        const mBox = document.getElementById('sizeMeasurementBox');
        const mText = document.getElementById('selectedSizeNameText');
        const mBadges = document.getElementById('selectedSizeMeasurementsBadges');

        if (mBox && mBadges && (chest || waist || length)) {
            mText.innerText = sizeName;
            let badgesHtml = '';
            if (chest) badgesHtml += `<span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-sm fw-semibold" style="font-size: 0.75rem;">Chest: <strong class="text-warning">${chest}"</strong></span>`;
            if (waist) badgesHtml += `<span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-sm fw-semibold" style="font-size: 0.75rem;">Waist: <strong class="text-warning">${waist}"</strong></span>`;
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

    document.addEventListener('DOMContentLoaded', function() {
        const checkedSize = document.querySelector('input[name="size"]:checked');
        if (checkedSize) {
            updateStockNotice(checkedSize);
        }
    });

    function shareProductLink(url, title) {
        if (navigator.share) {
            navigator.share({
                title: title + ' - ' + @js($siteName),
                text: 'Check out ' + title + ' on ' + @js($siteName) + '!',
                url: url
            }).catch(() => {
                copyToClipboard(url);
            });
        } else {
            copyToClipboard(url);
        }
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const toast = document.getElementById('copyToast');
            if (toast) {
                toast.classList.remove('d-none');
                setTimeout(() => toast.classList.add('d-none'), 2500);
            } else {
                alert('Product link copied to clipboard!');
            }
        });
    }
</script>
@endsection
