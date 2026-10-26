<div class="modal fade" id="trackingIdModal" tabindex="-1" aria-labelledby="trackingIdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="trackingIdModalLabel"><i class="fa-solid fa-truck-fast text-gold me-2"></i>Tracking ID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <form id="trackingIdForm" class="mb-3">
                    <label for="trackingIdPhone" class="form-label">Enter the phone number used for your order</label>
                    <div class="input-group">
                        <span class="input-group-text">+91</span>
                        <input type="tel" id="trackingIdPhone" class="form-control" inputmode="numeric" maxlength="14" placeholder="10 digit phone number" autocomplete="tel" required>
                        <button class="btn btn-dark" type="submit" id="trackingIdSubmit">Find</button>
                    </div>
                    <div class="small text-danger mt-2" id="trackingIdError" hidden></div>
                </form>
                <div id="trackingIdResults"></div>
            </div>
        </div>
    </div>
</div>
<script>
(() => {
    const modal = document.getElementById('trackingIdModal');
    if (!modal) return;
    const form = document.getElementById('trackingIdForm'), phone = document.getElementById('trackingIdPhone');
    const error = document.getElementById('trackingIdError'), results = document.getElementById('trackingIdResults'), submit = document.getElementById('trackingIdSubmit');
    const indiaPostUrl = 'https://www.indiapost.gov.in/';
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    modal.addEventListener('shown.bs.modal', () => phone.focus());

    form.addEventListener('submit', async e => {
        e.preventDefault();
        error.hidden = true; results.innerHTML = ''; submit.disabled = true;
        try {
            const res = await fetch(@json(route('tracking.lookup')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token()) },
                body: JSON.stringify({ phone: phone.value })
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(res.status === 429 ? 'Too many tries. Please wait a minute and try again.' : (data.message || 'Something went wrong. Please try again.'));
            if (!data.orders.length) {
                results.innerHTML = '<div class="alert alert-light border text-center mb-0">No orders found for this phone number.</div>';
                return;
            }
            results.innerHTML = data.orders.map(o => `
                <div class="border rounded-3 p-3 mb-2">
                    <div class="d-flex justify-content-between flex-wrap gap-1">
                        <strong>${esc(o.order_number)}</strong>
                        <span class="small text-muted">${esc(o.date)} · ${esc(o.status)}</span>
                    </div>
                    ${o.tracking_number ? `
                        <div class="mt-2 small text-muted">Tracking number</div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="fw-bold fs-6 font-monospace">${esc(o.tracking_number)}</span>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-copy="${esc(o.tracking_number)}"><i class="fa-regular fa-copy me-1"></i>Copy</button>
                            <a class="btn btn-sm text-white" style="background:linear-gradient(135deg,#D4AF37 0%,#AA7C11 100%)" href="${indiaPostUrl}" target="_blank" rel="noopener" data-copy-open="${esc(o.tracking_number)}"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Track on India Post</a>
                        </div>` : `<div class="mt-2 small text-warning-emphasis"><i class="fa-regular fa-clock me-1"></i>Tracking number not generated yet.</div>`}
                </div>`).join('') + '<div class="small text-muted">Tip: the tracking number is copied when you open India Post. Paste it there to see the status.</div>';
        } catch (err) {
            error.textContent = err.message; error.hidden = false;
        } finally {
            submit.disabled = false;
        }
    });

    async function copy(text) {
        try { await navigator.clipboard.writeText(text); return true; }
        catch (e) {
            const t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select();
            const ok = document.execCommand('copy'); t.remove(); return ok;
        }
    }
    results.addEventListener('click', async e => {
        const btn = e.target.closest('[data-copy]');
        const link = e.target.closest('[data-copy-open]');
        if (btn) {
            const ok = await copy(btn.dataset.copy);
            btn.innerHTML = ok ? '<i class="fa-solid fa-check me-1"></i>Copied' : 'Copy failed';
            setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy me-1"></i>Copy'; }, 2000);
        } else if (link) {
            copy(link.dataset.copyOpen);
        }
    });
})();
</script>
