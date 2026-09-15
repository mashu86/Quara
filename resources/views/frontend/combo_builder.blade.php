@extends('layouts.app')

@section('title', $seoTitle ?? ($category->name . ' - Combo Offer Package | ' . $siteName))
@section('meta_description', $seoDescription ?? ('Buy ' . $category->min_count . ' items for just ₹' . number_format($category->combo_price, 2) . ' at ' . $siteName))
@section('canonical_url', $canonicalUrl ?? route('category.products', $category->slug))

@section('styles')
<style>
    .combo-builder-page {
        padding-bottom: 100px;
    }

    .custom-sidebar-scroll {
        overflow-y: auto;
        max-height: calc(100vh - 170px);
        scrollbar-width: thin;
        scrollbar-color: #D4AF37 #F1F1F1;
    }
    .custom-sidebar-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-track {
        background: #F1F1F1;
        border-radius: 10px;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-thumb {
        background: #D4AF37;
        border-radius: 10px;
    }

    /* Header Luxury Banner */
    .combo-header-banner {
        background: linear-gradient(135deg, #111111 0%, #241c09 60%, #111111 100%);
        border: 1px solid rgba(212, 175, 55, 0.4) !important;
        border-radius: 20px;
    }

    /* Product Combo Cards Styling */
    .product-combo-card {
        border: 1px solid #EAEAEA;
        border-radius: 16px;
        background: #FFFFFF;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .product-combo-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
        border-color: #D4AF37;
    }

    /* Image Wrap */
    .combo-img-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 5;
        background-color: #F8F8FA;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        overflow: hidden;
    }
    .combo-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .product-combo-card:hover .combo-img-wrap img {
        transform: scale(1.04);
    }

    /* Overlay Badges */
    .combo-badge-top-left {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 2;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 50px;
        background: rgba(13, 13, 14, 0.85);
        color: #FFFFFF;
        backdrop-filter: blur(4px);
    }
    .combo-badge-top-right {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 2;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 50px;
        background: #198754;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    /* Card Content Area */
    .combo-card-content {
        padding: 12px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-grow: 1;
    }

    .combo-prod-title {
        font-family: 'Outfit', sans-serif;
        font-size: 0.88rem;
        font-weight: 600;
        color: #111111;
        line-height: 1.25;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .combo-price-wrap {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 8px;
    }
    .combo-price-unit {
        font-size: 0.98rem;
        font-weight: 700;
        color: #996515;
    }
    .combo-price-orig {
        font-size: 0.76rem;
        color: #888888;
        text-decoration: line-through;
    }

    /* Size Info Box */
    .combo-size-display-bar {
        background: #F8F9FA;
        border: 1px solid #EEEEEE;
        border-radius: 8px;
        padding: 6px 8px;
        margin-bottom: 8px;
    }

    /* Add Button Styling */
    .btn-add-combo-main {
        background: linear-gradient(135deg, #D4AF37 0%, #AA7C11 100%) !important;
        color: #FFFFFF !important;
        border: none !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        letter-spacing: 0.3px;
        padding: 8px 12px !important;
        border-radius: 50px !important;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        box-shadow: 0 3px 10px rgba(212, 175, 55, 0.25);
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-add-combo-main:hover, .btn-add-combo-main:active {
        background: linear-gradient(135deg, #AA7C11 0%, #D4AF37 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(212, 175, 55, 0.35);
    }

    /* Remove Button Styling on Card */
    .btn-combo-remove {
        background-color: #dc3545 !important;
        color: #FFFFFF !important;
        border: none !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        padding: 8px 12px !important;
        border-radius: 50px !important;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-combo-remove:hover {
        background-color: #bb2d3b !important;
        transform: translateY(-1px);
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 991.98px) {
        /* Move floating WhatsApp icon safely above sticky bottom bar */
        .qw-floating-whatsapp {
            bottom: 78px !important;
            right: 14px !important;
            width: 46px !important;
            height: 46px !important;
            font-size: 24px !important;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.5) !important;
        }
    }

    @media (max-width: 575.98px) {
        .combo-builder-page {
            padding-top: 0.25rem !important;
            padding-bottom: 115px !important;
        }
        .combo-header-banner {
            border-radius: 16px;
            background: linear-gradient(135deg, #0f0c08 0%, #1e1708 50%, #0d0d0d 100%) !important;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.3) !important;
        }
        .combo-header-banner .card-body {
            padding: 0.85rem 1rem !important;
        }
        .combo-img-wrap {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .combo-card-content {
            padding: 8px !important;
        }
        .combo-prod-title {
            font-size: 0.82rem !important;
            line-height: 1.25 !important;
            margin-bottom: 3px !important;
        }
        .combo-price-wrap {
            margin-bottom: 6px !important;
            gap: 4px !important;
        }
        .combo-price-unit {
            font-size: 0.88rem !important;
        }
        .combo-price-orig {
            font-size: 0.7rem !important;
        }
        .combo-size-display-bar {
            padding: 3px 5px !important;
            margin-bottom: 5px !important;
            border-radius: 6px !important;
        }
        .btn-add-combo-main, .btn-combo-remove {
            font-size: 0.65rem !important;
            padding: 4px 6px !important;
            letter-spacing: 0.2px;
            border-radius: 50rem !important;
            min-height: 28px !important;
        }
        .btn-add-combo-main i, .btn-combo-remove i {
            font-size: 0.68rem !important;
        }
        .combo-badge-top-left {
            font-size: 0.55rem !important;
            padding: 2px 5px !important;
            max-width: 48%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .combo-badge-top-right {
            font-size: 0.55rem !important;
            padding: 2px 5px !important;
            max-width: 48%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mobile-combo-sticky-bar {
            background: rgba(17, 17, 17, 0.96) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-top: 1.5px solid #D4AF37 !important;
            box-shadow: 0 -6px 20px rgba(0,0,0,0.3) !important;
            padding: 8px 10px !important;
        }
        .mobile-combo-sticky-bar .submit-combo-btn {
            font-size: 0.70rem !important;
            padding: 6px 12px !important;
        }
        .mobile-combo-sticky-bar .combo-count-badge {
            font-size: 0.60rem !important;
            padding: 2px 6px !important;
        }
        .combo-header-banner h4 {
            font-size: 0.95rem !important;
        }
        .combo-header-banner p {
            font-size: 0.72rem !important;
        }
        .product-combo-card.is-selected {
            border-color: #D4AF37 !important;
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.4) !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container py-3 py-md-4 combo-builder-page">

    <!-- Combo Offer Header Banner -->
    <div class="card border-0 combo-header-banner overflow-hidden shadow-sm mb-3 mb-md-4 position-relative text-white">
        <div class="card-body p-2.5 p-sm-3 p-md-4">
            <div class="row align-items-center g-2 g-md-3">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-1 px-2.5 py-0.5 rounded-pill fw-bold mb-1.5" 
                         style="background: rgba(212, 175, 55, 0.18); color: #F3E5AB; border: 1px solid rgba(212, 175, 55, 0.4); font-size: 0.65rem; letter-spacing: 0.3px;">
                        <span>👑 OFFER COMBO PACKAGE</span>
                    </div>
                    <h4 class="fw-bold text-white mb-1.5" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; line-height: 1.3;">
                        {{ $category->name }}
                    </h4>
                    <p class="text-white-50 mb-2" style="font-size: 0.82rem; line-height: 1.4;">
                        Pick any <strong class="text-warning fw-bold">{{ $category->min_count }} items</strong> from this collection for only 
                        <strong class="text-gold fw-bold ms-0.5" style="font-size: 0.98rem;">₹{{ number_format($category->combo_price, 2) }}</strong>!
                    </p>
                    <div class="d-flex align-items-center justify-content-between gap-2 w-100">
                        <span class="badge rounded-pill fw-bold px-2.5 py-1" style="background: #D4AF37; color: #111111; font-size: 0.68rem;">
                            ₹{{ number_format($unitComboPrice, 2) }} / item
                        </span>
                        @if((float)$category->delivery_charge === 0.0)
                            <span class="badge bg-success rounded-pill fw-bold px-2.5 py-1" style="font-size: 0.68rem;">
                                <i class="fa-solid fa-truck-fast me-1"></i> FREE DELIVERY
                            </span>
                        @else
                            <span class="badge bg-secondary rounded-pill fw-bold px-2.5 py-1" style="font-size: 0.68rem;">
                                Delivery: ₹{{ number_format($category->delivery_charge, 2) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end d-none d-lg-block">
                    <div class="bg-white bg-opacity-10 backdrop-blur rounded-3 p-2.5 border border-white border-opacity-10 text-center">
                        <div class="small text-white-50 text-uppercase tracking-wider fw-semibold mb-0.5" style="font-size: 0.7rem;">Combo Bundle Minimum</div>
                        <div class="fs-2 fw-bold text-warning font-serif mb-0.5">{{ $category->min_count }}</div>
                        <div class="text-white-50" style="font-size: 0.68rem;">Items required to unlock package price</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Builder Grid -->
    <div class="row g-3 g-md-4 position-relative">

        <!-- Products List Column -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-2.5">
                <h5 class="fw-bold mb-0 text-dark fs-6 fs-md-5" style="font-family: 'Outfit', sans-serif;">
                    <i class="fa-solid fa-boxes-packing text-warning me-1.5"></i> Select Your Combo Items
                </h5>
                <span class="text-muted small" style="font-size: 0.78rem;">{{ $products->count() }} Products</span>
            </div>

            <div class="row g-2 g-sm-3 g-md-4">
                @forelse($products as $product)
                    @php
                        $availableSizes = $product->sizes->where('stock', '>', 0);
                        $isOut = $product->is_out_of_stock || $availableSizes->isEmpty() || !empty($product->booked_by);
                    @endphp
                    <div class="col-6 col-md-4">
                        <div class="product-combo-card shadow-xs {{ $isOut ? 'opacity-75' : '' }}">
                            
                            <!-- Product Image -->
                            <div class="combo-img-wrap">
                                <a href="{{ route('product.detail', $product->slug) }}" target="_blank">
                                    <img src="{{ $product->primary_image_url }}" 
                                         alt="{{ $product->name }}" 
                                         loading="lazy" 
                                         onerror="this.onerror=null; this.src='{{ \App\Models\Setting::logoUrl() }}';">
                                </a>

                                @if($isOut)
                                    <span class="combo-badge-top-left bg-danger">
                                        @if(!empty($product->booked_by)) BOOKED @else OUT OF STOCK @endif
                                    </span>
                                @else
                                    <span class="combo-badge-top-left">
                                        Combo
                                    </span>
                                @endif

                                @if($product->final_price > $unitComboPrice)
                                    <span class="combo-badge-top-right">
                                        Save ₹{{ number_format($product->final_price - $unitComboPrice) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="combo-card-content">
                                <div>
                                    <h6 class="combo-prod-title" title="{{ $product->name }}">
                                        <a href="{{ route('product.detail', $product->slug) }}" target="_blank" class="text-decoration-none text-dark">
                                            {{ $product->name }}
                                        </a>
                                    </h6>

                                    <!-- Price Breakdown -->
                                    <div class="combo-price-wrap">
                                        <span class="combo-price-unit">₹{{ number_format($unitComboPrice, 2) }}</span>
                                        <span class="combo-price-orig">₹{{ number_format($product->final_price, 2) }}</span>
                                    </div>
                                </div>

                                <!-- Size & Add Controls -->
                                <div>
                                    @if($isOut)
                                        <button class="btn btn-secondary btn-sm w-100 rounded-pill text-uppercase fw-bold" style="font-size: 0.72rem; padding: 7px 8px;" disabled>Sold Out</button>
                                    @else
                                        @php
                                            $firstSz = $availableSizes->first();
                                            $hasMeasurements = $firstSz && ($firstSz->chest || $firstSz->waist || $firstSz->length);
                                        @endphp

                                        @if($availableSizes->count() === 1 && $firstSz)
                                            <!-- Single Size Pre-selected -->
                                            <input type="hidden" class="product-size-select" id="size_select_{{ $product->id }}" value="{{ $firstSz->size }}">
                                            <div class="combo-size-display-bar">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-muted fw-semibold" style="font-size: 0.72rem;">Size:</span>
                                                    <span class="badge bg-dark fw-bold text-wrap" style="font-size: 0.7rem; letter-spacing: 0.2px;">{{ $firstSz->size }}</span>
                                                </div>
                                                @if($firstSz->chest || $firstSz->waist || $firstSz->length)
                                                    <div class="text-muted mt-1 pt-1 border-top" style="font-size: 0.65rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Measurements">
                                                        {{ implode(' • ', array_filter([$firstSz->chest ? 'C: '.$firstSz->chest.'"' : null, $firstSz->waist ? 'W: '.$firstSz->waist.'"' : null, $firstSz->length ? 'L: '.$firstSz->length.'"' : null])) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <!-- Multiple Sizes Dropdown -->
                                            <div class="mb-1.5">
                                                <select class="form-select form-select-sm rounded-3 product-size-select" id="size_select_{{ $product->id }}" style="font-size: 0.75rem; padding: 4px 8px;">
                                                    @foreach($product->sizes as $sz)
                                                        @if($sz->stock > 0)
                                                            <option value="{{ $sz->size }}" {{ $loop->first ? 'selected' : '' }}>
                                                                Size {{ $sz->size }} ({{ $sz->stock }})
                                                            </option>
                                                        @else
                                                            <option value="{{ $sz->size }}" disabled>Size {{ $sz->size }} (Out)</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <button type="button" 
                                                class="btn btn-add-combo-main add-to-combo-btn" 
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                data-product-image="{{ $product->primary_image_url }}"
                                                data-original-price="{{ $product->final_price }}">
                                            <i class="fa-solid fa-plus"></i> <span>Add to Combo</span>
                                        </button>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center text-muted">
                        <i class="fa-solid fa-box-open fs-1 text-gold mb-3 d-block"></i>
                        <h5>No products available in this offer combo right now.</h5>
                        <p class="small">Check back soon for new arrivals!</p>
                        <a href="{{ route('shop') }}" class="btn btn-dark rounded-pill px-4 btn-sm">Explore Shop</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Desktop Sticky Sidebar Box -->
        <div class="col-lg-4 d-none d-lg-block">
            <div class="card border-0 shadow-lg rounded-4 sticky-top overflow-hidden" style="top: 90px; z-index: 10; max-height: calc(100vh - 110px); display: flex; flex-direction: column;">
                <div class="card-header bg-dark text-white py-3 rounded-top-4 d-flex justify-content-between align-items-center flex-shrink-0">
                    <h6 class="font-serif fw-bold mb-0">
                        <i class="fa-solid fa-crown text-warning me-2"></i> Your Combo Box
                    </h6>
                    <span class="badge bg-warning text-dark rounded-pill fw-bold combo-count-badge">0 / {{ $category->min_count }} Items</span>
                </div>
                <div class="card-body p-3.5 custom-sidebar-scroll" style="overflow-y: auto; flex-grow: 1; max-height: calc(100vh - 170px);">
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="fw-semibold text-muted">Progress</span>
                            <span class="fw-bold text-dark progress-percent-text">0%</span>
                        </div>
                        <div class="progress rounded-pill bg-light" style="height: 10px;">
                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated combo-progress-bar" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div class="mt-2 text-center combo-status-msg">
                            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill w-100 text-wrap" style="font-size: 0.75rem;">
                                Add {{ $category->min_count }} items to unlock combo price!
                            </span>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <!-- Selected Items Container -->
                    <div class="selected-combo-items-list mb-3 combo-items-container" style="max-height: 200px; overflow-y: auto;">
                        <div class="text-center text-muted py-4 small empty-combo-notice">
                            <i class="fa-solid fa-cart-flatbed fs-3 text-secondary mb-2 d-block opacity-50"></i>
                            Your combo box is currently empty.<br>Select sizes and click <strong>"+ Add to Combo"</strong>.
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <!-- Pricing Summary -->
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1.5">
                            <span>Original Items Value:</span>
                            <span class="text-decoration-line-through fw-semibold original-total-text">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between small text-success fw-bold mb-1.5 savings-row" style="display: none !important;">
                            <span>💚 Total Combo Savings:</span>
                            <span class="savings-text">-₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1.5">
                            <span>Delivery Fee:</span>
                            @if((float)$category->delivery_charge === 0.0)
                                <span class="badge bg-success">FREE</span>
                            @else
                                <span class="fw-semibold">₹{{ number_format($category->delivery_charge, 2) }}</span>
                            @endif
                        </div>
                        <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Combo Package Total:</span>
                            <span class="fs-4 fw-bold text-warning font-serif combo-package-total-text">₹0.00</span>
                        </div>
                    </div>

                    <!-- Add to Cart / Buy Now Button -->
                    <button type="button" 
                            class="btn btn-add-combo-main w-100 py-2.5 shadow text-uppercase submit-combo-btn" 
                            disabled>
                        <i class="fa-solid fa-bolt me-1"></i> Buy Combo Now
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Mobile Fixed Bottom Action Bar -->
<div class="d-lg-none position-fixed bottom-0 start-0 end-0 bg-dark text-white shadow-lg mobile-combo-sticky-bar" style="z-index: 1030;">
    <!-- Top Progress Indicator Line -->
    <div style="height: 3px; background: rgba(255,255,255,0.12); width: 100%; position: absolute; top: 0; left: 0; overflow: hidden;">
        <div class="progress-bar bg-warning combo-progress-bar" role="progressbar" style="height: 100%; width: 0%; transition: width 0.3s ease;"></div>
    </div>
    
    <div class="container-fluid d-flex align-items-center justify-content-between gap-2 pt-1">
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-0.5">
                <span class="badge bg-warning text-dark rounded-pill fw-bold combo-count-badge" style="font-size: 0.72rem;">0 / {{ $category->min_count }} Items</span>
                <span class="text-warning fw-bold font-serif combo-package-total-text" style="font-size: 0.98rem;">₹0.00</span>
            </div>
            <button type="button" class="btn btn-link text-white-50 p-0 text-decoration-underline text-start small border-0" data-bs-toggle="modal" data-bs-target="#mobileComboModal" style="font-size: 0.72rem; letter-spacing: 0.2px;">
                <i class="fa-solid fa-eye text-warning me-1"></i> View Items (<span class="combo-count-num">0</span>)
            </button>
        </div>
        <button type="button" 
                class="btn btn-add-combo-main px-3 py-2 shadow-sm text-uppercase submit-combo-btn" 
                style="width: auto; font-size: 0.76rem; min-width: 130px; border-radius: 50rem !important;" 
                disabled>
            <i class="fa-solid fa-bolt me-1"></i> Buy Now
        </button>
    </div>
</div>

<!-- Mobile Selected Items Modal -->
<div class="modal fade" id="mobileComboModal" tabindex="-1" aria-labelledby="mobileComboModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 rounded-top-4">
                <h6 class="modal-title font-serif fw-bold" id="mobileComboModalLabel">
                    <i class="fa-solid fa-crown text-warning me-2"></i> Your Combo Box Items
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Progress -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-muted">Package Progress</span>
                        <span class="fw-bold text-dark progress-percent-text">0%</span>
                    </div>
                    <div class="progress rounded-pill bg-light" style="height: 10px;">
                        <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated combo-progress-bar" role="progressbar" style="width: 0%;"></div>
                    </div>
                </div>

                <div class="combo-items-container mb-3" style="max-height: 45vh; overflow-y: auto;">
                    <div class="text-center text-muted py-4 small empty-combo-notice">
                        Your combo box is currently empty.
                    </div>
                </div>

                <div class="bg-light rounded-3 p-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Original Total:</span>
                        <span class="text-decoration-line-through fw-semibold original-total-text">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between small text-success fw-bold mb-1 savings-row" style="display: none !important;">
                        <span>💚 Savings:</span>
                        <span class="savings-text">-₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                        <span class="fw-bold text-dark">Combo Total:</span>
                        <span class="fs-5 fw-bold text-warning font-serif combo-package-total-text">₹0.00</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 p-3">
                <button type="button" 
                        class="btn btn-add-combo-main w-100 py-2.5 shadow text-uppercase submit-combo-btn" 
                        disabled>
                    <i class="fa-solid fa-bolt me-1"></i> Buy Combo Now
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const MIN_COUNT = {{ (int) $category->min_count }};
    const COMBO_PRICE = {{ (float) $category->combo_price }};
    const UNIT_COMBO_PRICE = {{ (float) $unitComboPrice }};
    const COMBO_CATEGORY_ID = {{ (int) $category->id }};
    const ADD_COMBO_URL = "{{ route('cart.add_combo') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    let selectedComboItems = [];

    document.addEventListener('DOMContentLoaded', function() {
        // Add or Remove Combo Item directly from product card button
        document.querySelectorAll('.add-to-combo-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = parseInt(this.getAttribute('data-product-id'), 10);
                const productName = this.getAttribute('data-product-name');
                const productImage = this.getAttribute('data-product-image');
                const originalPrice = parseFloat(this.getAttribute('data-original-price'));

                // Check if product is already added
                const existingIndex = selectedComboItems.findIndex(item => item.product_id === productId);
                if (existingIndex !== -1) {
                    // Remove from combo when clicking the button again
                    selectedComboItems.splice(existingIndex, 1);
                    renderComboSummary();
                    return;
                }

                const sizeSelect = document.getElementById(`size_select_${productId}`);
                if (!sizeSelect || !sizeSelect.value) {
                    alert('Please select a size for this product first!');
                    if (sizeSelect) sizeSelect.focus();
                    return;
                }

                const selectedSize = sizeSelect.value;

                selectedComboItems.push({
                    id: Date.now() + '_' + Math.random().toString(36).substring(2, 7),
                    product_id: productId,
                    name: productName,
                    image: productImage,
                    size: selectedSize,
                    original_price: originalPrice,
                    unit_price: UNIT_COMBO_PRICE
                });

                renderComboSummary();
            });
        });

        // Submit Combo Box Handler
        document.querySelectorAll('.submit-combo-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (selectedComboItems.length < MIN_COUNT) {
                    alert(`Please select at least ${MIN_COUNT} items to complete this combo package.`);
                    return;
                }

                document.querySelectorAll('.submit-combo-btn').forEach(b => {
                    b.disabled = true;
                    b.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Adding...`;
                });

                const payloadItems = selectedComboItems.map(item => ({
                    product_id: item.product_id,
                    size: item.size,
                    quantity: 1
                }));

                fetch(ADD_COMBO_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        combo_category_id: COMBO_CATEGORY_ID,
                        items: payloadItems
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "{{ route('checkout.index') }}";
                    } else {
                        alert(data.message || 'Failed to process combo offer.');
                        document.querySelectorAll('.submit-combo-btn').forEach(b => {
                            b.disabled = false;
                            b.innerHTML = `<i class="fa-solid fa-bolt me-1"></i> Buy Combo Now`;
                        });
                    }
                })
                .catch(err => {
                    alert('An error occurred. Please try again.');
                    document.querySelectorAll('.submit-combo-btn').forEach(b => {
                        b.disabled = false;
                        b.innerHTML = `<i class="fa-solid fa-bolt me-1"></i> Buy Combo Now`;
                    });
                });
            });
        });
    });

    function removeComboItem(itemId) {
        selectedComboItems = selectedComboItems.filter(item => item.id !== itemId);
        renderComboSummary();
    }

    function renderComboSummary() {
        const count = selectedComboItems.length;
        const percent = Math.min(100, Math.round((count / MIN_COUNT) * 100));
        const selectedProdIds = selectedComboItems.map(item => item.product_id);

        // Update product card buttons UI (Toggle between "+ Add to Combo" and " Remove")
        document.querySelectorAll('.add-to-combo-btn').forEach(btn => {
            const prodId = parseInt(btn.getAttribute('data-product-id'), 10);
            const card = btn.closest('.product-combo-card');
            if (selectedProdIds.includes(prodId)) {
                btn.disabled = false;
                btn.className = 'btn btn-combo-remove add-to-combo-btn';
                btn.innerHTML = `<i class="fa-solid fa-trash-can me-1"></i> <span>Remove</span>`;
                btn.setAttribute('title', 'Click to remove from combo box');
                if (card) card.classList.add('is-selected');
            } else {
                btn.disabled = false;
                btn.className = 'btn btn-add-combo-main add-to-combo-btn';
                btn.innerHTML = `<i class="fa-solid fa-plus"></i> <span>Add to Combo</span>`;
                btn.removeAttribute('title');
                if (card) card.classList.remove('is-selected');
            }
        });

        // Update all elements matching class targets
        document.querySelectorAll('.combo-count-badge').forEach(el => el.textContent = `${count} / ${MIN_COUNT} Items`);
        document.querySelectorAll('.combo-count-num').forEach(el => el.textContent = count);
        document.querySelectorAll('.combo-progress-bar').forEach(el => el.style.width = `${percent}%`);
        document.querySelectorAll('.progress-percent-text').forEach(el => el.textContent = `${percent}%`);

        document.querySelectorAll('.combo-status-msg').forEach(el => {
            if (count < MIN_COUNT) {
                const diff = MIN_COUNT - count;
                el.innerHTML = `<span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill w-100 text-wrap" style="font-size: 0.75rem;">
                    Add ${diff} more item${diff > 1 ? 's' : ''} to unlock combo price!
                </span>`;
            } else {
                el.innerHTML = `<span class="badge bg-success px-3 py-1.5 rounded-pill w-100 text-wrap" style="font-size: 0.75rem;">
                    🎉 Offer Combo Unlocked! (${count} Items Selected)
                </span>`;
            }
        });

        // Render Items HTML in all containers
        let itemsHtml = '';
        if (count === 0) {
            itemsHtml = `<div class="text-center text-muted py-3 small empty-combo-notice">
                <i class="fa-solid fa-cart-flatbed fs-3 text-secondary mb-2 d-block opacity-50"></i>
                Your combo box is currently empty.<br>Select sizes and click <strong>"+ Add to Combo"</strong>.
            </div>`;
        } else {
            itemsHtml = '<div class="d-flex flex-column gap-2">';
            selectedComboItems.forEach(item => {
                itemsHtml += `
                    <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-3 border">
                        <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                            <img src="${item.image}" alt="${item.name}" class="rounded-2 object-fit-cover flex-shrink-0" style="width: 38px; height: 38px;">
                            <div class="text-truncate">
                                <h6 class="fw-bold mb-0 text-dark small text-truncate" style="font-size: 0.8rem;">${item.name}</h6>
                                <span class="badge bg-dark" style="font-size: 0.65rem;">Size: ${item.size}</span>
                                <span class="text-warning fw-bold small ms-1" style="font-size: 0.75rem;">₹${item.unit_price.toFixed(2)}</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-link text-danger p-1 text-decoration-none flex-shrink-0" onclick="removeComboItem('${item.id}')" title="Remove item">
                            <i class="fa-solid fa-xmark fs-5"></i>
                        </button>
                    </div>
                `;
            });
            itemsHtml += '</div>';
        }

        document.querySelectorAll('.combo-items-container').forEach(el => el.innerHTML = itemsHtml);

        // Calculate Totals - Ceil round total combo price to nearest whole rupee (e.g. 583.33 -> 584)
        let originalTotal = selectedComboItems.reduce((sum, item) => sum + item.original_price, 0);
        let rawComboTotal = (COMBO_PRICE / MIN_COUNT) * count;
        let comboTotal = Math.ceil(rawComboTotal);
        let savings = Math.max(0, originalTotal - comboTotal);

        document.querySelectorAll('.original-total-text').forEach(el => el.textContent = `₹${originalTotal.toFixed(2)}`);
        document.querySelectorAll('.combo-package-total-text').forEach(el => el.textContent = `₹${comboTotal.toFixed(2)}`);

        document.querySelectorAll('.savings-row').forEach(row => {
            if (savings > 0) {
                row.style.setProperty('display', 'flex', 'important');
            } else {
                row.style.setProperty('display', 'none', 'important');
            }
        });

        document.querySelectorAll('.savings-text').forEach(el => el.textContent = `-₹${savings.toFixed(2)}`);

        // Enable / Disable submit buttons
        document.querySelectorAll('.submit-combo-btn').forEach(btn => {
            btn.disabled = (count < MIN_COUNT);
        });
    }
</script>
@endsection
