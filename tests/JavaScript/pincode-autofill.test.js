import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

function field(value = '') {
    return { value, dataset: {}, listeners: {}, addEventListener(name, fn) { this.listeners[name] = fn; },
        dispatchEvent(event) { this.listeners[event.type]?.(event); } };
}

function setup() {
    const pin = field(''), district = field('Existing district'), state = field('Existing state');
    const toggle = field(); toggle.checked = true;
    const form = { querySelector: selector => ({ '[name="state"]': state, '[name="district"]': district, '[data-auto-pincode]': toggle })[selector] };
    pin.closest = () => form;
    pin.insertAdjacentElement = () => {};
    let resolve;
    let requests = 0;
    const document = {
        currentScript: { dataset: { directoryUrl: '/directory', lookupUrl: '/lookup' } }, body: {},
        addEventListener: (_, fn) => fn(), querySelectorAll: () => [pin],
        createElement: () => ({ setAttribute() {} }),
    };
    const context = {
        document, window: {}, Event, URLSearchParams,
        MutationObserver: class { observe() {} },
        localStorage: { getItem: key => key.includes('pincodes-') ? JSON.stringify({ pins: {}, locations: [] }) : null, setItem() {} },
        fetch: () => { requests++; return new Promise(done => { resolve = done; }); },
    };
    vm.runInNewContext(fs.readFileSync('public/js/pincode_autofill.js', 'utf8'), context);
    return { pin, district, state, toggle, requests: () => requests,
        finish: () => resolve({ ok: true, json: async () => ({ district: 'Kannur', state: 'Kerala' }) }) };
}

test('manual mode does not look up or clear entered district/state', async () => {
    const f = setup();
    f.toggle.checked = false; f.toggle.dispatchEvent(new Event('change'));
    f.pin.value = '670001'; await f.pin.listeners.input();
    assert.equal(f.requests(), 0);
    assert.equal(f.district.value, 'Existing district');
    assert.equal(f.state.value, 'Existing state');
});

test('automatic results remain editable and late responses respect manual changes or switch off', async () => {
    for (const action of ['none', 'edit', 'off']) {
        const f = setup();
        f.pin.value = '670001';
        const pending = f.pin.listeners.input();
        await new Promise(resolve => setImmediate(resolve));
        if (action === 'edit') { f.district.value = 'Manual district'; f.district.dispatchEvent(new Event('input')); }
        if (action === 'off') { f.toggle.checked = false; f.toggle.dispatchEvent(new Event('change')); }
        f.finish(); await pending;
        assert.equal(f.district.value, action === 'none' ? 'Kannur' : action === 'edit' ? 'Manual district' : 'Existing district');
        if (action === 'none') {
            f.state.value = 'Manual state'; f.state.dispatchEvent(new Event('input'));
            await f.pin.listeners.change();
            assert.equal(f.state.value, 'Manual state');
        }
    }
});
