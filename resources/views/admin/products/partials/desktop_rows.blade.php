@foreach($products as $product)
    @php
        $totalStock = $product->sizes->sum('stock');
        $imgSrc = $product->primary_image_url;
        $displayName = Str::limit($product->name, 22);
    @endphp
    <tr>
        <td class="prod-sticky-col text-center py-2 px-2">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="prod-img-wrapper border shadow-xs mb-1 position-relative" onclick="openProductPreview('{{ addslashes($imgSrc) }}', '{{ addslashes($product->name) }}', '₹{{ number_format($product->final_price, 0) }}', {{ json_encode($product->sizes) }}, '{{ addslashes($product->product_code) }}')" title="Click to preview product image">
                    <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit: cover;">
                    @if(!empty($product->combo_category_id))
                        <span class="position-absolute top-0 start-0 bg-dark text-warning px-1 py-0.5 rounded-end shadow-sm" style="font-size: 0.65rem; z-index: 4;" title="Offer Combo Category Product">
                            👑
                        </span>
                    @endif
                    <div class="prod-img-overlay">
                        <i class="fa-solid fa-eye text-white" style="font-size: 0.72rem;"></i>
                    </div>
                </div>
                <div class="fw-bold text-dark text-center lh-xs prod-name-text" style="font-size: 0.75rem; max-width: 110px;" title="{{ $product->name }}">
                    @if(!empty($product->combo_category_id))
                        <span class="text-warning me-0.5" title="Offer Combo Category Product">👑</span>
                    @endif
                    {{ $displayName }}
                </div>
                @if(filled($product->product_code))
                    <span class="badge bg-light text-dark border font-monospace mt-1" style="font-size: 0.65rem;" title="Product code">{{ $product->product_code }}</span>
                @endif
            </div>
        </td>
        <td>
            <div class="d-flex flex-wrap gap-1" style="max-width: 160px;">
                @php
                    $cats = $product->categories->isNotEmpty() ? $product->categories : collect([$product->category])->filter();
                @endphp
                @foreach($cats as $cat)
                    <span class="badge bg-light text-dark border" style="font-size: 0.68rem;">{{ $cat->name }}</span>
                @endforeach
            </div>
        </td>
        <td>₹{{ number_format($product->price, 2) }}</td>
        <td>
            @if($product->discount_type === 'fixed')
                <span class="badge bg-danger" style="font-size: 0.68rem;">₹{{ number_format($product->discount_value, 2) }} OFF</span>
            @elseif($product->discount_type === 'percentage')
                <span class="badge bg-danger" style="font-size: 0.68rem;">{{ (int)$product->discount_value }}% OFF</span>
            @else
                <span class="text-muted small">None</span>
            @endif
        </td>
        <td class="fw-bold text-gold fs-6">₹{{ number_format($product->final_price, 2) }}</td>
        <td>
            <div class="d-flex flex-wrap gap-1" style="max-width: 180px;">
                @foreach($product->sizes as $pSize)
                    @php $reservedQty = (int) $product->bookings->where('product_size_id', $pSize->id)->sum(fn($booking) => $booking->remaining_quantity); @endphp
                    <span class="badge {{ $pSize->stock > $reservedQty ? 'bg-dark' : ($reservedQty > 0 ? 'bg-warning text-dark' : 'bg-danger') }}" style="font-size: 0.68rem;" title="Size {{ $pSize->size }}; {{ $reservedQty }} reserved">
                        {{ $pSize->size }}: {{ $pSize->stock }}
                        @if($reservedQty > 0)<span class="opacity-75">({{ $reservedQty }} booked)</span>@endif
                    </span>
                @endforeach
            </div>
            <div class="small fw-bold mt-1 {{ $totalStock > 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.72rem;">
                Total: {{ $totalStock }} pcs
            </div>
        </td>
        <td>
            @include('admin.products.partials.booking_actions')
        </td>
        <td>
            <div class="form-check form-switch mb-0 d-inline-flex align-items-center" title="{{ ucfirst($product->status) }}">
                <input class="form-check-input product-status-toggle" type="checkbox" role="switch"
                       id="productStatusToggle_{{ $product->id }}"
                       data-product-id="{{ $product->id }}"
                       data-url="{{ route('admin.products.toggle-status', $product->id) }}"
                       aria-label="{{ $product->name }} status"
                       {{ $product->status === 'active' ? 'checked' : '' }}
                       style="cursor: pointer; width: 2.3em; height: 1.2em;">
                <label class="visually-hidden" for="productStatusToggle_{{ $product->id }}">{{ ucfirst($product->status) }}</label>
            </div>
        </td>
        <td class="text-nowrap small">
            {{ $product->created_at?->format('d M Y') ?? '—' }}
            <div class="text-muted">{{ $product->created_at?->format('h:i A') }}</div>
        </td>
        <td class="text-nowrap small">
            {{ $product->updated_at?->format('d M Y') ?? '—' }}
            <div class="text-muted">{{ $product->updated_at?->format('h:i A') }}</div>
        </td>
        <td class="text-end pe-2 pe-sm-3">
            <div class="d-flex align-items-center justify-content-end gap-2 gap-md-2.5 flex-nowrap">
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm prod-action-btn" title="Edit Product">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>

                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Delete this product permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm prod-action-btn" title="Delete Product">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
