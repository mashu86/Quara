document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-district-checkout]');
    if (!form) return;
    const pin = form.elements.pin_code;
    const message = document.getElementById('pinLookupMessage');
    const offerRow = document.getElementById('districtOfferRow');
    const total = document.getElementById('checkoutTotal');
    const rounding = document.getElementById('checkoutRounding');
    const shippingAmount = document.getElementById('checkoutShippingAmount');
    const shippingLabel = document.getElementById('checkoutShippingLabel');
    const submit = form.querySelector('button[type="submit"]');
    let revision = 0;
    let lastRequestedPin = '';
    window.checkoutDistrictOfferData = {pin: '', discount: 0, shipping: Number(form.dataset.baseShipping || 0)};

    // Pricing verification never writes to the customer's editable address fields.
    async function lookup(force = false) {
        const value = pin.value.trim();
        if (!force && value === lastRequestedPin) return;
        lastRequestedPin = value;
        const version = ++revision;
        offerRow.hidden = true;
        window.checkoutDistrictOfferData = {pin: value, discount: 0, shipping: Number(form.dataset.baseShipping || 0)};
        if (shippingAmount) {
            const baseShipping = Number(form.dataset.baseShipping || 0);
            shippingAmount.textContent = baseShipping > 0 ? '\u20b9' + baseShipping.toFixed(2) : 'FREE';
            shippingAmount.className = baseShipping > 0 ? 'fw-bold text-dark' : 'text-success fw-semibold';
        }
        if (shippingLabel) shippingLabel.textContent = 'Shipping Charge';
        total.textContent = '\u20b9' + Number(form.dataset.baseTotal).toFixed(2);
        rounding.textContent = '+\u20b9' + Number(form.dataset.baseRounding).toFixed(2);
        submit.disabled = false;
        if (!/^[1-9][0-9]{5}$/.test(value)) {
            message.textContent = 'Enter a valid six-digit PIN code.';
            return;
        }
        submit.disabled = true;
        message.textContent = 'Checking delivery charge...';
        try {
            const response = await fetch(form.dataset.districtCheckout + '?' + new URLSearchParams({pin_code: value}), {headers: {Accept: 'application/json'}});
            const data = await response.json();
            if (version !== revision) return;
            if (!response.ok) throw new Error('Offer lookup unavailable.');
            window.QuaraPincode?.remember(value, data);
            message.textContent = data.discount > 0 ? 'District offer applied for this PIN code.' : '';
            window.checkoutDistrictOfferData = {pin: value, discount: Number(data.discount || 0), shipping: Number(data.shipping ?? form.dataset.baseShipping ?? 0)};
            if (shippingAmount) {
                const shipping = Number(data.shipping || 0);
                shippingAmount.textContent = shipping > 0 ? '\u20b9' + shipping.toFixed(2) : 'FREE';
                shippingAmount.className = shipping > 0 ? 'fw-bold text-dark' : 'text-success fw-semibold';
            }
            if (shippingLabel) shippingLabel.textContent = Number(data.outside_kerala_surcharge || 0) > 0 ? 'Shipping Charge (Outside Kerala)' : 'Shipping Charge';
            offerRow.hidden = !(data.discount > 0);
            document.getElementById('districtOfferAmount').textContent = '-\u20b9' + Number(data.discount).toFixed(2);
            const couponDiscount = Number(form.dataset.couponDiscount || 0);
            const raw = Math.max(0, Number(form.dataset.baseProductTotal) - couponDiscount - Number(data.discount || 0) + Number(data.shipping || 0));
            const rounded = Math.ceil(raw);
            total.textContent = '\u20b9' + rounded.toFixed(2);
            rounding.textContent = '+\u20b9' + (rounded - raw).toFixed(2);
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
