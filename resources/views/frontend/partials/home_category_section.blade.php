<!-- Categories Section -->
<section class="py-4 py-md-5">
    <div class="container">
        <div class="text-center mb-4">
            <span class="text-gold text-uppercase fw-bold tracking-wider small">CHIC COLLECTIONS</span>
            <h2 class="font-serif display-6 fw-bold fs-3 fs-md-2 mb-2">SHOP BY CATEGORY</h2>
            <div class="mx-auto bg-gold" style="width: 50px; height: 3px;"></div>
        </div>

        <div class="row g-3">
            @foreach($categories as $category)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('home', ['category' => $category->slug]) }}" class="text-decoration-none">
                        <div class="qw-category-card">
                            @if($category->background_image)
                                <img src="{{ $category->background_image_url }}" alt="{{ $category->name }}" class="qw-category-bg" loading="lazy">
                            @else
                                <div class="qw-category-bg bg-black" role="img" aria-label="{{ $category->name }}"></div>
                            @endif
                            <div class="qw-category-overlay">
                                <h4 class="font-serif fw-bold mb-1" style="color: {{ $category->text_color }}; text-shadow: 0 2px 4px rgba(0,0,0,0.6);">
                                    {{ $category->name }}
                                </h4>
                                <span class="badge bg-gold rounded-pill px-3 py-2 small shadow-sm">
                                    {{ $category->products_count }} Items
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
