<div id="sizeGuideExport" aria-hidden="true" style="position: fixed; left: -10000px; top: 0; width: 760px; padding: 24px; background: #fff; color: #212529;">
    <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 2px solid #212529;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" crossorigin="anonymous" style="display: block; max-height: 52px; max-width: 170px; object-fit: contain;">
            <div>
                <div style="font-size: 22px; font-weight: 700; line-height: 1.2;">{{ $siteName }}</div>
                <div style="font-size: 13px; color: #6c757d; margin-top: 2px;">Garment Size Guide · All measurements in inches (in)</div>
            </div>
        </div>
        @if(isset($selectedMaster) && $selectedMaster)
            <div style="background: #0d6efd; color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700;">
                {{ $selectedMaster->name }}
            </div>
        @endif
    </div>

    @if(isset($selectedMaster) && $selectedMaster)
        <section style="margin-bottom: 16px;">
            <h2 style="font-size: 16px; margin: 0 0 10px; font-weight: 700;">{{ $selectedMaster->name }} Measurement Chart</h2>
            <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 13px;">
                <thead>
                    <tr style="background: #212529; color: #fff;">
                        <th style="padding: 8px; border: 1px solid #212529;">Size</th>
                        <th style="padding: 8px; border: 1px solid #212529;">Chest (C)</th>
                        <th style="padding: 8px; border: 1px solid #212529;">Waist (W)</th>
                        <th style="padding: 8px; border: 1px solid #212529;">Length (L)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($selectedMaster->rows as $index => $r)
                        <tr style="background: {{ $index % 2 ? '#f8f9fa' : '#fff' }};">
                            <td style="padding: 7px; border: 1px solid #dee2e6; font-weight: 700; color: #0d6efd;">{{ $r->size_label }}</td>
                            <td style="padding: 7px; border: 1px solid #dee2e6; font-weight: 600;">{{ $r->chest ?: '-' }}</td>
                            <td style="padding: 7px; border: 1px solid #dee2e6;">{{ $r->waist ?: '-' }}</td>
                            <td style="padding: 7px; border: 1px solid #dee2e6;">{{ $r->length ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 12px; color: #6c757d;">No size measurements available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif

    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; font-size: 11px; color: #495057;">
        <strong>How to measure finished garment:</strong>
        Chest (C) & Waist (W) are full circumference flat-garment measurements in inches. Length (L) is measured from top shoulder to bottom hem.
    </div>
</div>
