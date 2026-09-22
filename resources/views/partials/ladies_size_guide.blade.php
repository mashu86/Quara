<div class="p-3 p-md-5 bg-white" id="{{ $guideId ?? 'sizeGuideCard' }}">
    <!-- Brand Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between border-bottom pb-3 mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="rounded-3 shadow-sm object-fit-contain" style="max-height: 48px; max-width: 150px;">
            <div>
                <h2 class="h5 fw-bold mb-0 text-dark">{{ $siteName }}</h2>
                <span class="badge bg-gold-subtle text-dark fw-bold text-uppercase px-2.5 py-1 rounded-pill" style="font-size: 0.68rem; background: rgba(212, 175, 55, 0.15); color: #8b6508;">
                    <i class="fa-solid fa-crown me-1 text-warning"></i> Size Guide Master
                </span>
            </div>
        </div>
        <div class="text-start text-sm-end text-muted small mt-1 mt-sm-0">
            <div class="fw-semibold text-dark" style="font-size: 0.78rem;">
                <i class="fa-solid fa-ruler-combined me-1 text-warning"></i> Garment Measurement Guide
            </div>
            <div class="text-secondary" style="font-size: 0.72rem;">All measurements are in finished garment dimensions</div>
        </div>
    </div>

    @if(isset($sizeMasters) && $sizeMasters->isNotEmpty())
        <!-- Mobile Dropdown Selector (Visible on Small Screens) -->
        <div class="d-block d-md-none mb-3">
            <label class="form-label fw-bold text-dark mb-1 small text-uppercase tracking-wider">
                <i class="fa-solid fa-sliders me-1 text-warning"></i> Select Category:
            </label>
            <select class="form-select rounded-3 border-dark-subtle fw-semibold" onchange="window.location.href=this.value" style="font-size: 0.85rem;">
                @foreach($sizeMasters as $m)
                    <option value="{{ request()->fullUrlWithQuery(['category_id' => $m->id]) }}" {{ (isset($selectedMaster) && $selectedMaster->id === $m->id) ? 'selected' : '' }}>
                        {{ $m->name }} ({{ $m->rows->count() }} sizes)
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Horizontal Scrollable Category Pills (Desktop & Tablet) -->
        <div class="mb-4">
            <div class="d-none d-md-block fw-bold text-dark mb-2 small text-uppercase tracking-wider">
                <i class="fa-solid fa-layer-group me-1 text-warning"></i> Product Size Category:
            </div>
            <div class="d-flex flex-nowrap overflow-x-auto pb-2 gap-2 scrollbar-hidden" style="-webkit-overflow-scrolling: touch;">
                @foreach($sizeMasters as $m)
                    @php $isActive = (isset($selectedMaster) && $selectedMaster->id === $m->id); @endphp
                    <a href="{{ request()->fullUrlWithQuery(['category_id' => $m->id]) }}" 
                       class="btn text-nowrap rounded-pill px-3 py-2 btn-sm fw-semibold text-decoration-none transition-all {{ $isActive ? 'btn-dark text-white shadow-sm border-dark' : 'btn-light text-dark border bg-white hover-bg-light' }}"
                       style="font-size: 0.80rem; border-width: 1.5px;">
                        @if($isActive)<i class="fa-solid fa-circle-check me-1.5 text-warning"></i>@endif
                        {{ $m->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if(isset($selectedMaster) && $selectedMaster)
        <section class="mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-circle bg-light border text-warning d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-shirt"></i>
                    </span>
                    <div>
                        <h3 class="h6 fw-bold text-dark mb-0">{{ $selectedMaster->name }}</h3>
                        <span class="text-muted" style="font-size: 0.72rem;">Finished garment size chart</span>
                    </div>
                </div>

                <!-- Interactive Unit Toggle (Inch / Cm) -->
                <div class="btn-group btn-group-sm rounded-pill p-0.5 bg-light border" role="group" aria-label="Unit Switcher">
                    <input type="radio" class="btn-check" name="sg_unit_toggle" id="sg_unit_inch" value="inch" checked onchange="toggleSizeGuideUnit('inch')">
                    <label class="btn btn-outline-dark rounded-pill py-1 px-3 border-0 small fw-bold" for="sg_unit_inch" style="font-size: 0.72rem;">INCH</label>
                    <input type="radio" class="btn-check" name="sg_unit_toggle" id="sg_unit_cm" value="cm" onchange="toggleSizeGuideUnit('cm')">
                    <label class="btn btn-outline-dark rounded-pill py-1 px-3 border-0 small fw-bold" for="sg_unit_cm" style="font-size: 0.72rem;">CM</label>
                </div>
            </div>

            <div class="table-responsive rounded-3 border shadow-sm bg-white">
                <table class="table table-striped table-hover align-middle text-center mb-0" id="sgMasterTable">
                    <thead class="table-dark">
                        <tr style="font-size: 0.78rem;">
                            <th class="py-2.5 px-2 px-sm-3">Size</th>
                            <th class="py-2.5 px-2 px-sm-3">Chest (<span class="sg-unit-label">in</span>)</th>
                            <th class="py-2.5 px-2 px-sm-3">Waist (<span class="sg-unit-label">in</span>)</th>
                            <th class="py-2.5 px-2 px-sm-3">Length (<span class="sg-unit-label">in</span>)</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.82rem;">
                        @forelse($selectedMaster->rows as $r)
                            <tr>
                                <td class="py-2.5 px-2">
                                    <span class="badge bg-dark text-warning border border-warning-subtle px-2.5 py-1.5 rounded-2 font-monospace fw-bold" style="font-size: 0.78rem;">
                                        {{ $r->size_label }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark sg-chest" data-inch="{{ $r->chest }}">{{ $r->chest ?: '-' }}</td>
                                <td class="fw-semibold text-secondary sg-waist" data-inch="{{ $r->waist }}">{{ $r->waist ?: '-' }}</td>
                                <td class="fw-semibold text-secondary sg-length" data-inch="{{ $r->length }}">{{ $r->length ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-muted small">No measurements added to this size category yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <div class="alert alert-info border-0 rounded-3 p-3 small">
            <i class="fa-solid fa-info-circle me-2"></i>Select a size category above to view finished garment measurements.
        </div>
    @endif

    <!-- Visual Measurement Guide Cards -->
    <section class="rounded-3 border bg-light p-3 p-md-4 mt-4">
        <h4 class="h6 fw-bold mb-3 text-dark d-flex align-items-center gap-2">
            <i class="fa-solid fa-ruler text-warning"></i> How to Measure Your Garment
        </h4>
        <div class="row g-2.5 g-md-3">
            <div class="col-12 col-md-4">
                <div class="p-2.5 p-md-3 bg-white rounded-3 border h-100 shadow-sm">
                    <div class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1.5">
                        <span class="badge bg-primary text-white rounded-circle p-1" style="width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem;">C</span>
                        Chest / Bust (C)
                    </div>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">
                        Lay garment flat, measure armpit-to-armpit, and multiply by 2 for full circumference.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-2.5 p-md-3 bg-white rounded-3 border h-100 shadow-sm">
                    <div class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1.5">
                        <span class="badge bg-success text-white rounded-circle p-1" style="width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem;">W</span>
                        Waist (W)
                    </div>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">
                        Measure across the narrowest waist area flat, and multiply by 2 for full circumference.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-2.5 p-md-3 bg-white rounded-3 border h-100 shadow-sm">
                    <div class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1.5">
                        <span class="badge bg-dark text-warning rounded-circle p-1" style="width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem;">L</span>
                        Length (L)
                    </div>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">
                        Measure straight from the highest shoulder seam down to the bottom hemline.
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-2.5 pt-2 border-top text-muted small d-flex align-items-center gap-2" style="font-size: 0.73rem;">
            <i class="fa-solid fa-lightbulb text-warning"></i>
            <span><strong>Fitting Tip:</strong> If your measurements fall between two size levels, choose the larger size for a relaxed, comfortable fit.</span>
        </div>
    </section>
</div>

<script>
function convertInchStringToCmSG(str) {
    if (!str || str.trim() === '-' || str.trim() === '') return '-';
    let clean = str.replace(/["″]/g, '').trim();
    if (clean.includes('–') || clean.includes('-')) {
        let parts = clean.split(/[–-]/);
        if (parts.length === 2) {
            let n1 = parseFloat(parts[0]);
            let n2 = parseFloat(parts[1]);
            if (!isNaN(n1) && !isNaN(n2)) {
                let cm1 = Math.round(n1 * 2.54);
                let cm2 = Math.round(n2 * 2.54);
                return `${cm1}–${cm2} cm`;
            }
        }
    }
    let num = parseFloat(clean);
    if (!isNaN(num)) {
        let cm = Math.round(num * 2.54);
        return `${cm} cm`;
    }
    return str;
}

function toggleSizeGuideUnit(unit) {
    const table = document.getElementById('sgMasterTable');
    if (!table) return;

    const unitLabels = table.querySelectorAll('.sg-unit-label');
    unitLabels.forEach(el => el.textContent = unit);

    const cells = table.querySelectorAll('.sg-chest, .sg-waist, .sg-length');
    cells.forEach(cell => {
        const inchVal = cell.getAttribute('data-inch');
        if (!inchVal || inchVal.trim() === '-' || inchVal.trim() === '') {
            cell.textContent = '-';
            return;
        }
        if (unit === 'cm') {
            cell.textContent = convertInchStringToCmSG(inchVal);
        } else {
            cell.textContent = inchVal.includes('"') ? inchVal : (inchVal + '"');
        }
    });
}
</script>
