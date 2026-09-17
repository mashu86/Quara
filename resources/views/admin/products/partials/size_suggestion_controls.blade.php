<div class="rounded-3 border p-3 mb-3" id="product-size-suggestion-controls">
    <div class="small fw-bold mb-2"><i class="fa-solid fa-wand-magic-sparkles text-warning me-1" aria-hidden="true"></i> Magic Size Suggestion</div>
    <div class="row g-2">
        <div class="col-12 col-sm-6">
            <label for="size-suggestion-type" class="form-label small mb-1">Product type</label>
            <select id="size-suggestion-type" class="form-select form-select-sm" data-size-type>
                <option value="auto">Auto-detect from name / categories</option>
                <option value="korean_top">Korean top (regular fit)</option>
                <option value="korean_crop_top">Korean crop top (fitted)</option>
                <option value="normal_top">Normal top (regular fit)</option>
                <option value="ladies_shirt">Ladies shirt (regular fit)</option>
                <option value="overcoat">Overcoat / jacket (layering fit)</option>
            </select>
        </div>
        <div class="col-12 col-sm-6">
            <label for="size-suggestion-basis" class="form-label small mb-1">How the garment was measured</label>
            <select id="size-suggestion-basis" class="form-select form-select-sm" data-size-basis>
                <option value="circumference">Finished garment full circumference (inches)</option>
                <option value="flat">Finished garment flat width (inches; doubled)</option>
            </select>
        </div>
    </div>
    <div class="small fw-semibold mt-2" data-size-type-status aria-live="polite"></div>
    <p class="small text-muted mt-1 mb-0">Enter the <strong>finished garment</strong> chest (not the customer's body chest), then click the magic icon beside Size Label. Each ladieswear style uses its own fit allowance. Waist and length are saved/displayed but do not decide the alpha size. Supplier chart always takes priority.</p>
</div>
