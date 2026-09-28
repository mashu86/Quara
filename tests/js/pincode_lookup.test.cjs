const {test} = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const directory = JSON.parse(fs.readFileSync('public/data/pincodes.json', 'utf8'));
const code = fs.readFileSync('public/js/pincode_autofill.js', 'utf8');
function browser(cachedDirectory, fetch) {
    const storage = new Map(cachedDirectory ? [['quara-pincodes-105d24c8', JSON.stringify(directory)]] : []);
    const context = {
        window: {}, URLSearchParams, fetch,
        localStorage: {getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value)},
        document: {currentScript: {dataset: {directoryUrl: '/data/pincodes.json', lookupUrl: '/address/pincode'}}, addEventListener() {}},
    };
    vm.runInNewContext(code, context);
    return {lookup: context.window.QuaraPincode, storage};
}
test('cached PIN directory works offline without a fetch', async () => {
    const {lookup} = browser(true, () => { throw Error('Network must not be used'); });
    assert.equal(lookup.cached('670001').district, 'Kannur');
    assert.equal((await lookup.lookup('670582')).state, 'Kerala');
    await assert.rejects(lookup.lookup('123'), /six-digit/);
});
test('first load downloads one compact directory and reuses it for different pins', async () => {
    let requests = 0;
    const {lookup, storage} = browser(false, async () => {
        requests++;
        return {ok: true, json: async () => directory};
    });
    await Promise.all([lookup.lookup('670001'), lookup.lookup('670582')]);
    assert.equal(requests, 1);
    assert.ok(storage.has('quara-pincodes-105d24c8'));
});
test('unknown PIN uses one shared verified fallback request and caches the result', async () => {
    let requests = 0;
    const {lookup} = browser(true, async () => {
        requests++;
        return {ok: true, json: async () => ({district: 'Kannur', state: 'Kerala'})};
    });
    await Promise.all([lookup.lookup('999998'), lookup.lookup('999998')]);
    await lookup.lookup('999998');
    assert.equal(requests, 1);
});
test('unknown offline PIN fails explicitly without inventing a district', async () => {
    const {lookup} = browser(true, async () => { throw Error('Offline'); });
    await assert.rejects(lookup.lookup('999999'), /Offline/);
    assert.equal(lookup.cached('999999'), null);
});
