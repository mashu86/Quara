@extends('layouts.admin')

@section('title', 'Booked Conflict Resolver - ' . $siteName . ' Admin')

@section('content')
<style>
.conflict-card {
    border-radius: 1rem;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.conflict-product-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}
@media (max-width: 576px) {
    .conflict-page-title {
        font-size: 1.1rem !important;
    }
    .conflict-table-text {
        font-size: 0.82rem !important;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 conflict-page-title">
            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Booked Conflict Resolver
        </h3>
        <p class="text-muted small mb-0">Audit and resolve discrepancies for products marked as Booked or Sold.</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark rounded-pill btn-sm text-nowrap">
        <i class="fa-solid fa-arrow-left me-1"></i>Back to Products
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card conflict-card mb-4">
    <div class="card-body p-3 p-md-4">
        <!-- Search & Filter Bar -->
        <form action="{{ route('admin.products.booked-conflicts') }}" method="GET" class="mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="Search by Product ID, Name, or Booked By..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-dark rounded-3 px-3">Search</button>
                    @if($search)
                        <a href="{{ route('admin.products.booked-conflicts') }}" class="btn btn-outline-secondary rounded-3 px-3">Reset</a>
                    @endif
                </div>
                <div class="col-auto ms-auto text-end">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                        Total Found: {{ $products->total() }}
                    </span>
                </div>
            </div>
        </form>

        <!-- Conflict Products Table -->
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0 conflict-table-text">
                <thead class="table-light border-bottom">
                    <tr>
                        <th style="width: 70px;">Image</th>
                        <th style="width: 100px;">Primary Key (ID)</th>
                        <th>Product Details</th>
                        <th>Stock & Booking Status</th>
                        <th class="text-center" style="width: 160px;">Operation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $totalStock = $product->sizes->sum('stock');
                            $primaryImg = $product->primaryImage ? asset($product->primaryImage->image_path) : asset('images/placeholder.jpg');
                        @endphp
                        <tr>
                            <td>
                                <img src="{{ $primaryImg }}" alt="{{ $product->name }}" class="conflict-product-img border">
                            </td>
                            <td>
                                <span class="badge bg-secondary font-monospace fw-bold fs-6">#{{ $product->id }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1">{{ $product->name }}</div>
                                <div class="small text-muted">
                                    Categories: {{ $product->categories->pluck('name')->implode(', ') ?: 'Uncategorized' }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    <span class="badge {{ $totalStock > 0 ? 'bg-info text-dark' : 'bg-danger' }}">
                                        Stock: {{ $totalStock }} pcs
                                    </span>

                                    @if($product->is_out_of_stock)
                                        <span class="badge bg-warning text-dark fw-bold">
                                            <i class="fa-solid fa-lock me-1"></i>Booked Status ON
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Booked Status OFF</span>
                                    @endif

                                    @if($product->booked_by)
                                        <span class="badge bg-dark text-white">
                                            Booked By: {{ $product->booked_by }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" 
                                        class="btn btn-warning btn-sm rounded-3 fw-bold text-nowrap shadow-sm"
                                        onclick="openResolveModal('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $primaryImg }}', '{{ addslashes($product->booked_by ?? '') }}')">
                                    <i class="fa-solid fa-wrench me-1"></i> Resolve Conflict
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-check fs-2 text-success mb-2 d-block"></i>
                                No booked product conflicts found!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Resolution Modal -->
<div class="modal fade" id="resolveConflictModal" tabindex="-1" aria-labelledby="resolveModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form id="resolveConflictForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold fs-6" id="resolveModalTitle">
                        <i class="fa-solid fa-wrench text-warning me-2"></i>Resolve Product Conflict
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <!-- Fetched Product Info Header -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border mb-3">
                        <img id="modalProductImg" src="" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                        <div>
                            <span class="badge bg-secondary font-monospace mb-1" id="modalProductId">#0</span>
                            <h6 class="fw-bold mb-0 text-dark" id="modalProductName">Product Name</h6>
                        </div>
                    </div>

                    <!-- Question Prompt -->
                    <div class="alert alert-warning p-2.5 rounded-3 mb-3 border-0 small fw-bold text-dark">
                        <i class="fa-solid fa-circle-question me-1 text-dark"></i> Select the issue to resolve for this product:
                    </div>

                    <!-- Option 1 -->
                    <div class="form-check p-3 rounded-3 border mb-2 cursor-pointer border-2" id="optionCard1" onclick="selectOption('already_sold')">
                        <input class="form-check-input" type="radio" name="resolution" id="resOption1" value="already_sold" checked onchange="handleOptionChange()">
                        <label class="form-check-label fw-bold text-dark cursor-pointer ms-1" for="resOption1">
                            1) Already sold, but still displaying as Booked
                        </label>
                        <div class="small text-muted ms-4 mt-1">
                            Clears Booked status (<code class="text-danger">is_out_of_stock = 0</code> and removes <code class="text-danger">booked_by</code>).
                        </div>
                    </div>

                    <!-- Option 2 -->
                    <div class="form-check p-3 rounded-3 border cursor-pointer border-2" id="optionCard2" onclick="selectOption('not_sold_keep_booked')">
                        <input class="form-check-input" type="radio" name="resolution" id="resOption2" value="not_sold_keep_booked" onchange="handleOptionChange()">
                        <label class="form-check-label fw-bold text-dark cursor-pointer ms-1" for="resOption2">
                            2) Not sold yet, mark / keep in Booked
                        </label>
                        <div class="small text-muted ms-4 mt-1">
                            Maintains product in Booked state (<code class="text-success">is_out_of_stock = 1</code> and keeps <code class="text-success">booked_by</code>).
                        </div>
                        
                        <!-- Booked By Input for Option 2 -->
                        <div id="modalBookedByContainer" class="ms-4 mt-2 d-none">
                            <label class="form-label fw-bold small mb-1">Booked By Details <span class="text-danger">*</span></label>
                            <input type="text" name="booked_by" id="modalBookedByInput" class="form-control form-control-sm rounded-3" placeholder="Enter customer name / phone">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-3 fw-bold px-4">
                        <i class="fa-solid fa-check me-1"></i> Apply Resolution
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResolveModal(id, name, imgUrl, currentBookedBy) {
    document.getElementById('modalProductId').innerText = '#' + id;
    document.getElementById('modalProductName').innerText = name;
    document.getElementById('modalProductImg').src = imgUrl;
    document.getElementById('modalBookedByInput').value = currentBookedBy || 'Booked Customer';

    // Set Form Action URL
    const form = document.getElementById('resolveConflictForm');
    form.action = "{{ url('admin/products') }}/" + id + "/resolve-conflict";

    // Default select option 1
    document.getElementById('resOption1').checked = true;
    handleOptionChange();

    // Show Modal
    const modal = new bootstrap.Modal(document.getElementById('resolveConflictModal'));
    modal.show();
}

function selectOption(val) {
    if (val === 'already_sold') {
        document.getElementById('resOption1').checked = true;
    } else {
        document.getElementById('resOption2').checked = true;
    }
    handleOptionChange();
}

function handleOptionChange() {
    const isOpt2 = document.getElementById('resOption2').checked;
    const container = document.getElementById('modalBookedByContainer');
    const input = document.getElementById('modalBookedByInput');

    const card1 = document.getElementById('optionCard1');
    const card2 = document.getElementById('optionCard2');

    if (isOpt2) {
        container.classList.remove('d-none');
        if (input) input.required = true;
        card2.classList.add('border-warning', 'bg-light');
        card1.classList.remove('border-warning', 'bg-light');
    } else {
        container.classList.add('d-none');
        if (input) input.required = false;
        card1.classList.add('border-warning', 'bg-light');
        card2.classList.remove('border-warning', 'bg-light');
    }
}
</script>
@endsection
