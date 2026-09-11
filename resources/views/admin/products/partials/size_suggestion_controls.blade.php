<div class="rounded-3 border p-3 mb-3" id="product-size-suggestion-controls">
    <div class="small fw-bold mb-2"><i class="fa-solid fa-wand-magic-sparkles text-warning me-1" aria-hidden="true"></i> Magic Size Suggestion</div>
    <div class="row g-2">
        <div class="col-12 col-sm-6">
            <label for="size-suggestion-type" class="form-label small mb-1">Product type</label>
            <select id="size-suggestion-type" class="form-select form-select-sm" data-size-type>
                <option value="auto">Auto-detect from name / categories</option>
                <option value="kurti">Kurti / Kurta / Salwar set</option>
                <option value="top">Top / Shirt / Blouse</option>
                <option value="dress">Dress / Gown / Maxi</option>
                <option value="bottom">Pants / Skirt / Bottom</option>
                <option value="abaya">Abaya</option>
            </select>
        </div>
        <div class="col-12 col-sm-6">
            <label for="size-suggestion-basis" class="form-label small mb-1">Chest / Waist measurements</label>
            <select id="size-suggestion-basis" class="form-select form-select-sm" data-size-basis>
                <option value="circumference">Full circumference (inches)</option>
                <option value="flat">Flat width (inches; double for suggestion)</option>
            </select>
        </div>
    </div>
    <div class="small fw-semibold mt-2" data-size-type-status aria-live="polite"></div>
    <p class="small text-muted mt-1 mb-0">Enter Chest, Waist and Length, then click the magic icon beside Size Label. Suggestions use an approximate adult size guide; check the supplier label before saving. Measurements and stock stay as entered.</p>
</div>
