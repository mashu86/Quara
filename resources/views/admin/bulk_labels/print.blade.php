<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $filename }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; font: 14px Arial, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 2; padding: 14px 18px; background: #f4f4f5; border-bottom: 1px solid #ddd; display: flex; align-items: center; flex-wrap: wrap; gap: 12px; }
        .toolbar strong { margin-right: auto; }
        .toolbar label { display: inline-flex; align-items: center; gap: 5px; }
        button { border: 0; border-radius: 5px; padding: 9px 14px; font-weight: 700; cursor: pointer; background: #222; color: #fff; }
        button.secondary { background: #b8860b; color: #111; }
        #labels { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 12px; }
        .label { width: 105mm; height: 145mm; overflow: hidden; padding: 6mm 7mm 5mm; border: 1px dashed #aaa; page-break-after: always; break-after: page; display: flex; flex-direction: column; font-weight: 800; font-family: Arial, "Segoe UI", Helvetica, sans-serif; }
        .label:last-child { page-break-after: auto; break-after: auto; }
        .addresses { display: flex; flex: 1 1 auto; min-height: 0; flex-direction: column; justify-content: flex-start; }
        .block { overflow: visible; overflow-wrap: anywhere; line-height: 1.15; font-size: 24px; font-weight: 800; }
        .to-block { flex: 0 0 auto; min-height: 0; max-height: none; }
        .from-block { flex: 0 0 auto; align-self: flex-end; width: 80mm; border-top: 2px dashed #111; margin-top: 4mm; padding-top: 2mm; }
        .caption { display: block; font-size: 24px; font-weight: 800; text-transform: uppercase; margin-bottom: 2mm; }
        .name { font-size: 24px; font-weight: 800; margin-bottom: 0; }
        .meta { font-size: 24px; font-weight: 800; margin-top: 0; }
        .label, .label * { font-size: 24px !important; font-weight: 800 !important; line-height: 1.15 !important; }
        .label .sender-address { font-size: 21px !important; line-height: 1.15 !important; }
        .label .customer-id { display: block; /* align-self: flex-end; */ width: 80mm; text-align: left; margin: 3mm 0 0; padding: 2mm 0 0; border-top: 2px dashed #111; }
        body.split .addresses { display: grid; grid-template-columns: 1fr 1fr; gap: 3mm; }
        body.split .block { min-width: 0; line-height: 1.1; }
        body.split .from-block { width: auto; border-top: 0; border-left: 2px solid #111; margin: 0; padding: 0 0 0 3mm; }
        body.split .customer-id { grid-column: 2; width: auto; align-self: start; }
        body.split .caption, body.split .name, body.split .meta { font-size: 24px; }
        @media screen and (max-width: 600px) {
            .toolbar { position: static; padding: 10px; gap: 8px; }
            .toolbar strong { width: 100%; flex: 0 0 100%; overflow-wrap: anywhere; font-size: 13px; }
            .toolbar > span { margin-right: 2px; }
            .toolbar button { padding: 8px 10px; }
            #labels { width: 100%; align-items: flex-start; overflow-x: auto; padding: 8px; gap: 8px; }
            .label { width: 105mm; min-width: 105mm; max-width: 105mm; height: 145mm; min-height: 145mm; max-height: 145mm; padding: 6mm 7mm 5mm; }
            .addresses { min-height: 0; }
            .block, .caption, .name, .meta { font-size: clamp(14px, 5.45vw, 24px); }
            .to-block { min-height: 0; max-height: none; }
            .from-block { width: 80%; }
            .customer-id { width: 80%; }
        }
        @page { size: 105mm 145mm; margin: 0; }
        @media print {
            html, body { width: 105mm !important; min-width: 105mm !important; height: auto !important; margin: 0 !important; padding: 0 !important; }
            .toolbar { display: none !important; }
            #labels { display: block !important; width: 105mm !important; margin: 0 !important; padding: 0 !important; }
            .label { display: flex !important; box-sizing: border-box !important; margin: 0 !important; border: 0 !important; width: 105mm !important; min-width: 105mm !important; max-width: 105mm !important; height: 145mm !important; min-height: 145mm !important; max-height: 145mm !important; padding: 6mm 7mm 5mm !important; overflow: hidden !important; }
            .addresses { flex: 1 1 auto !important; width: 100% !important; min-height: 0 !important; }
            .block, .caption, .name, .meta { min-width: 0 !important; font-size: 24px !important; font-weight: 800 !important; }
            .block { line-height: 1.15 !important; }
            .to-block { flex: 0 0 auto !important; min-height: 0 !important; max-height: none !important; }
            .from-block { flex: 0 0 auto !important; width: 80mm !important; margin-top: 4mm !important; padding-top: 2mm !important; }
        }
    </style>
</head>
<body class="{{ $layout }}">
    <div class="toolbar">
        <strong>{{ $orders->count() }} label(s) · {{ $filename }}</strong>
        <span>Layout:</span>
        <label><input type="radio" name="layout" value="vertical" {{ $layout === 'vertical' ? 'checked' : '' }}> Vertical</label>
        <label><input type="radio" name="layout" value="split" {{ $layout === 'split' ? 'checked' : '' }}> Equal split</label>
        <button type="button" onclick="window.print()"><i>Print</i></button>
        <button type="submit" form="bulkPdfDownloadForm" class="secondary">Download PDF</button>
    </div>
    <form id="bulkPdfDownloadForm" method="POST" action="{{ route('admin.bulk-labels.download-pdf') }}">
        @csrf
        <input type="hidden" name="start_date" value="{{ $startDate }}">
        <input type="hidden" name="end_date" value="{{ $endDate }}">
        <input type="hidden" name="layout" id="downloadPdfLayout" value="{{ $layout }}">
        @foreach($orders as $order)<input type="hidden" name="order_ids[]" value="{{ $order->id }}">@endforeach
    </form>
    <main id="labels">
        @foreach($orders as $order)
            <article class="label">
                <div class="addresses">
                    <section class="block to-block">
                        <div class="caption">To ,</div>
                        <div class="name">{{ mb_strtoupper($order->customer_name ?? '') }}</div>
                        @php($houseStreet = collect([$order->house_building, $order->street])->filter()->implode(' - '))
                        @if($houseStreet)<div>{{ $houseStreet }}</div>@endif
                        @php($area = trim((string) $order->area))
                        @if($order->order_source === 'manual' && preg_match('/^counter\s+sale$/i', $area)) @php($area = '') @endif
                        @php($areaCity = collect([$area, $order->city])->filter()->implode(', '))
                        @if($areaCity)<div>{{ $areaCity }}</div>@endif
                        @php($districtStatePin = collect([$order->district, $order->state])->filter()->implode(', '))
                        @if($order->pin_code)@php($districtStatePin = trim($districtStatePin . ', PIN: ' . $order->pin_code, ', '))@endif
                        @if($districtStatePin)<div>{{ $districtStatePin }}</div>@endif
                        @if($order->customer_phone)<div>PH: {{ $order->customer_phone }}</div>@endif
                    </section>
                    <section class="block from-block">
                        <div class="caption">From:</div>
                        <div class="sender-address">@foreach($fromAddress as $senderLine){{ $senderLine }}@if(!$loop->last)<br>@endif @endforeach</div>
                    </section>
                    <div class="customer-id">Customer Id: {{ $customerId }}</div>
                </div>
            </article>
        @endforeach
    </main>
    <script>
        document.querySelectorAll('input[name="layout"]').forEach(input => input.addEventListener('change', () => {
            document.body.classList.toggle('split', input.value === 'split');
            document.body.classList.toggle('vertical', input.value === 'vertical');
            document.getElementById('downloadPdfLayout').value = input.value;
        }));
    </script>
</body>
</html>
