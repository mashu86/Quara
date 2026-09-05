/**
 * Indian Pincode Auto-State & District Lookup Helper
 * Automatically fills state and district fields when 6 valid digits are typed into any pincode field.
 */
document.addEventListener('DOMContentLoaded', function() {
    function setupPincodeLookup(pincodeInput) {
        if (!pincodeInput || pincodeInput.dataset.pincodeListener) return;
        pincodeInput.dataset.pincodeListener = 'true';

        let debounceTimer;

        pincodeInput.addEventListener('input', function() {
            const pincode = this.value.trim().replace(/\D/g, '');
            if (pincode.length !== 6) return;

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                const form = pincodeInput.closest('form') || document;
                const stateInput = form.querySelector('input[name="state"], select[name="state"], #state');
                const districtInput = form.querySelector('input[name="district"], select[name="district"], #district');

                if (stateInput && !stateInput.value) stateInput.placeholder = 'Fetching state...';
                if (districtInput && !districtInput.value) districtInput.placeholder = 'Fetching district...';

                fetch('https://api.postalpincode.in/pincode/' + pincode)
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice && data[0].PostOffice.length > 0) {
                            const po = data[0].PostOffice[0];
                            if (stateInput && po.State) {
                                stateInput.value = po.State;
                                stateInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                            if (districtInput && po.District) {
                                districtInput.value = po.District;
                                districtInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    })
                    .catch(function(err) {
                        console.log('Pincode lookup error:', err);
                    })
                    .finally(function() {
                        if (stateInput) stateInput.placeholder = '';
                        if (districtInput) districtInput.placeholder = '';
                    });
            }, 300);
        });
    }

    function initAllPincodeInputs() {
        const selector = 'input[name="pin_code"], input[name="pincode"], input[name="postal_code"], #pin_code, #pincode';
        document.querySelectorAll(selector).forEach(setupPincodeLookup);
    }

    initAllPincodeInputs();

    const observer = new MutationObserver(function() {
        initAllPincodeInputs();
    });
    observer.observe(document.body, { childList: true, subtree: true });
});
