@foreach($products as $product)
    @php $totalStock = $product->sizes->sum('stock'); @endphp
    <tr>
        <td>
            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="rounded-3 border" style="width: 55px; height: 70px; object-fit: cover;">
        </td>
        <td>
            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">{{ $product->name }}</h6>
            <span class="text-muted small" style="font-size: 0.72rem;">Slug: {{ $product->slug }}</span>
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
                    <span class="badge {{ $pSize->stock > 0 ? 'bg-dark' : 'bg-danger' }}" style="font-size: 0.68rem;" title="Size {{ $pSize->size }}">
                        {{ $pSize->size }}: {{ $pSize->stock }}
                    </span>
                @endforeach
            </div>
            <div class="small fw-bold mt-1 {{ $totalStock > 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.72rem;">
                Total: {{ $totalStock }} pcs
            </div>
        </td>
        <td>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input out-of-stock-toggle" type="checkbox" role="switch"
                       id="outOfStockToggle_{{ $product->id }}"
                       data-product-id="{{ $product->id }}"
                       data-url="{{ route('admin.products.toggle-out-of-stock', $product->id) }}"
                       {{ $product->is_out_of_stock ? 'checked' : '' }}
                       style="cursor: pointer; width: 2.3em; height: 1.2em;">
                <label class="form-check-label small fw-bold ms-1 {{ $product->is_out_of_stock ? 'text-danger' : 'text-success' }}"
                       id="outOfStockLabel_{{ $product->id }}"
                       for="outOfStockToggle_{{ $product->id }}" style="cursor: pointer; font-size: 0.78rem;">
                    {{ $product->is_out_of_stock ? 'Out of Stock' : 'Normal Stock' }}
                </label>
            </div>
        </td>
        <td>
            <span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}" style="font-size: 0.72rem;">
                {{ ucfirst($product->status) }}
            </span>
        </td>
        <td class="text-end pe-3">
            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Product">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>

                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Delete this product permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Delete Product">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
