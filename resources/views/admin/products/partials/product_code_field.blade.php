{{-- Product code (optional). Create pre-fills the next PR code; edit shows the saved one. --}}
<div class="col-md-6">
    <label for="productCodeInput" class="form-label fw-bold">Product Code <span class="text-muted small fw-normal">(optional)</span></label>
    <div class="input-group">
        <input type="text" name="product_code" id="productCodeInput" maxlength="50"
               class="form-control rounded-start-3 text-uppercase @error('product_code') is-invalid @enderror"
               placeholder="e.g. PR12" value="{{ $codeValue }}" autocomplete="off">
        <button type="button" class="btn btn-outline-warning text-dark rounded-end-3" id="regenerateProductCodeBtn"
                title="Generate next product code" aria-label="Generate next product code">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
        </button>
        @error('product_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-text small">Auto-filled with the next PR number. You can type your own code; it must not be used by another product.</div>
</div>
<script>
    document.getElementById('regenerateProductCodeBtn')?.addEventListener('click', function () {
        const btn = this;
        const input = document.getElementById('productCodeInput');
        btn.disabled = true;
        fetch(@json(route('admin.products.next-code')), { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => { if (data.code) { input.value = data.code; input.classList.remove('is-invalid'); } })
            .catch(() => alert('Could not generate a product code.'))
            .finally(() => { btn.disabled = false; });
    });
</script>
