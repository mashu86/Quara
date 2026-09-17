<div id="sizeGuideExport" aria-hidden="true" style="position: fixed; left: -10000px; top: 0; width: 760px; padding: 24px; background: #fff; color: #212529;">
    <div style="display: flex; align-items: center; gap: 16px; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 2px solid #212529;">
        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" crossorigin="anonymous" style="display: block; max-height: 52px; max-width: 170px; object-fit: contain;">
        <div>
            <div style="font-size: 24px; font-weight: 700; line-height: 1.2;">{{ $siteName }}</div>
            <div style="font-size: 13px; color: #6c757d; margin-top: 3px;">Ladieswear Size Guide · Measurements in inches</div>
        </div>
    </div>

    <section style="margin-bottom: 14px;">
        <h2 style="font-size: 16px; margin: 0 0 7px;">Body measurement size chart</h2>
        <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 13px;">
            <thead><tr style="background: #212529; color: #fff;"><th style="padding: 6px; border: 1px solid #212529;">Indian size</th><th style="padding: 6px; border: 1px solid #212529;">Your body bust</th><th style="padding: 6px; border: 1px solid #212529;">Your body waist</th></tr></thead>
            <tbody>
                @foreach ([
                    ['XS', '32–33&quot;', '26–27&quot;'], ['S', '34–35&quot;', '28–29&quot;'], ['M', '36–37&quot;', '30–31&quot;'],
                    ['L', '38–39&quot;', '32–33&quot;'], ['XL', '40–41&quot;', '34–35&quot;'], ['XXL', '42–43&quot;', '36–37&quot;'],
                    ['3XL', '44–45&quot;', '38–39&quot;'], ['4XL', '46–47&quot;', '40–41&quot;'], ['5XL', '48–49&quot;', '42–43&quot;'], ['6XL', '50–51&quot;', '42–43&quot;'],
                ] as [$size, $bust, $waist])
                    <tr style="background: {{ in_array($size, ['M', 'L']) ? '#f1f5f9' : '#fff' }};"><td style="padding: 5px; border: 1px solid #dee2e6; font-weight: 700;">{{ $size }}</td><td style="padding: 5px; border: 1px solid #dee2e6;">{!! $bust !!}</td><td style="padding: 5px; border: 1px solid #dee2e6;">{!! $waist !!}</td></tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2 style="font-size: 16px; margin: 0 0 7px;">Finished garment chest guide</h2>
        <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 11px;">
            <thead><tr style="background: #212529; color: #fff;"><th style="padding: 6px 3px; border: 1px solid #212529;">Size</th><th style="padding: 6px 3px; border: 1px solid #212529;">Korean crop top<br><span style="font-weight: 400;">fitted</span></th><th style="padding: 6px 3px; border: 1px solid #212529;">Korean top<br><span style="font-weight: 400;">regular</span></th><th style="padding: 6px 3px; border: 1px solid #212529;">Normal top<br><span style="font-weight: 400;">regular</span></th><th style="padding: 6px 3px; border: 1px solid #212529;">Ladies shirt<br><span style="font-weight: 400;">regular</span></th><th style="padding: 6px 3px; border: 1px solid #212529;">Overcoat / jacket<br><span style="font-weight: 400;">layering fit</span></th></tr></thead>
            <tbody>
                @foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL', '6XL'] as $index => $size)
                    <tr style="background: {{ $index % 2 ? '#f8f9fa' : '#fff' }};"><td style="padding: 5px 3px; border: 1px solid #dee2e6; font-weight: 700;">{{ $size }}</td><td style="padding: 5px 3px; border: 1px solid #dee2e6;">{{ 34 + ($index * 2) }}&quot;</td><td style="padding: 5px 3px; border: 1px solid #dee2e6;">{{ 36 + ($index * 2) }}&quot;</td><td style="padding: 5px 3px; border: 1px solid #dee2e6;">{{ 36 + ($index * 2) }}&quot;</td><td style="padding: 5px 3px; border: 1px solid #dee2e6;">{{ 36 + ($index * 2) }}&quot;</td><td style="padding: 5px 3px; border: 1px solid #dee2e6;">{{ 38 + ($index * 2) }}&quot;</td></tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
