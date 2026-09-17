<div class="p-3 p-md-5 bg-white" id="{{ $guideId ?? 'sizeGuideCard' }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between border-bottom pb-4 mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="rounded-3 shadow-sm object-fit-contain" style="max-height: 55px; max-width: 180px;">
            <div>
                <h2 class="h4 fw-bold mb-0 text-dark">{{ $siteName }}</h2>
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-2.5 py-1 rounded-pill small">Ladieswear Size Guide</span>
            </div>
        </div>
        <div class="text-md-end text-muted small">
            <div><i class="fa-solid fa-ruler-combined me-1"></i> India / Kerala fit reference</div>
            <div>All measurements are in inches</div>
        </div>
    </div>

    <div class="alert alert-info border border-info-subtle rounded-3 p-3 mb-4">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-circle-info fs-5 text-info mt-0.5"></i>
            <div class="small"><strong>Choose by your body bust first.</strong> Measure around the fullest part of your bust while wearing light clothing, keeping the tape comfortably level. The garment-chest values below are the finished clothing measurement, so they are larger than the body bust for comfort and movement.</div>
        </div>
    </div>

    <section class="mb-5">
        <h3 class="h5 fw-bold text-dark mb-2">1. Find your ladieswear size from your body measurement</h3>
        <p class="small text-muted mb-3">Use the body-bust range below to select your usual Indian alpha size. If you fall between sizes, choose the larger size for a relaxed fit.</p>
        <div class="table-responsive rounded-3 border">
            <table class="table table-striped table-hover align-middle text-center mb-0">
                <thead class="table-dark"><tr><th class="py-3">Indian size</th><th class="py-3">Your body bust</th><th class="py-3">Your body waist</th></tr></thead>
                <tbody>
                    @foreach ([
                        ['XS', '32–33&quot;', '26–27&quot;'], ['S', '34–35&quot;', '28–29&quot;'], ['M', '36–37&quot;', '30–31&quot;'],
                        ['L', '38–39&quot;', '32–33&quot;'], ['XL', '40–41&quot;', '34–35&quot;'], ['XXL', '42–43&quot;', '36–37&quot;'],
                        ['3XL', '44–45&quot;', '38–39&quot;'], ['4XL', '46–47&quot;', '40–41&quot;'], ['5XL', '48–49&quot;', '42–43&quot;'], ['6XL', '50–51&quot;', '44–45&quot;'],
                    ] as [$size, $bust, $waist])
                        <tr>
                            <td><span class="badge {{ in_array($size, ['M', 'L']) ? 'bg-primary text-white' : 'bg-secondary-subtle text-dark' }} px-3 py-1.5 rounded-pill fs-6">{{ $size }}</span></td>
                            <td class="fw-bold text-primary">{!! $bust !!}</td>
                            <td>{!! $waist !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-5">
        <h3 class="h5 fw-bold text-dark mb-2">2. Finished garment chest guide for each style</h3>
        <p class="small text-muted mb-3">For product listings, measure the actual garment around the chest. A crop top is closer-fitting; an overcoat needs extra room for layering. Values are nominal; a ±1&quot; production tolerance is normal.</p>
        <div class="table-responsive rounded-3 border">
            <table class="table table-striped table-hover align-middle text-center mb-0 small">
                <thead class="table-dark"><tr><th class="py-3">Size</th><th class="py-3">Korean crop top<br><span class="fw-normal">fitted</span></th><th class="py-3">Korean top<br><span class="fw-normal">regular</span></th><th class="py-3">Normal top<br><span class="fw-normal">regular</span></th><th class="py-3">Ladies shirt<br><span class="fw-normal">regular</span></th><th class="py-3">Overcoat / jacket<br><span class="fw-normal">layering fit</span></th></tr></thead>
                <tbody>
                    @foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL', '6XL'] as $index => $size)
                        <tr>
                            <td class="fw-bold">{{ $size }}</td>
                            <td class="text-primary fw-semibold">{{ 34 + ($index * 2) }}&quot;</td>
                            <td>{{ 36 + ($index * 2) }}&quot;</td>
                            <td>{{ 36 + ($index * 2) }}&quot;</td>
                            <td>{{ 36 + ($index * 2) }}&quot;</td>
                            <td class="fw-semibold">{{ 38 + ($index * 2) }}&quot;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-3 border bg-light p-3 p-md-4">
        <h3 class="h6 fw-bold mb-3"><i class="fa-solid fa-clipboard-check text-success me-2"></i>How to get the right fit</h3>
        <ol class="small text-secondary mb-0 ps-3">
            <li class="mb-2">For customers: use the <strong>body bust</strong> chart, not a flat garment width.</li>
            <li class="mb-2">For product entry: lay the garment flat, measure armpit-to-armpit, then multiply by 2 to get its <strong>finished garment chest</strong>.</li>
            <li class="mb-2">If the supplier provides a size label or chart, use that label/chart even when it differs from this reference.</li>
            <li>For a bust or waist between two sizes, select the larger size; fitted Korean crop styles should feel close but never restrictive.</li>
        </ol>
    </section>
</div>
