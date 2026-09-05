@extends('layouts.admin')

@section('title', 'Category Master - ' . $siteName . ' Admin')

@section('content')
<style>
    .cat-sticky-col {
        position: sticky;
        left: 0;
        z-index: 5;
        background-color: #ffffff !important;
        box-shadow: 2px 0 6px rgba(0, 0, 0, 0.06);
    }
    thead th.cat-sticky-col {
        z-index: 6;
        background-color: #f8f9fa !important;
    }
    .cat-img-wrapper {
        position: relative;
        width: 44px;
        height: 44px;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        display: inline-block;
        flex-shrink: 0;
    }
    .cat-img-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.85;
        transition: opacity 0.2s ease;
    }
    .cat-img-wrapper:hover .cat-img-overlay {
        opacity: 1;
        background: rgba(0, 0, 0, 0.52);
    }
    .cat-action-btn {
        width: 28px;
        height: 28px;
        font-size: 0.7rem;
    }
    @media (min-width: 576px) {
        .cat-action-btn {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }
    }
</style>

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
                        <th class="cat-sticky-col text-center" style="min-width: 105px;">Image / Category</th>
                        <th>Text Color</th>
                        <th>Products Count</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="cat-sticky-col text-center py-2.5 px-2">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    @php
                                        $hasBg = !empty($category->background_image);
                                        $imgSrc = $hasBg ? $category->background_image_url : '';
                                    @endphp
                                    <div class="cat-img-wrapper border shadow-xs mb-1" onclick="openCategoryPreview('{{ addslashes($imgSrc) }}', '{{ addslashes($category->name) }}')" title="Click to preview category image">
                                        @if($hasBg)
                                            <img src="{{ $imgSrc }}" alt="{{ $category->name }}" class="w-100 h-100" style="object-fit: cover;">
                                        @else
                                            <div class="w-100 h-100 bg-dark d-flex align-items-center justify-content-center text-white-50" style="font-size: 0.65rem;">
                                                Default
                                            </div>
                                        @endif
                                        <div class="cat-img-overlay">
                                            <i class="fa-solid fa-eye text-white" style="font-size: 0.72rem;"></i>
                                        </div>
                                    </div>
                                    <div class="fw-bold text-dark lh-sm text-truncate" style="font-size: 0.78rem; max-width: 95px;" title="{{ $category->name }}">
                                        {{ $category->name }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill border px-2.5 py-1" style="background-color: #f8f8f8; color: {{ $category->text_color }}; font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle me-1" style="color: {{ $category->text_color }};"></i> {{ $category->text_color }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.78rem;">{{ $category->products_count }}</span>
                            </td>
                            <td>
                                <form action="{{ route('admin.categories.toggle-status', $category->id) }}" method="POST" class="d-inline mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm badge bg-{{ $category->status === 'active' ? 'success' : 'danger' }} border-0 px-3 py-1.5" style="font-size: 0.74rem;">
                                        {{ ucfirst($category->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-2 pe-sm-3">
                                <div class="d-flex align-items-center justify-content-end gap-2 gap-sm-2.5 flex-nowrap">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm cat-action-btn" title="Edit Category">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm cat-action-btn" title="Delete Category" {{ $category->products_count > 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>
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

<!-- Category Image Preview Modal -->
<div class="modal fade" id="categoryPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <h5 class="modal-title fs-6 fw-bold text-truncate" id="categoryPreviewTitle">Category Image Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-light">
                <div id="categoryPreviewContent">
                    <img id="categoryPreviewImg" src="" alt="Category Image" class="img-fluid rounded-3 border shadow-sm" style="max-height: 75vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openCategoryPreview(imageUrl, title) {
        const modalEl = document.getElementById('categoryPreviewModal');
        const titleEl = document.getElementById('categoryPreviewTitle');
        const contentEl = document.getElementById('categoryPreviewContent');

        titleEl.textContent = title || 'Category Background Preview';

        if (imageUrl) {
            contentEl.innerHTML = `<img src="${imageUrl}" alt="${title}" class="img-fluid rounded-3 border shadow-sm" style="max-height: 75vh; object-fit: contain;">`;
        } else {
            contentEl.innerHTML = `
                <div class="p-4 bg-dark text-white rounded-3 border shadow-sm my-3 d-inline-block" style="min-width: 220px;">
                    <i class="fa-solid fa-image fa-3x text-secondary mb-2 opacity-50"></i>
                    <p class="mb-0 fw-bold small">Default Black Background Active</p>
                    <span class="small text-white-50" style="font-size: 0.72rem;">No custom background image uploaded</span>
                </div>`;
        }

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endsection
