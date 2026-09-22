@extends('layouts.admin')

@section('title', 'Dress Size Master & Size Guide - ' . $siteName)

@section('content')
<div class="container-fluid px-0 py-1 py-md-2">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                <i class="fa-solid fa-ruler-combined text-warning me-2"></i>Dress Size Master & Size Guide
            </h1>
            <p class="text-muted small mb-0">Manage measurement masters for product categories and automatic size recommendations.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto">
            <button type="button" class="btn btn-warning rounded-pill px-3 py-2 font-semibold shadow-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#addCategoryModal" style="font-size: 0.82rem;">
                <i class="fa-solid fa-plus me-1.5"></i> Add Size Category
            </button>
            <div class="dropdown flex-fill flex-md-grow-0">
                <button class="btn btn-dark rounded-pill px-3 py-2 w-100 dropdown-toggle font-semibold shadow-sm" type="button" id="sizeGuideDownload" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-download me-1.5"></i> Download Guide
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                    <li><button class="dropdown-item py-2 small fw-semibold" type="button" onclick="exportSizeGuide('pdf')"><i class="fa-solid fa-file-pdf text-danger me-2"></i>PDF Document</button></li>
                    <li><button class="dropdown-item py-2 small fw-semibold" type="button" onclick="exportSizeGuide('image')"><i class="fa-solid fa-file-image text-success me-2"></i>PNG Image</button></li>
                </ul>
            </div>
            <a href="{{ route('products.size-guide') }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3 py-2 font-semibold flex-fill flex-md-grow-0 text-center" style="font-size: 0.82rem;">
                <i class="fa-solid fa-arrow-up-right-from-square me-1.5"></i> Client View
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>Please fix the errors below.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Category Selection Tabs / Dropdown -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-2.5 p-sm-3 p-md-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
                <label class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="fa-solid fa-layer-group text-primary me-2"></i>Select Size Category:
                </label>
                <!-- Category Tabs for Large Screen / Select Dropdown for Mobile -->
                <div class="w-100 d-md-none">
                    <select class="form-select rounded-pill" onchange="window.location.href=this.value">
                        @foreach($sizeMasters as $master)
                            <option value="{{ route('admin.size-guide.index', ['category_id' => $master->id]) }}" {{ ($selectedMaster && $selectedMaster->id === $master->id) ? 'selected' : '' }}>
                                {{ $master->name }} ({{ $master->rows->count() }} sizes)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Horizontal Category Nav Pill Tabs -->
            <div class="d-none d-md-flex flex-wrap gap-2">
                @foreach($sizeMasters as $master)
                    <a href="{{ route('admin.size-guide.index', ['category_id' => $master->id]) }}"
                       class="btn {{ ($selectedMaster && $selectedMaster->id === $master->id) ? 'btn-primary shadow-sm' : 'btn-light border text-dark' }} rounded-pill px-3.5 py-2 font-semibold text-decoration-none">
                        <i class="fa-solid fa-tag me-1.5 opacity-75"></i>{{ $master->name }}
                        <span class="badge {{ ($selectedMaster && $selectedMaster->id === $master->id) ? 'bg-white text-primary' : 'bg-secondary-subtle text-dark' }} rounded-circle ms-1">{{ $master->rows->count() }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($selectedMaster)
        <!-- Selected Category Details & Measurement Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 border">
            <div class="card-header bg-dark text-white p-3 p-md-4">
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.68rem;">
                                <i class="fa-solid fa-crown me-1"></i> MASTER CATEGORY
                            </span>
                        </div>
                        <h2 class="h4 fw-bold mb-0 text-white">{{ $selectedMaster->name }}</h2>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-sm-auto">
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 fw-semibold flex-fill flex-sm-grow-0 text-nowrap" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $selectedMaster->id }}" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-pen-to-square me-1 text-warning"></i> Edit Name
                        </button>
                        @if($sizeMasters->count() > 1)
                            <form action="{{ route('admin.size-masters.category.destroy', $selectedMaster->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this entire size category and its measurements?');" class="d-inline flex-fill flex-sm-grow-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold text-white w-100 text-nowrap" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-trash-can me-1 text-danger"></i> Delete Category
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body p-3 p-md-4" id="adminSizeGuideCard">
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
                    <div>
                        <h3 class="h6 fw-bold text-dark mb-1">
                            <i class="fa-solid fa-table me-1.5 text-warning"></i> Measurement Master Table (Inches)
                        </h3>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                            These values are used by product auto-fill suggestions and displayed in client size guides.
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-2 fw-semibold text-nowrap mt-2 mt-sm-0" data-bs-toggle="modal" data-bs-target="#addRowModal" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-plus me-1"></i> Add Size Row
                    </button>
                </div>

                <div class="table-responsive rounded-3 border">
                    <table class="table table-striped table-hover align-middle text-center mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3">Size Label</th>
                                <th class="py-3">Chest (C)</th>
                                <th class="py-3">Waist (W)</th>
                                <th class="py-3">Length (L)</th>
                                <th class="py-3" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selectedMaster->rows as $row)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary px-3 py-1.5 rounded-pill fs-6 fw-bold">{{ $row->size_label }}</span>
                                    </td>
                                    <td class="fw-bold text-primary fs-6">{{ $row->chest ?: '-' }}</td>
                                    <td class="fw-semibold text-secondary fs-6">{{ $row->waist ?: '-' }}</td>
                                    <td class="fw-semibold text-dark fs-6">{{ $row->length ?: '-' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 0;" data-bs-toggle="modal" data-bs-target="#editRowModal{{ $row->id }}" title="Edit Row">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <form action="{{ route('admin.size-masters.row.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Delete this size row?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Delete Row">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Row Modal -->
                                <div class="modal fade" id="editRowModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form action="{{ route('admin.size-masters.row.update', $row->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-dark text-white py-3">
                                                    <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Edit Size Row ({{ $row->size_label }})</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-muted">Size Label <span class="text-danger">*</span></label>
                                                        <input type="text" name="size_label" class="form-control rounded-3" value="{{ $row->size_label }}" required placeholder="e.g. M, L, XL, 3XL">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-muted">Chest (C)</label>
                                                        <input type="text" name="chest" class="form-control rounded-3" value="{{ $row->chest }}" placeholder='e.g. 38" or 36–38"'>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-muted">Waist (W)</label>
                                                        <input type="text" name="waist" class="form-control rounded-3" value="{{ $row->waist }}" placeholder='e.g. 36" or 32–34"'>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-muted">Length (L)</label>
                                                        <input type="text" name="length" class="form-control rounded-3" value="{{ $row->length }}" placeholder='e.g. 26–27"'>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-0 py-3">
                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-muted">No size rows added to this category yet. Click <strong>Add Size Row</strong> to add measurements.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div class="modal fade" id="editCategoryModal{{ $selectedMaster->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form action="{{ route('admin.size-masters.category.update', $selectedMaster->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-dark text-white py-3">
                            <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Rename Size Category</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" value="{{ $selectedMaster->name }}" required>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0 py-3">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Update Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Size Row Modal -->
        <div class="modal fade" id="addRowModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form action="{{ route('admin.size-masters.row.store', $selectedMaster->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-dark text-white py-3">
                            <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-plus text-warning me-2"></i>Add Size Row to {{ $selectedMaster->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Size Label <span class="text-danger">*</span></label>
                                <input type="text" name="size_label" class="form-control rounded-3" required placeholder="e.g. S, M, L, XL, XXL, 3XL">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Chest (C)</label>
                                <input type="text" name="chest" class="form-control rounded-3" placeholder='e.g. 36" or 34–36"'>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Waist (W)</label>
                                <input type="text" name="waist" class="form-control rounded-3" placeholder='e.g. 34" or 30–32"'>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Length (L)</label>
                                <input type="text" name="length" class="form-control rounded-3" placeholder='e.g. 25–26"'>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0 py-3">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success rounded-pill px-4">Add Row</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Add New Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form action="{{ route('admin.size-masters.category.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-dark text-white py-3">
                        <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-plus text-warning me-2"></i>Add New Size Master Category</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" required placeholder="e.g. Korean Crop Top, Ladies Overcoat">
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('partials.ladies_size_guide_export')

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportSizeGuide(type) {
    const element = document.getElementById('sizeGuideExport');
    const button = document.getElementById('sizeGuideDownload');
    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Preparing...';
    const finish = () => { button.disabled = false; button.innerHTML = original; };

    if (type === 'image') {
        html2canvas(element, { scale: 2, useCORS: true, backgroundColor: '#ffffff' }).then(canvas => {
            const link = document.createElement('a'); 
            link.download = '{{ Str::slug($siteName) }}-size-guide-{{ $selectedMaster ? $selectedMaster->slug : "master" }}.png'; 
            link.href = canvas.toDataURL('image/png'); 
            link.click(); 
            finish();
        }).catch(() => { alert('Could not create the image. Please try again.'); finish(); });
        return;
    }

    html2pdf().set({ 
        margin: 0.25, 
        filename: '{{ Str::slug($siteName) }}-size-guide-{{ $selectedMaster ? $selectedMaster->slug : "master" }}.pdf', 
        image: { type: 'jpeg', quality: 0.98 }, 
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' }, 
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' } 
    }).from(element).save().then(finish).catch(() => { alert('Could not create the PDF. Please try again.'); finish(); });
}
</script>
@endsection
