@extends('layouts.admin')
@section('title','Connect Receivers to Orders')
@section('content')
<style>
    .ba-board{position:relative;display:grid;grid-template-columns:minmax(0,1fr) 140px minmax(0,1fr);align-items:start}
    .ba-col-title{font-weight:600;margin-bottom:.5rem}
    .ba-item{position:relative;border:1px solid #dee2e6;border-radius:.5rem;padding:.5rem .75rem;margin-bottom:.5rem;background:#fff;cursor:pointer;min-height:64px}
    .ba-item small{display:block;color:#6c757d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .ba-item.is-connected{border-color:#198754;background:#f1faf4}
    .ba-item.is-selected{border-color:#0d6efd;box-shadow:0 0 0 .2rem rgba(13,110,253,.25)}
    .ba-item.is-disabled{opacity:.55;cursor:not-allowed;background:#f8f9fa}
    .ba-dot{position:absolute;top:50%;width:12px;height:12px;margin-top:-6px;border-radius:50%;background:#adb5bd;border:2px solid #fff}
    .ba-left .ba-dot{right:-7px;width:16px;height:16px;margin-top:-8px;cursor:crosshair;touch-action:none}.ba-right .ba-dot{left:-7px}
    .ba-left .ba-item:not(.is-disabled) .ba-dot{background:#0d6efd}
    .ba-left .ba-item.is-connected .ba-dot{background:#198754}
    .ba-right .ba-item.is-drop{border-color:#0d6efd;background:#eef4ff}
    .ba-item.is-connected .ba-dot{background:#198754}
    .ba-scroll{max-height:calc(100vh - 290px);min-height:300px;overflow-y:auto;padding-right:10px;padding-left:10px}
    .ba-lines{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;overflow:visible}
    .ba-lines g{pointer-events:auto;cursor:pointer}
    .ba-lines g:hover path.ba-line{stroke:#dc3545}
    @media (max-width:767px){.ba-board{grid-template-columns:minmax(0,1fr) 40px minmax(0,1fr)}}
</style>
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h3 class="mb-0">Connect Receivers to Orders</h3>
        <span class="badge bg-primary fs-6"><span id="baCount">0</span> / {{ count($rows) }} connected</span>
    </div>
    <div class="alert alert-info py-2 small">Same names are connected automatically. Click a line to remove it. To connect manually, click an Excel receiver on the left, then an order on the right. Only connected pairs are saved.</div>
    <div class="row g-2 align-items-center mb-3">
        <div class="col-md-5">
            <input type="search" id="baFilter" class="form-control" placeholder="Filter both lists by name, address, PIN or order number" autocomplete="off">
        </div>
        <div class="col-auto"><button type="button" id="baQuick" class="btn btn-success" disabled>Connect (Enter)</button></div>
        <div class="col-auto"><button type="button" id="baFilterClear" class="btn btn-outline-secondary">Clear</button></div>
        <div class="col small text-primary fw-semibold" id="baHint">Click a receiver on the left (or drag its blue point) and then an order on the right.</div>
    </div>

    <div class="ba-board" id="baBoard">
        <div class="ba-left">
            <div class="ba-col-title">Excel Receiver ({{ count($rows) }})</div>
            <div class="ba-scroll">
            @forelse($rows as $index => $r)
                <div class="ba-item {{ $r['issue'] ? 'is-disabled' : '' }}" data-row="{{ $index }}" @if($r['issue']) title="{{ $r['issue'] }}" @endif>
                    <strong>{{ $r['name'] ?: '—' }}</strong>
                    @if($r['issue'])<span class="badge bg-danger ms-1">{{ $r['issue'] }}</span>@endif
                    <small>{{ $r['address'] }}{{ $r['pin'] ? ' - '.$r['pin'] : '' }}</small>
                    <small>Article: {{ $r['article'] ?: '—' }} · Tariff: ₹{{ $r['charge'] }}</small>
                    <span class="ba-dot"></span>
                </div>
            @empty
                <div class="text-muted">No rows in the file.</div>
            @endforelse
            </div>
        </div>
        <div></div>
        <div class="ba-right">
            <div class="ba-col-title">Orders ({{ $orders->count() }})</div>
            <div class="ba-scroll">
            @foreach($orders as $o)
                @php($generated = $o->shipment || $o->tracking_number)
                <div class="ba-item {{ $generated ? 'is-disabled' : '' }}" data-order="{{ $o->id }}" @if($generated) title="Tracking already generated" @endif>
                    <strong>{{ $o->customer_name }}</strong> <span class="text-muted small">#{{ $o->order_number }}</span>
                    @if($generated)<span class="badge bg-warning text-dark ms-1">Already generated</span>@endif
                    <small>{{ collect([$o->area, $o->city, $o->pin_code])->filter()->implode(', ') }}</small>
                    <small>Weight: {{ rtrim(rtrim(number_format($o->website_weight, 2, '.', ''), '0'), '.') }} g</small>
                    <span class="ba-dot"></span>
                </div>
            @endforeach
            </div>
        </div>
        <svg class="ba-lines" id="baLines"></svg>
    </div>

    <form method="post" action="{{ route('admin.bulk-article.save') }}" id="baForm" class="d-flex justify-content-between mt-3">
        @csrf
        <a href="{{ route('admin.bulk-article.upload') }}" class="btn btn-outline-secondary">Back / Correct Mapping</a>
        <div id="baInputs"></div>
        <div class="text-end">
            <button class="btn btn-success" id="baSubmit">Proceed Bulk Tracking Number</button>
            <div class="small text-muted mt-1">Rows left unconnected are kept under "Not mapped from order".</div>
        </div>
    </form>
</div>
<script>
(() => {
    const pairs = new Map(Object.entries(@json((object) $pairs)).map(([row, order]) => [String(row), String(order)]));
    const board = document.getElementById('baBoard'), svg = document.getElementById('baLines');
    const leftOf = row => board.querySelector(`.ba-left [data-row="${row}"]`);
    const rightOf = order => board.querySelector(`.ba-right [data-order="${order}"]`);
    let selectedRow = null;

    function inView(el) {
        if (el.hidden) return false;
        const box = el.closest('.ba-scroll').getBoundingClientRect(), r = el.getBoundingClientRect();
        const mid = r.top + r.height / 2;
        return mid >= box.top && mid <= box.bottom;
    }

    // One filter for both lists; connections are kept when the filter is cleared.
    const filter = document.getElementById('baFilter'), quick = document.getElementById('baQuick');
    const visible = side => [...board.querySelectorAll(`.ba-${side} .ba-item:not(.is-disabled)`)].filter(el => !el.hidden);
    function applyFilter() {
        const term = filter.value.trim().toLowerCase();
        board.querySelectorAll('.ba-item').forEach(el => { el.hidden = term !== '' && !el.textContent.toLowerCase().includes(term); });
        // A search that leaves one receiver selects it, so a single click (or Enter) on the order connects.
        const left = visible('left'), right = visible('right');
        if (term !== '' && left.length === 1) select(left[0].dataset.row);
        else if (selectedRow !== null && leftOf(selectedRow).hidden) select(null);
        quick.disabled = !(term !== '' && left.length === 1 && right.length === 1);
        draw();
    }
    function quickConnect() {
        const left = visible('left'), right = visible('right');
        if (left.length !== 1 || right.length !== 1) return;
        connect(left[0].dataset.row, right[0].dataset.order);
        filter.value = ''; applyFilter(); filter.focus();
    }
    filter.addEventListener('input', applyFilter);
    filter.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); quickConnect(); } });
    quick.addEventListener('click', quickConnect);
    document.getElementById('baFilterClear').addEventListener('click', () => { filter.value = ''; applyFilter(); filter.focus(); });
    board.querySelectorAll('.ba-scroll').forEach(list => list.addEventListener('scroll', () => requestAnimationFrame(draw)));

    function draw() {
        const box = board.getBoundingClientRect();
        svg.innerHTML = '';
        board.querySelectorAll('.ba-item').forEach(el => el.classList.remove('is-connected'));
        pairs.forEach((order, row) => {
            const l = leftOf(row), r = rightOf(order);
            if (!l || !r) return;
            l.classList.add('is-connected'); r.classList.add('is-connected');
            // Lines are drawn only while both ends are visible inside their scroll lists.
            if (!inView(l) || !inView(r)) return;
            const a = l.getBoundingClientRect(), b = r.getBoundingClientRect();
            const x1 = a.right - box.left, y1 = a.top + a.height / 2 - box.top;
            const x2 = b.left - box.left, y2 = b.top + b.height / 2 - box.top;
            const mid = (x1 + x2) / 2, d = `M${x1},${y1} C${mid},${y1} ${mid},${y2} ${x2},${y2}`;
            const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            g.innerHTML = `<title>Click to remove this connection</title><path d="${d}" stroke="transparent" stroke-width="14" fill="none"/><path class="ba-line" d="${d}" stroke="#198754" stroke-width="2.5" fill="none" marker-end="url(#baArrow)"/>`;
            g.addEventListener('click', () => { pairs.delete(row); draw(); });
            svg.appendChild(g);
        });
        svg.insertAdjacentHTML('afterbegin', '<defs><marker id="baArrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M0,0 L10,5 L0,10 z" fill="#198754"/></marker></defs>');
        document.getElementById('baCount').textContent = pairs.size;
    }

    const hint = document.getElementById('baHint'), defaultHint = hint.textContent;
    function select(row) {
        board.querySelectorAll('.ba-left .is-selected').forEach(x => x.classList.remove('is-selected'));
        selectedRow = row;
        if (row !== null) leftOf(row).classList.add('is-selected');
        hint.textContent = row === null ? defaultHint : `Selected "${leftOf(row).querySelector('strong').textContent}". Now click the matching order on the right.`;
    }
    function connect(row, order) {
        // One order per row: an order picked again moves to the new row.
        pairs.forEach((o, r) => { if (o === order) pairs.delete(r); });
        pairs.set(row, order);
        select(null);
        draw();
    }

    board.querySelectorAll('.ba-left .ba-item:not(.is-disabled)').forEach(el => el.addEventListener('click', () => {
        select(selectedRow === el.dataset.row ? null : el.dataset.row);
    }));
    board.querySelectorAll('.ba-right .ba-item:not(.is-disabled)').forEach(el => el.addEventListener('click', () => {
        if (selectedRow === null) { hint.textContent = 'First click a receiver on the left.'; return; }
        connect(selectedRow, el.dataset.order);
    }));

    // Drag from a left point and drop on an order to connect.
    board.querySelectorAll('.ba-left .ba-item:not(.is-disabled) .ba-dot').forEach(dot => dot.addEventListener('pointerdown', e => {
        e.preventDefault(); e.stopPropagation();
        const row = dot.closest('.ba-item').dataset.row, box = board.getBoundingClientRect(), d = dot.getBoundingClientRect();
        const x1 = d.left + d.width / 2 - box.left, y1 = d.top + d.height / 2 - box.top;
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1); line.setAttribute('y1', y1); line.setAttribute('x2', x1); line.setAttribute('y2', y1);
        line.setAttribute('stroke', '#0d6efd'); line.setAttribute('stroke-width', '2.5'); line.setAttribute('stroke-dasharray', '6 4');
        svg.appendChild(line);
        select(row);
        let target = null;
        const move = ev => {
            line.setAttribute('x2', ev.clientX - box.left); line.setAttribute('y2', ev.clientY - box.top);
            const over = document.elementFromPoint(ev.clientX, ev.clientY)?.closest('.ba-right .ba-item:not(.is-disabled)');
            if (over !== target) { target?.classList.remove('is-drop'); target = over || null; target?.classList.add('is-drop'); }
        };
        const up = () => {
            document.removeEventListener('pointermove', move); document.removeEventListener('pointerup', up);
            line.remove(); target?.classList.remove('is-drop');
            if (target) connect(row, target.dataset.order);
        };
        document.addEventListener('pointermove', move); document.addEventListener('pointerup', up);
    }));
    board.querySelectorAll('.ba-left .ba-dot').forEach(dot => dot.addEventListener('click', e => e.stopPropagation()));

    document.getElementById('baForm').addEventListener('submit', () => {
        const box = document.getElementById('baInputs');
        box.innerHTML = '';
        pairs.forEach((order, row) => box.insertAdjacentHTML('beforeend', `<input type="hidden" name="pairs[${row}]" value="${order}">`));
    });
    window.addEventListener('resize', draw);
    draw();
})();
</script>
@endsection
