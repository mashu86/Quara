document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-district-checkout]');
    if (!form) return;
    const pin = form.elements.pin_code;
    const message = document.getElementById('pinLookupMessage');
    const offerRow = document.getElementById('districtOfferRow');
    const total = document.getElementById('checkoutTotal');
    const rounding = document.getElementById('checkoutRounding');
    const submit = form.querySelector('button[type="submit"]');
    let revision = 0;
    let lastRequestedPin = '';

    // Pricing verification never writes to the customer's editable address fields.
    async function lookup(force = false) {
        const value = pin.value.trim();
        if (!force && value === lastRequestedPin) return;
        lastRequestedPin = value;
        const version = ++revision;
        offerRow.hidden = true;
        total.textContent = '\u20b9' + Number(form.dataset.baseTotal).toFixed(2);
        rounding.textContent = '+\u20b9' + Number(form.dataset.baseRounding).toFixed(2);
        submit.disabled = false;
        if (!/^[1-9][0-9]{5}$/.test(value)) {
            message.textContent = 'Enter a valid six-digit PIN code.';
            return;
        }
        submit.disabled = true;
        message.textContent = 'Checking district offer...';
        try {
            const response = await fetch(form.dataset.districtCheckout + '?' + new URLSearchParams({pin_code: value}), {headers: {Accept: 'application/json'}});
            const data = await response.json();
            if (version !== revision) return;
            if (!response.ok) throw new Error('Offer lookup unavailable.');
            window.QuaraPincode?.remember(value, data);
            message.textContent = data.discount > 0 ? 'District offer applied for this PIN code.' : '';
            offerRow.hidden = !(data.discount > 0);
            document.getElementById('districtOfferAmount').textContent = '-\u20b9' + Number(data.discount).toFixed(2);
            total.textContent = '\u20b9' + Number(data.grand_total).toFixed(2);
            rounding.textContent = '+\u20b9' + Number(data.rounding_adjustment).toFixed(2);
        } catch {
            if (version === revision) {
                lastRequestedPin = '';
                message.textContent = 'District offer could not be verified. You can enter your address manually and continue; offers are checked again when placing the order.';
            }
        } finally {
            if (version === revision) submit.disabled = false;
        }
    }
    pin.addEventListener('input', () => lookup());
    pin.addEventListener('change', () => lookup());
    window.addEventListener('online', () => lookup(true));
    lookup();
});
