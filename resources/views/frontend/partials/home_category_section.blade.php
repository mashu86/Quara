<!-- Categories Section -->
@php
    $style = $categoryDisplayStyle ?? 'grid';
@endphp

<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container">
        <div class="text-center mb-4">
            <span class="text-gold text-uppercase fw-bold tracking-wider small">CHIC COLLECTIONS</span>
            <h2 class="font-serif display-6 fw-bold fs-3 fs-md-2 mb-2">SHOP BY CATEGORY</h2>
            <div class="mx-auto bg-gold" style="width: 50px; height: 3px;"></div>
        </div>

        @if($style === 'drawer')
            <!-- OPTION 2: RIGHT DRAWER PANEL WITH ICON TRIGGER -->
            <div class="text-center py-3">
                <p class="text-muted small mb-3">Click below to explore all category collections in our quick side panel.</p>
                <button type="button" class="btn btn-qw-gold rounded-pill px-4 py-3 shadow-sm fw-bold d-inline-flex align-items-center gap-2" data-bs-toggle="offcanvas" data-bs-target="#categoryOffcanvasDrawer">
                    <i class="fa-solid fa-layer-group fs-5"></i>
                    <span>Browse Categories</span>
                    <span class="badge bg-dark rounded-pill px-2.5 py-1.5 ms-1">{{ $categories->count() }}</span>
                </button>
            </div>

            <!-- Offcanvas Right Drawer -->
            <div class="offcanvas offcanvas-end rounded-start-4 border-0 shadow-lg" tabindex="-1" id="categoryOffcanvasDrawer" aria-labelledby="categoryDrawerLabel">
                <div class="offcanvas-header bg-dark text-white py-3">
                    <h5 class="offcanvas-title font-serif fw-bold" id="categoryDrawerLabel">
                        <i class="fa-solid fa-layer-group text-warning me-2"></i> All Categories
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body p-3">
                    <div class="list-group list-group-flush gap-2">
                        @foreach($categories as $category)
                            <a href="{{ route('category.products', $category->slug) }}" class="list-group-item list-group-item-action rounded-3 border d-flex justify-content-between align-items-center p-2.5 shadow-sm text-decoration-none">
                                <div class="d-flex align-items-center gap-3">
                                    @if($category->background_image)
                                        <img src="{{ $category->background_image_url }}" alt="{{ $category->name }}" class="rounded-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-black rounded-3 d-flex align-items-center justify-content-center text-gold fw-bold" style="width: 50px; height: 50px;">
                                            <i class="fa-solid fa-tag"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 small d-flex align-items-center gap-1" style="color: {{ $category->text_color }};">
                                            @if($category->is_combo_offer) <span title="Offer Combo Category">👑</span> @endif
                                            {{ $category->name }}
                                        </h6>
                                        <span class="badge bg-light text-dark border mt-1" style="font-size: 0.7rem;">
                                            @if($category->is_combo_offer)
                                                <span class="text-warning fw-bold">Min {{ $category->min_count }} for ₹{{ number_format($category->combo_price) }}</span>
                                            @else
                                                {{ $category->products_count }} Products
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-muted small"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

        @elseif($style === 'horizontal_scroll')
            <!-- OPTION 3: HORIZONTAL LINEAR SCROLL SLIDER -->
            <style>
                .qw-category-scroll-container::-webkit-scrollbar {
                    display: none;
                }
            </style>
            <div class="d-flex flex-nowrap overflow-x-auto gap-3 py-2 px-1 qw-category-scroll-container" style="scrollbar-width: none; -ms-overflow-style: none; scroll-snap-type: x mandatory;">
                @foreach($categories as $category)
                    <div class="flex-shrink-0" style="width: 175px; scroll-snap-align: start;">
                        <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none">
                            <div class="qw-category-card rounded-4 shadow-sm position-relative" style="height: 180px;">
                                @if($category->is_combo_offer)
                                    <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 shadow-sm fw-bold" style="font-size: 0.65rem; z-index: 2;">👑 COMBO OFFER</span>
                                @endif
                                @if($category->background_image)
                                    <img src="{{ $category->background_image_url }}" alt="{{ $category->name }}" class="qw-category-bg" loading="lazy">
                                @else
                                    <div class="qw-category-bg bg-black" role="img" aria-label="{{ $category->name }}"></div>
                                @endif
                                <div class="qw-category-overlay p-2 text-center">
                                    <h6 class="font-serif fw-bold mb-1 small text-truncate w-100" style="color: {{ $category->text_color }}; text-shadow: 0 2px 4px rgba(0,0,0,0.6);">
                                        @if($category->is_combo_offer) 👑 @endif{{ $category->name }}
                                    </h6>
                                    <span class="badge bg-gold rounded-pill px-2.5 py-1" style="font-size: 0.65rem;">
                                        @if($category->is_combo_offer)
                                            {{ $category->min_count }} for ₹{{ number_format($category->combo_price) }}
                                        @else
                                            {{ $category->products_count }} Items
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-2 text-muted small" style="font-size: 0.72rem;">
                <i class="fa-solid fa-arrows-left-right me-1 text-gold"></i> Swipe horizontally to view more categories
            </div>

        @else
            <!-- OPTION 1: DEFAULT GRID CARDS VIEW -->
            <div class="row g-3">
                @foreach($categories as $category)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none">
                            <div class="qw-category-card position-relative">
                                @if($category->is_combo_offer)
                                    <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 shadow-sm fw-bold text-truncate rounded-pill" style="font-size: 0.65rem; max-width: calc(100% - 16px); z-index: 2; letter-spacing: 0.3px;">
                                        👑 COMBO OFFER
                                    </span>
                                @endif
                                @if($category->background_image)
                                    <img src="{{ $category->background_image_url }}" alt="{{ $category->name }}" class="qw-category-bg" loading="lazy">
                                @else
                                    <div class="qw-category-bg bg-black" role="img" aria-label="{{ $category->name }}"></div>
                                @endif
                                <div class="qw-category-overlay p-2 p-sm-3">
                                    <h4 class="font-serif fw-bold mb-1 fs-6 fs-sm-5 fs-md-4 text-center text-wrap" style="color: {{ $category->text_color }}; text-shadow: 0 2px 4px rgba(0,0,0,0.6); word-break: break-word;">
                                        @if($category->is_combo_offer) 👑 @endif{{ $category->name }}
                                    </h4>
                                    <span class="badge bg-gold rounded-pill px-2.5 py-1.5 px-sm-3 py-sm-2 small shadow-sm text-wrap" style="font-size: 0.68rem;">
                                        @if($category->is_combo_offer)
                                            Min {{ $category->min_count }} for ₹{{ number_format($category->combo_price) }}
                                        @else
                                            {{ $category->products_count }} Items
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
