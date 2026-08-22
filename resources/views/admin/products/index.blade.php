@extends('layouts.admin')

@section('title', 'Product Master - QUARA WALDROP Admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Product Master</h3>
        <p class="text-muted small mb-0">Manage product inventory, pricing, discounts and variants</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-warning rounded-pill fw-bold mt-3 mt-md-0 px-4">
        <i class="fa-solid fa-plus me-1"></i> Add New Product
    </a>
</div>

<!-- Search & Filters -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control rounded-3" placeholder="Search product name..." value="{{ request()->search }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select rounded-3">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select rounded-3">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request()->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request()->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock_status" class="form-select rounded-3">
                    <option value="">All Stock</option>
                    <option value="in_stock" {{ request()->stock_status === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ request()->stock_status === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select rounded-3" onchange="this.form.submit()">
                    <option value="newest" {{ request()->sort === 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request()->sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="price_low" {{ request()->sort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ request()->sort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Original Price</th>
                        <th>Discount</th>
                        <th>Selling Price</th>
                        <th>Size-wise Stock</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php $totalStock = $product->sizes->sum('stock'); @endphp
                        <tr>
                            <td>
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="rounded-3 border" style="width: 55px; height: 70px; object-fit: cover;">
                            </td>
                            <td>
                                <h6 class="fw-bold text-dark mb-0">{{ $product->name }}</h6>
                                <span class="text-muted small">Slug: {{ $product->slug }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $product->category->name }}</span></td>
                            <td>₹{{ number_format($product->price, 2) }}</td>
                            <td>
                                @if($product->discount_type === 'fixed')
                                    <span class="badge bg-danger">₹{{ number_format($product->discount_value, 2) }} OFF</span>
                                @elseif($product->discount_type === 'percentage')
                                    <span class="badge bg-danger">{{ (int)$product->discount_value }}% OFF</span>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td class="fw-bold text-gold fs-6">₹{{ number_format($product->final_price, 2) }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" style="max-width: 180px;">
                                    @foreach($product->sizes as $pSize)
                                        <span class="badge {{ $pSize->stock > 0 ? 'bg-dark' : 'bg-danger' }}" title="Size {{ $pSize->size }}">
                                            {{ $pSize->size }}: {{ $pSize->stock }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="small fw-bold mt-1 {{ $totalStock > 0 ? 'text-success' : 'text-danger' }}">
                                    Total: {{ $totalStock }} pcs
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-dark me-1"><i class="fa-solid fa-pen-to-square"></i> Edit</a>

                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No products found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $products->links() }}
    </div>
</div>
@endsection
