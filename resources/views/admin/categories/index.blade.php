@extends('layouts.admin')

@section('title', 'Category Master - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
    <div>
        <h4 class="fw-bold mb-0" style="font-size: 0.95rem;">Category Master</h4>
        <p class="text-muted small mb-0 d-none d-sm-block">Organize and manage apparel categories</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-warning rounded-3 fw-bold btn-sm px-2.5 px-sm-3 py-1 text-nowrap" style="font-size: 0.78rem; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Add New Category">
        <i class="fa-solid fa-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Add Category</span>
    </a>
</div>

<!-- Search & Filters -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.categories.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control rounded-3" placeholder="Search category name..." value="{{ request()->search }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select rounded-3">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request()->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request()->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select rounded-3">
                    <option value="newest" {{ request()->sort === 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request()->sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="name" {{ request()->sort === 'name' ? 'selected' : '' }}>Name A-Z</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100 rounded-3">Filter</button>
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
                        <th>Background Preview</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Text Color</th>
                        <th>Products Count</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                @if($category->background_image)
                                    <img src="{{ $category->background_image_url }}" alt="{{ $category->name }}" class="rounded-3 border" style="width: 70px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="rounded-3 border bg-black" style="width: 70px; height: 50px;" title="Default black background"></div>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">{{ $category->name }}</td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>
                                <span class="badge rounded-pill border px-3 py-1" style="background-color: #f8f8f8; color: {{ $category->text_color }};">
                                    <i class="fa-solid fa-circle me-1" style="color: {{ $category->text_color }};"></i> {{ $category->text_color }}
                                </span>
                            </td>
                            <td><span class="badge bg-secondary rounded-pill px-3">{{ $category->products_count }} Products</span></td>
                            <td>
                                <form action="{{ route('admin.categories.toggle-status', $category->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm badge bg-{{ $category->status === 'active' ? 'success' : 'danger' }} border-0 px-3 py-2">
                                        {{ ucfirst($category->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Category">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Delete Category" {{ $category->products_count > 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $categories->links() }}
    </div>
</div>
@endsection
