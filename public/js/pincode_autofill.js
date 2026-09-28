// Shared local-first postal lookup for checkout and admin sale forms.
(() => {
    const script = document.currentScript;
    const directoryUrl = script.dataset.directoryUrl;
    const lookupUrl = script.dataset.lookupUrl;
    const directoryKey = 'quara-pincodes-105d24c8';
    const resultsKey = 'quara-verified-pins-v1';
    const read = key => { try { return JSON.parse(localStorage.getItem(key)); } catch { return null; } };
    const save = (key, value) => { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* Storage may be disabled. */ } };
    let directory = read(directoryKey);
    const results = read(resultsKey) || {};
    const pending = new Map();
    let loading;
    const valid = value => value && typeof value.district === 'string' && typeof value.state === 'string';
    function cached(pin) {
        const index = directory?.pins?.[pin];
        const location = index !== undefined ? directory?.locations?.[index] : null;
        if (Array.isArray(location) && location.length === 2) return {district: location[0], state: location[1]};
        return valid(results[pin]) ? results[pin] : null;
    }
    function remember(pin, location) {
        if (valid(location)) {
            results[pin] = {district: location.district, state: location.state};
            save(resultsKey, results);
        }
    }
    function preload() {
        if (directory?.pins && directory?.locations) return Promise.resolve();
        if (!loading) loading = fetch(directoryUrl, {cache: 'force-cache'})
            .then(response => { if (!response.ok) throw new Error('Directory unavailable'); return response.json(); })
            .then(data => { directory = data; save(directoryKey, data); })
            .catch(() => { loading = null; });
        return loading;
    }
    async function lookup(pin) {
        if (!/^[1-9][0-9]{5}$/.test(pin)) throw new Error('Enter a valid six-digit PIN code.');
        const immediate = cached(pin);
        if (immediate) return immediate;
        await preload();
        if (cached(pin)) return cached(pin);
        if (!pending.has(pin)) {
            pending.set(pin, fetch(lookupUrl + '?' + new URLSearchParams({pin_code: pin}), {headers: {Accept: 'application/json'}})
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || !valid(data)) throw new Error(data.errors?.pin_code?.[0] || 'PIN lookup unavailable. Please retry when connected.');
                    remember(pin, data);
                    return data;
                }).finally(() => pending.delete(pin)));
        }
        return pending.get(pin);
    }
    window.QuaraPincode = {cached, lookup, remember, preload};

    document.addEventListener('DOMContentLoaded', () => {
        const selector = 'input[name="pin_code"], input[name="pincode"], input[name="postal_code"]';
        function setup(pin) {
            if (pin.dataset.pincodeListener) return;
            pin.dataset.pincodeListener = 'true';
            const form = pin.closest('form') || document;
            const state = form.querySelector('[name="state"]');
            const district = form.querySelector('[name="district"]');
            if (!state || !district) return;
            const message = document.createElement('div');
            message.className = 'form-text small mb-2';
            message.setAttribute('role', 'status');
            pin.insertAdjacentElement('afterend', message);
            let version = 0;
            let lastPin = '';
            async function update() {
                const value = pin.value.trim();
                if (value === lastPin) return;
                lastPin = value;
                const request = ++version;
                state.value = '';
                district.value = '';
                district.dispatchEvent(new Event('change', {bubbles: true}));
                if (!/^[1-9][0-9]{5}$/.test(value)) {
                    message.textContent = 'Enter a six-digit PIN code.';
                    return;
                }
                message.textContent = 'Finding district and state…';
                try {
                    const data = cached(value) || await lookup(value);
                    if (request !== version) return;
                    state.value = data.state;
                    district.value = data.district;
                    message.textContent = data.district + ', ' + data.state;
                    district.dispatchEvent(new Event('change', {bubbles: true}));
                } catch (error) {
                    if (request !== version) return;
                    message.textContent = error.message;
                    lastPin = '';
                }
            }
            pin.addEventListener('input', update);
            pin.addEventListener('change', update);
            window.addEventListener('online', () => { lastPin = ''; update(); });
        }
        function init() {
            const pins = document.querySelectorAll(selector);
            if (pins.length) preload();
            pins.forEach(setup);
        }
        init();
        new MutationObserver(init).observe(document.body, {childList: true, subtree: true});
    });
})();
