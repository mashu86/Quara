import test from 'node:test';
import assert from 'node:assert/strict';
import { parseProductNotes, matchPriceCategories, requestImageAutoFill } from '../../public/js/product-auto-fill.js';

test('image request returns generated fields and surfaces API and session failures', async () => {
    const originalFetch = globalThis.fetch;
    try {
        const result = { success: true, name: 'Floral Top', description: 'A floral top.', size_master_id: 3 };
        globalThis.fetch = async () => ({ ok: true, json: async () => result });
        assert.deepEqual(await requestImageAutoFill('/image', new FormData()), result);
        for (const incomplete of [
            { ...result, description: '' },
            { ...result, description: '   ' },
            { ...result, name: null },
            { ...result, description: ['Invalid description'] },
        ]) {
            globalThis.fetch = async () => ({ ok: true, json: async () => incomplete });
            await assert.rejects(requestImageAutoFill('/image', new FormData()), /both a product name and description/);
        }
        globalThis.fetch = async () => ({ ok: false, status: 422, json: async () => ({ success: false, message: 'Saved key cannot be decrypted.' }) });
        await assert.rejects(requestImageAutoFill('/image', new FormData()), /Saved key cannot be decrypted/);
        globalThis.fetch = async () => ({ ok: false, status: 419, json: async () => { throw new SyntaxError(); } });
        await assert.rejects(requestImageAutoFill('/image', new FormData()), /session has expired/);
    } finally {
        globalThis.fetch = originalFetch;
    }
});

test('reads pasted price and compact measurements', () => {
    for (const text of ['169/-\nc40\nw40\nL40', '169 c40 w40 l40', '₹169\nChest:40 Waist=40 Length 40']) {
        assert.deepEqual(parseProductNotes(text), { values: { price: 169, chest: 40, waist: 40, length: 40 }, warnings: [] });
    }
});
test('reads comma-separated price, chest, waist and length in order', () => {
    for (const text of ['129,30,30,30', ' 129, 30, 30, 30 ', '₹129/-,30,30,30']) {
        assert.deepEqual(parseProductNotes(text), { values: { price: 129, chest: 30, waist: 30, length: 30 }, warnings: [] });
    }
    assert.deepEqual(parseProductNotes('129.50,30.5,28,42').values, { price: 129.5, chest: 30.5, waist: 28, length: 42 });
    assert.deepEqual(parseProductNotes('1,169').values, { price: 1169 });
    const invalid = parseProductNotes('129,0,30,999');
    assert.deepEqual(invalid.values, { price: 129, waist: 30 });
    assert.equal(invalid.warnings.length, 2);
});
test('accepts common spelling mistakes, decimals and optional fields', () => {
    assert.deepEqual(parseProductNotes('price 1,169/-\nchst 40.5\nweist 38\nlenght 42').values,
        { price: 1169, chest: 40.5, waist: 38, length: 42 });
    assert.deepEqual(parseProductNotes('169').values, { price: 169 });
    assert.deepEqual(parseProductNotes('c40').values, { chest: 40 });
    assert.deepEqual(parseProductNotes('chset 40 waost 38 lengthh 42').values, { chest: 40, waist: 38, length: 42 });
    assert.deepEqual(parseProductNotes(''), { values: {}, warnings: [] });
});
test('does not silently choose conflicting, invalid or ranged measurements', () => {
    for (const text of ['c40 c42', 'c38-40', 'chest 40cm', 'c0', 'c999']) {
        const result = parseProductNotes(text);
        assert.equal(result.values.chest, undefined, text);
        assert.ok(result.warnings.length, text);
    }
});
test('category matching prefers exact names and respects numeric boundaries', () => {
    const categories = ['169', 'Offer 169', '1169', '1699', '169.50'].map(name => ({ name }));
    assert.deepEqual(matchPriceCategories(169, categories), [{ name: '169' }]);
    assert.deepEqual(matchPriceCategories(169, categories.slice(1)), [{ name: 'Offer 169' }]);
    assert.equal(matchPriceCategories(169, [{ name: 'Offer 169' }, { name: 'Sale 169' }]).length, 2);
    assert.deepEqual(matchPriceCategories(200, categories), []);
});
test('Size Master matching uses chest then waist and never invents a match', async () => {
    globalThis.window = {};
    const { findBestMatchingMasterRow } = await import('../../public/js/product-size-suggestion.js');
    const rows = [{ size_label: 'M', chest: '38', waist: '36' }, { size_label: 'L', chest: '40', waist: '38' }];
    assert.equal(findBestMatchingMasterRow('', '40', '36', rows).size_label, 'L');
    assert.equal(findBestMatchingMasterRow('', '', '38', rows).size_label, 'L');
    assert.equal(findBestMatchingMasterRow('', '40', '', [{ size_label: 'M' }]), null);
    assert.equal(findBestMatchingMasterRow('', '', '', rows), null);
    assert.equal(findBestMatchingMasterRow('', '39.8', '', rows).size_label, 'L');
    assert.equal(findBestMatchingMasterRow('', '', '', [{ size_label: '54', length: '54' }, { size_label: '56', length: '56' }], '56').size_label, '56');
});

test('notes size autofill uses the selected image category embedded chart', async () => {
    await import('../../public/js/product-size-suggestion.js');
    const previousDocument = globalThis.document;
    const previousFetch = globalThis.fetch;
    globalThis.document = { getElementById: () => ({ value: '3', selectedOptions: [{ dataset: { chart: JSON.stringify([
        { size_label: 'M', chest: '38' }, { size_label: 'L', chest: '40' },
    ]) } }] }) };
    globalThis.fetch = () => { throw new Error('Embedded chart must not require another request'); };
    try {
        const row = { querySelector: selector => ({ value: selector.includes('chests') ? '40' : '' }) };
        assert.equal(await window.suggestProductNoteSize(row), 'L');
    } finally {
        globalThis.document = previousDocument;
        globalThis.fetch = previousFetch;
    }
});
