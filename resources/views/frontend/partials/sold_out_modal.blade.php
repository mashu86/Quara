{{-- Shown when an item was bought by another shopper before this shopper paid. --}}
<div class="modal fade" id="soldOutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="soldOutModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-body p-4 text-center">
                <i class="fa-solid fa-circle-exclamation text-danger display-5 mb-3" aria-hidden="true"></i>
                <h2 class="h5 fw-bold mb-2" id="soldOutModalTitle">Sorry, this item was just sold</h2>
                <p class="text-muted small mb-2">Another customer completed their purchase first. The following item(s) were removed from your order:</p>
                <ul id="soldOutModalItems" class="list-unstyled fw-semibold mb-3">
                    @foreach(session('sold_out_items', []) as $soldItem)
                        <li>{{ $soldItem }}</li>
                    @endforeach
                </ul>
                <p id="soldOutModalRemaining" class="small text-success mb-0" hidden>You can still pay for the other items in your order.</p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex flex-column flex-sm-row gap-2">
                <button type="button" id="soldOutModalShop" class="btn btn-outline-dark rounded-pill flex-fill">
                    <i class="fa-solid fa-bag-shopping me-1" aria-hidden="true"></i> Shop other products
                </button>
                <button type="button" id="soldOutModalContinue" class="btn btn-qw-gold rounded-pill flex-fill" hidden>
                    <i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i> Continue payment
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    /**
     * items: names of the sold items. onShop / onContinue: what each button does;
     * leave onContinue out when nothing is left to pay for.
     */
    window.showSoldOutModal = function (items, onShop, onContinue) {
        const list = document.getElementById('soldOutModalItems');
        list.replaceChildren(...items.map((name) => Object.assign(document.createElement('li'), { textContent: name })));
        document.getElementById('soldOutModalRemaining').hidden = !onContinue;
        const continueBtn = document.getElementById('soldOutModalContinue');
        continueBtn.hidden = !onContinue;
        continueBtn.onclick = onContinue || null;
        document.getElementById('soldOutModalShop').onclick = onShop;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('soldOutModal')).show();
    };

    @if(session('sold_out_items'))
    {{-- Redirected here by checkout: continue by resubmitting the checkout form when one is on the page. --}}
    document.addEventListener('DOMContentLoaded', function () {
        const checkoutForm = document.querySelector('form[action="{{ route('checkout.process') }}"]');
        window.showSoldOutModal(
            @json(session('sold_out_items')),
            () => { window.location.href = @json(route('shop')); },
            checkoutForm ? () => checkoutForm.requestSubmit() : null
        );
    });
    @endif
</script>
