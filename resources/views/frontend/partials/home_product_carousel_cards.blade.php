<div class="qw-horizontal-track qw-products-track" id="{{ $trackId }}" data-interval="{{ $interval }}">
    @foreach($items as $product)
        @php($discountPercent = $product->price > 0 && $product->final_price < $product->price ? round((($product->price-$product->final_price)/$product->price)*100) : 0)
        <a class="qw-product-slide" href="{{ route('product.detail', $product->slug) }}">
            <div class="qw-product-slide-image">@if($discountPercent)<span class="qw-sale-badge">{{ $discountPercent }}% OFF</span>@endif<img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy"></div>
            <div class="qw-product-slide-body"><p class="qw-product-slide-title">{{ $product->name }}</p><span class="qw-product-slide-price">₹{{ number_format((float)$product->final_price, 0) }}</span>@if($discountPercent)<span class="qw-product-slide-old">₹{{ number_format((float)$product->price, 0) }}</span>@endif</div>
        </a>
    @endforeach
</div>
