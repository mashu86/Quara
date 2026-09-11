import test from 'node:test';
import assert from 'node:assert/strict';
import { detectGarmentType, parseMeasurement, suggestSize } from '../../public/js/product-size-suggestion.js';

test('detects the product name before broad category choices', () => {
    assert.equal(detectGarmentType('Floral Chiffon Korean Top', ['Dresses']), 'top');
    assert.equal(detectGarmentType('Cotton Kurta With Pants', ['New Arrivals']), 'kurti');
    assert.equal(detectGarmentType('Premium Floral', ['New Arrivals', 'Maxi Dresses']), 'dress');
    assert.equal(detectGarmentType('Silk Collection', ['Tops', 'Pants']), null);
    assert.equal(detectGarmentType('Embroidered Abaya'), 'abaya');
});

test('does not guess an adult regular-fit size for unsupported clothing', () => {
    for (const name of ['Kids Dress', 'Baby Gown', 'Mens Shirt', 'Silk Saree', 'Oversized Top', 'Free Size Dress', 'Embroidered Kaftan']) {
        assert.equal(detectGarmentType(name), null, name);
        assert.throws(() => suggestSize({ name, chest: 38 }), /supplier size manually/);
    }
});

test('parses decimal inches without silently truncating ranges or units', () => {
    for (const raw of ['38.5', '38.5 inches', '38.5 IN', '38.5"', '38.5″']) {
        assert.equal(parseMeasurement(raw, 'Chest'), 38.5);
    }
    assert.equal(parseMeasurement('', 'Chest'), null);
    for (const raw of ['38-40', '38/40', '38cm', '-38', '0', 'Infinity', '38abc', '151']) {
        assert.throws(() => parseMeasurement(raw, 'Chest'), /positive measurement/);
    }
});

test('suggests alpha sizes using chest and waist and rounds boundaries upward', () => {
    assert.equal(suggestSize({ name: 'Cotton Kurti', chest: 38, waist: 34, length: 42 }).size, 'M');
    assert.equal(suggestSize({ name: 'Cotton Kurti', chest: 38.5, waist: 34, length: 42 }).size, 'L');
    assert.equal(suggestSize({ name: 'Maxi Dress', chest: 38, waist: 36, length: 54 }).size, 'L');
    assert.equal(suggestSize({ name: 'Korean Top', chest: 42, length: 25 }).size, 'XL');
    assert.equal(suggestSize({ name: 'Cotton Kurti', chest: 52, waist: 48 }).size, '6XL');
});

test('flat mode doubles chest and waist but never length', () => {
    assert.equal(suggestSize({ name: 'Top', chest: 19, waist: 17, length: 25, basis: 'flat' }).size, 'M');
    const abaya = suggestSize({ name: 'Abaya', chest: 23, waist: 24, length: 56, basis: 'flat' });
    assert.equal(abaya.size, '56');
    assert.match(abaya.message, /Chest 46″, Waist 48″, Length 56″/);
});

test('bottoms depend on waist and abayas depend on length', () => {
    assert.equal(suggestSize({ name: 'Cotton Pants', waist: 33, length: 40 }).size, '34');
    assert.equal(suggestSize({ name: 'Cotton Pants', waist: 34, length: 45 }).size, '34');
    assert.equal(suggestSize({ name: 'Abaya', chest: 44, length: 55 }).size, '56');
    assert.equal(suggestSize({ name: 'Abaya', chest: 48, length: 55 }).size, '56');
});

test('explicit type supports unrecognized product names', () => {
    assert.equal(suggestSize({ name: 'New Collection', type: 'dress', chest: 40, waist: 36 }).size, 'L');
    assert.throws(() => suggestSize({ name: 'New Collection', chest: 40 }), /identify/);
    assert.throws(() => suggestSize({ type: 'invalid', chest: 40 }), /identify/);
});

test('requires the measurement used by each size convention', () => {
    assert.throws(() => suggestSize({ name: 'Top', waist: 34 }), /Enter Chest/);
    assert.throws(() => suggestSize({ name: 'Pants', chest: 38 }), /Enter Waist/);
    assert.throws(() => suggestSize({ name: 'Abaya', chest: 38 }), /Enter Length/);
});

test('rejects out-of-chart and conflicting measurements without clamping', () => {
    assert.throws(() => suggestSize({ name: 'Top', chest: 19 }), /outside/);
    assert.throws(() => suggestSize({ name: 'Top', chest: 54 }), /outside/);
    assert.throws(() => suggestSize({ name: 'Top', chest: 36, waist: 44 }), /do not match/);
    assert.throws(() => suggestSize({ name: 'Pants', waist: 18 }), /outside/);
    assert.throws(() => suggestSize({ name: 'Abaya', length: 30 }), /outside/);
    assert.throws(() => suggestSize({ name: 'Top', chest: 38, length: 'long' }), /Length/);
});
