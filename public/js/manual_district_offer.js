document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-manual-district-offer]');
    if (!form) return;
    const box = document.getElementById('manualDistrictOffer');
    const message = document.getElementById('manualDistrictOfferMessage');
    const choices = Array.from(form.querySelectorAll('[name="provide_district_offer"]'));
    let offer = null;
    let pending = false;
    let failed = false;
    let version = 0;
    let timer;
    const money = amount => '₹' + amount.toFixed(2);

    window.manualDistrictDiscount = base => {
        const eligible = offer ? Math.min(Math.max(0, base), Math.round((offer.method === 'percentage' ? base * Number(offer.value) / 100 : Number(offer.value)) * 100) / 100) : 0;
        document.getElementById('manualDistrictOfferAmount').textContent = money(eligible);
        const accepted = choices.some(choice => choice.checked && choice.value === '1');
        const amount = accepted ? eligible : 0;
        document.getElementById('manualDistrictDiscountRow').hidden = amount <= 0;
        document.getElementById('manualDistrictDiscountValue').textContent = '-' + money(amount);
        return amount;
    };

    function refresh() {
        const current = ++version;
        clearTimeout(timer);
        pending = true;
        failed = false;
        offer = null;
        box.hidden = false;
        message.textContent = 'Checking district offer…';
        choices.forEach(choice => { choice.disabled = true; choice.required = false; });
        if (typeof calcTotals === 'function') calcTotals();
        timer = setTimeout(async () => {
            try {
                const params = new URLSearchParams({district: form.elements.district.value, state: form.elements.state.value || 'Kerala', sale_date: form.elements.sale_date.value});
                const response = await fetch(form.dataset.manualDistrictOffer + '?' + params, {headers: {Accept: 'application/json'}});
                const data = await response.json();
                if (current !== version) return;
                if (!response.ok) throw new Error('Check district and sale date, then retry the offer check.');
                offer = data.offer;
                box.hidden = !offer;
                message.textContent = offer ? offer.district + ': ' + (offer.method === 'percentage' ? offer.value + '%' : money(Number(offer.value))) + ' off' : '';
                choices.forEach(choice => { choice.disabled = !offer; choice.required = !!offer; });
            } catch (error) {
                if (current !== version) return;
                failed = true;
                message.textContent = error.message;
            } finally {
                if (current === version) {
                    pending = false;
                    if (typeof calcTotals === 'function') calcTotals();
                }
            }
        }, 250);
    }
    ['district', 'state', 'sale_date'].forEach(name => {
        ['input', 'change'].forEach(event => form.elements[name].addEventListener(event, () => {
            choices.forEach(choice => { choice.checked = false; });
            refresh();
        }));
    });
    choices.forEach(choice => choice.addEventListener('change', () => calcTotals()));
    document.getElementById('retryManualOffer').addEventListener('click', refresh);
    form.addEventListener('submit', event => {
        if (pending || failed || (offer && !choices.some(choice => choice.checked))) {
            event.preventDefault();
            box.hidden = false;
            box.scrollIntoView({block: 'center'});
            if (failed) refresh();
        }
    });
    refresh();
});
