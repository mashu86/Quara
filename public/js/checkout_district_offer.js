document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-district-checkout]');
    if (!form) return;
    const pin = form.elements.pin_code;
    const district = form.elements.district;
    const state = form.elements.state;
    const message = document.getElementById('pinLookupMessage');
    const offerRow = document.getElementById('districtOfferRow');
    const total = document.getElementById('checkoutTotal');
    const rounding = document.getElementById('checkoutRounding');
    const submit = form.querySelector('button[type="submit"]');
    let revision = 0;
    let verifiedPin = '';
    let lastRequestedPin = '';

    async function lookup(force = false) {
        const value = pin.value.trim();
        if (!force && value === lastRequestedPin) return;
        lastRequestedPin = value;
        const version = ++revision;
        verifiedPin = '';
        submit.disabled = true;
        district.value = '';
        state.value = '';
        offerRow.hidden = true;
        total.textContent = '₹' + Number(form.dataset.baseTotal).toFixed(2);
        rounding.textContent = '+₹' + Number(form.dataset.baseRounding).toFixed(2);
        if (!/^[1-9][0-9]{5}$/.test(value)) {
            message.textContent = 'Enter your six-digit PIN code to find district, state and any offer.';
            return;
        }
        message.textContent = 'Checking PIN code and district offer…';
        let serverFinished = false;
        const fillLocal = location => {
            if (version !== revision || !location || verifiedPin === value) return;
            district.value = location.district;
            state.value = location.state;
            message.textContent = location.district + ', ' + location.state + (serverFinished
                ? ' — reconnect to verify the offer and place your order.' : ' — checking order offer…');
        };
        const local = window.QuaraPincode?.cached(value);
        if (local) fillLocal(local);
        else window.QuaraPincode?.lookup(value).then(fillLocal).catch(() => {});
        {
            try {
                const response = await fetch(form.dataset.districtCheckout + '?' + new URLSearchParams({pin_code: value}), {headers: {Accept: 'application/json'}});
                const data = await response.json();
                if (version !== revision) return;
                if (!response.ok) throw new Error(data.errors?.pin_code?.[0] || 'Unable to check PIN code. Please retry.');
                district.value = data.district;
                state.value = data.state;
                window.QuaraPincode?.remember(value, data);
                verifiedPin = value;
                submit.disabled = false;
                message.textContent = data.district + ', ' + data.state;
                offerRow.hidden = !(data.discount > 0);
                document.getElementById('districtOfferAmount').textContent = '-₹' + Number(data.discount).toFixed(2);
                total.textContent = '₹' + Number(data.grand_total).toFixed(2);
                rounding.textContent = '+₹' + Number(data.rounding_adjustment).toFixed(2);
            } catch (error) {
                if (version === revision) {
                    lastRequestedPin = '';
                    message.textContent = district.value
                        ? district.value + ', ' + state.value + ' — reconnect to verify the offer and place your order.'
                        : error.message;
                }
            } finally {
                serverFinished = true;
            }
        }
    }
    pin.addEventListener('input', () => lookup());
    pin.addEventListener('change', () => lookup());
    document.getElementById('retryPinLookup').addEventListener('click', () => lookup(true));
    window.addEventListener('online', () => lookup(true));
    form.addEventListener('submit', event => {
        if (verifiedPin !== pin.value.trim()) {
            event.preventDefault();
            lookup(true);
        }
    });
    lookup();
});
