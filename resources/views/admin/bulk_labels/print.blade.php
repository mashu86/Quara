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
        .label { width: 105mm; height: 145mm; overflow: hidden; padding: 6mm 7mm 5mm; border: 1px dashed #aaa; page-break-after: always; break-after: page; display: flex; flex-direction: column; font-weight: 700; font-family: Arial, "Segoe UI", Helvetica, sans-serif; }
        .label:last-child { page-break-after: auto; break-after: auto; }
        .addresses { display: flex; flex: 1 1 auto; min-height: 0; flex-direction: column; justify-content: space-between; }
        .block { --fit: 1; overflow: hidden; overflow-wrap: anywhere; line-height: 1.4; font-size: calc(14pt * var(--fit)); font-weight: 700; }
        .to-block { flex: 1 1 auto; min-height: 50mm; max-height: 85mm; font-size: calc(16.5pt * var(--fit)); letter-spacing: 0.2px; }
        .from-block { flex: 0 0 auto; align-self: flex-end; width: 80mm; border-top: 2px dashed #111; margin-top: 3mm; padding-top: 3mm; font-size: calc(14pt * var(--fit)); }
        .caption { display: block; font-size: calc(18pt * var(--fit)); font-weight: 800; text-transform: uppercase; margin-bottom: 2mm; }
        .name { font-size: inherit; font-weight: 800; margin-bottom: 0; }
        .meta { font-size: inherit; font-weight: 700; margin-top: 0; }
        body.split .addresses { display: grid; grid-template-columns: 1fr 1fr; gap: 3mm; }
        body.split .block { min-width: 0; font-size: calc(10pt * var(--fit)); line-height: 1.2; }
        body.split .from-block { width: auto; border-top: 0; border-left: 2px solid #111; margin: 0; padding: 0 0 0 3mm; }
        body.split .caption { font-size: calc(12pt * var(--fit)); }
        body.split .name { font-size: inherit; }
        body.split .meta { font-size: inherit; }
        @page { size: 105mm 145mm; margin: 0; }
        @media print {
            html, body { width: 105mm !important; min-width: 105mm !important; height: auto !important; margin: 0 !important; padding: 0 !important; }
            .toolbar { display: none !important; }
            #labels { display: block !important; width: 105mm !important; margin: 0 !important; padding: 0 !important; }
            .label { display: flex !important; box-sizing: border-box !important; margin: 0 !important; border: 0 !important; width: 105mm !important; min-width: 105mm !important; max-width: 105mm !important; height: 145mm !important; min-height: 145mm !important; max-height: 145mm !important; padding: 6mm 7mm 5mm !important; overflow: hidden !important; }
            .addresses { flex: 1 1 auto !important; width: 100% !important; min-height: 0 !important; }
            .block { min-width: 0 !important; }
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
        <button type="button" class="secondary" onclick="window.print()">Download PDF</button>
    </div>
    <main id="labels">
        @foreach($orders as $order)
            <article class="label">
                <div class="addresses">
                    <section class="block to-block">
                        <div class="caption">To ,</div>
                        <div class="name">{{ mb_strtoupper($order->customer_name ?? '') }}</div>
                        @php($houseStreet = collect([$order->house_building, $order->street])->filter()->implode(' - '))
                        @if($houseStreet)<div>{{ $houseStreet }}</div>@endif
                        @php($areaCity = collect([$order->area, $order->city])->filter()->implode(', '))
                        @if($areaCity)<div>{{ $areaCity }}</div>@endif
                        @php($districtStatePin = collect([$order->district, $order->state])->filter()->implode(', '))
                        @if($order->pin_code)@php($districtStatePin = trim($districtStatePin . ', PIN: ' . $order->pin_code, ', '))@endif
                        @if($districtStatePin)<div>{{ $districtStatePin }}</div>@endif
                        @if($order->customer_phone)<div>PH: {{ $order->customer_phone }}</div>@endif
                    </section>
                    <section class="block from-block">
                        <div class="caption">From ,</div>
                        <div>@foreach($fromAddress as $senderLine){{ $senderLine }}@if(!$loop->last)<br>@endif @endforeach</div>
                    </section>
                </div>
            </article>
        @endforeach
    </main>
    <script>
        function fitLabelAddresses() {
            document.querySelectorAll('.block').forEach(block => {
                let scale = 1;
                block.style.setProperty('--fit', scale);
                while (block.scrollHeight > block.clientHeight + 1 && scale > 0.6) {
                    scale -= 0.05;
                    block.style.setProperty('--fit', scale.toFixed(2));
                }
            });
        }
        window.addEventListener('load', fitLabelAddresses);
        window.addEventListener('beforeprint', fitLabelAddresses);
        document.querySelectorAll('input[name="layout"]').forEach(input => input.addEventListener('change', () => {
            document.body.classList.toggle('split', input.value === 'split');
            document.body.classList.toggle('vertical', input.value === 'vertical');
            requestAnimationFrame(fitLabelAddresses);
        }));
    </script>
</body>
</html>
