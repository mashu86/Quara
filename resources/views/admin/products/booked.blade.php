@extends('layouts.admin')

@section('title', 'Booked Products - ' . $siteName)

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-user-lock text-warning me-2"></i>Booked Products</h4>
        <p class="text-muted small mb-0">View current product bookings and release them when needed.</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 align-self-start">
        <i class="fa-solid fa-shirt me-1"></i> Product Master
    </a>
</div>

<form action="{{ route('admin.products.booked') }}" method="GET" class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <label for="booked-product-search" class="form-label small fw-semibold">Search product or booked by</label>
        <div class="d-flex flex-wrap gap-2">
            <input id="booked-product-search" type="search" name="search" value="{{ $search }}" class="form-control flex-grow-1 w-auto" placeholder="Product name or booking name / phone">
            <button class="btn btn-dark px-3" type="submit">Search</button>
            @if($search !== '')
                <a href="{{ route('admin.products.booked') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold fs-6">Current Bookings</h5>
        <span class="badge bg-dark rounded-pill">{{ $products->total() }} products</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-nowrap">
                <tr>
                    <th class="ps-3">Product</th>
                    <th>Booked By</th>
                    <th>Size / Stock</th>
                    <th>Price</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="ps-3" style="min-width: 200px;">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $product->primary_image_url }}" alt="" loading="lazy" width="44" height="54" class="rounded flex-shrink-0" style="object-fit: cover;">
                                <div>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="fw-semibold text-dark text-decoration-none">{{ $product->name }}</a>
                                    <div class="small text-muted">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="min-width: 140px;">
                            <span class="fw-semibold">{{ $product->booked_by }}</span>
                            <div class="small text-warning-emphasis"><i class="fa-solid fa-lock me-1"></i>Booked</div>
                        </td>
                        <td style="min-width: 120px;">
                            @forelse($product->sizes as $size)
                                <span class="badge bg-light text-dark border mb-1">{{ $size->size }}: {{ $size->stock }}</span>
                            @empty
                                <span class="small text-muted">No sizes recorded</span>
                            @endforelse
                        </td>
                        <td class="text-nowrap fw-semibold">₹{{ number_format($product->final_price, 2) }}</td>
                        <td class="text-end pe-3">
                            <form action="{{ route('admin.products.toggle-out-of-stock', $product) }}" method="POST" onsubmit="return confirm('Unbook this product? Its booking will be cleared and normal stock availability will apply.');">
                                @csrf
                                <input type="hidden" name="is_out_of_stock" value="0">
                                <input type="hidden" name="booked_by" value="">
                                <button type="submit" class="btn btn-sm btn-outline-success text-nowrap rounded-pill px-3">
                                    <i class="fa-solid fa-lock-open me-1"></i> Unbook
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            {{ $search !== '' ? 'No bookings match your search.' : 'No products are currently booked.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white pt-3">{{ $products->links() }}</div>
    @endif
</div>
@endsection
