const garmentTypes = {
    korean_top: 'Korean top (regular fit)',
    korean_crop_top: 'Korean crop top (fitted)',
    normal_top: 'Normal top (regular fit)',
    ladies_shirt: 'Ladies shirt (regular fit)',
    overcoat: 'Overcoat / jacket (layering fit)',
};

const letterSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL', '6XL'];

// Product entry uses FINISHED GARMENT chest circumference, not the customer's body bust.
// Crop tops use about 2 inches ease, regular tops/shirts 4 inches, and outerwear 6 inches.
const garmentChestStandards = {
    korean_crop_top: [34, 36, 38, 40, 42, 44, 46, 48, 50, 52],
    korean_top: [36, 38, 40, 42, 44, 46, 48, 50, 52, 54],
    normal_top: [36, 38, 40, 42, 44, 46, 48, 50, 52, 54],
    ladies_shirt: [36, 38, 40, 42, 44, 46, 48, 50, 52, 54],
    overcoat: [38, 40, 42, 44, 46, 48, 50, 52, 54, 56],
};

const unsupported = /\b(kids?|child(?:ren)?|baby|boys?|girls?|mens?|men's|sarees?|saris?|unstitched|kaftans?|caftans?|oversized?|free[ -]?size)\b/i;
const typePatterns = [
    ['korean_crop_top', /\b(korean|korean-style)\b.*\b(crop|cropped)\b|\b(crop|cropped)\b.*\b(korean|korean-style)\b/i],
    ['overcoat', /\b(overcoats?|coats?|jackets?|blazers?)\b/i],
    ['ladies_shirt', /\b(ladies'?|women'?s?|womens?)\s+shirts?\b|\bshirts?\b/i],
    ['korean_top', /\b(korean|korean-style)\b.*\btops?\b|\bkorean\b/i],
    ['normal_top', /\b(tops?|blouses?|t[ -]?shirts?|tunics?)\b/i],
];

export function detectGarmentType(name, categories = []) {
    const title = String(name || '');
    if (unsupported.test(title)) return null;
    const namedType = typePatterns.find(([, pattern]) => pattern.test(title));
    if (namedType) return namedType[0];
    const categoryText = categories.join(' ');
    if (unsupported.test(categoryText)) return null;
    const matches = typePatterns.filter(([, pattern]) => pattern.test(categoryText));
    return matches.length === 1 ? matches[0][0] : null;
}

export function parseMeasurement(raw, label) {
    const value = String(raw ?? '').trim();
    if (!value) return null;
    const match = value.match(/^(\d+(?:\.\d+)?|\.\d+)\s*(?:inches|inch|in|["″”])?$/i);
    const number = match ? Number(match[1]) : NaN;
    if (!Number.isFinite(number) || number <= 0 || number > 150) {
        throw new Error(`${label}: enter one positive measurement in inches, e.g. 38 or 38.5.`);
    }
    return number;
}

function nearestSize(fullChest, standard) {
    let closestIndex = 0;
    for (let index = 1; index < standard.length; index += 1) {
        if (Math.abs(standard[index] - fullChest) < Math.abs(standard[closestIndex] - fullChest)) closestIndex = index;
    }
    if (Math.abs(standard[closestIndex] - fullChest) > 1) return null;
    return closestIndex;
}

export function suggestSize({ name, categories = [], type = 'auto', basis = 'circumference', chest, waist, length }) {
    const detectedType = type === 'auto' ? detectGarmentType(name, categories) : type;
    if (!Object.hasOwn(garmentTypes, detectedType)) {
        throw new Error('Select one of the supported ladieswear product types, or enter the supplier size manually.');
    }
    if (!['circumference', 'flat'].includes(basis)) throw new Error('Choose how the finished garment was measured.');
    const multiplier = basis === 'flat' ? 2 : 1;
    const chestInches = parseMeasurement(chest, 'Chest');
    const waistInches = parseMeasurement(waist, 'Waist');
    const lengthInches = parseMeasurement(length, 'Length');
    if (chestInches === null) throw new Error('Enter the finished garment chest to suggest a size.');

    const fullChest = chestInches * multiplier;
    const standard = garmentChestStandards[detectedType];
    const index = nearestSize(fullChest, standard);
    if (index === null) {
        throw new Error(`Finished garment chest ${fullChest}″ is outside this ${garmentTypes[detectedType]} chart. Check the measurement or use the supplier label.`);
    }

    const measurementText = [`Finished chest ${fullChest}″`];
    if (waistInches !== null) measurementText.push(`waist ${waistInches * multiplier}″`);
    if (lengthInches !== null) measurementText.push(`length ${lengthInches}″`);
    return {
        size: letterSizes[index],
        type: detectedType,
        message: `${garmentTypes[detectedType]} · ${measurementText.join(', ')}. Matched to the finished-garment chart; supplier size label takes priority.`,
    };
}

function initializeSizeSuggestions() {
    const controls = document.getElementById('product-size-suggestion-controls');
    if (!controls) return;
    const form = controls.closest('form');
    const typeSelect = controls.querySelector('[data-size-type]');
    const basisSelect = controls.querySelector('[data-size-basis]');
    const typeStatus = controls.querySelector('[data-size-type-status]');
    const categoryNames = () => Array.from(form.querySelectorAll('.category-checkbox:checked')).map((checkbox) => checkbox.closest('label').textContent.trim());
    const currentName = () => form.querySelector('input[name="name"]')?.value || '';
    const updateType = () => {
        const detected = typeSelect.value === 'auto' ? detectGarmentType(currentName(), categoryNames()) : typeSelect.value;
        typeStatus.textContent = detected ? `Type: ${garmentTypes[detected]}` : 'Add a product name/category or select its product type.';
    };
    const markChanged = () => {
        updateType();
        form.querySelectorAll('[data-size-suggestion-message]').forEach((message) => {
            if (message.textContent) message.textContent = 'Product details changed. Click the magic button again to update the suggestion.';
        });
    };
    const enhanceRows = () => {
        form.querySelectorAll('input[name="sizes[]"], input[name="new_sizes[]"], input[name^="existing_sizes["]').forEach((input) => {
            if (input.dataset.sizeSuggestionReady) return;
            input.dataset.sizeSuggestionReady = '1';
            const grid = input.closest('.row');
            const row = grid.parentElement;
            const group = document.createElement('div');
            group.className = 'input-group input-group-sm flex-nowrap';
            input.before(group);
            group.append(input);
            input.classList.remove('rounded-3');
            input.style.minWidth = '0';
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-warning px-2';
            button.style.minWidth = '34px';
            button.style.minHeight = '32px';
            button.title = 'Suggest size from finished garment chest';
            button.setAttribute('aria-label', 'Suggest size from finished garment chest');
            button.dataset.suggestSize = '1';
            button.innerHTML = '<svg width="16" height="16" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 13 8-8 2 2-8 8z M9 7l2 2 M4 2v4 M2 4h4 M12 1v3 M10.5 2.5h3 M14 10v4 M12 12h4"/></svg>';
            group.append(button);
            const message = document.createElement('div');
            message.className = 'small mt-2';
            message.dataset.sizeSuggestionMessage = '1';
            message.setAttribute('role', 'status');
            row.append(message);
            const measurement = (field) => row.querySelector(`input[name="${field}s[]"], input[name="new_${field}s[]"], input[name^="existing_${field}s["]`);
            button.addEventListener('click', () => {
                updateType();
                try {
                    const result = suggestSize({ name: currentName(), categories: categoryNames(), type: typeSelect.value, basis: basisSelect.value, chest: measurement('chest')?.value, waist: measurement('waist')?.value, length: measurement('length')?.value });
                    const previousSize = input.value;
                    input.value = result.size;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    message.className = 'small mt-2 text-success';
                    message.textContent = `Suggested ${result.size}. ${result.message} `;
                    if (previousSize !== result.size) {
                        const undo = document.createElement('button');
                        undo.type = 'button';
                        undo.className = 'btn btn-link btn-sm p-0 align-baseline';
                        undo.textContent = 'Undo';
                        undo.addEventListener('click', () => {
                            input.value = previousSize;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                            message.textContent = 'Previous size restored.';
                        });
                        message.append(undo);
                    }
                } catch (error) {
                    message.className = 'small mt-2 text-danger';
                    message.textContent = error.message;
                }
            });
            grid.addEventListener('input', (event) => {
                if (event.target === input) message.textContent = '';
                else if (['chest', 'waist', 'length'].some((field) => event.target === measurement(field)) && message.textContent) {
                    message.className = 'small mt-2 text-muted';
                    message.textContent = 'Measurements changed. Click the magic button again to update the size.';
                }
            });
        });
    };
    controls.addEventListener('change', markChanged);
    form.querySelector('input[name="name"]')?.addEventListener('input', markChanged);
    form.querySelectorAll('.category-checkbox').forEach((checkbox) => checkbox.addEventListener('change', markChanged));
    enhanceRows();
    updateType();
    const observer = new MutationObserver(enhanceRows);
    ['sizeRowsContainer', 'newSizesContainer'].forEach((id) => {
        const container = document.getElementById(id);
        if (container) observer.observe(container, { childList: true });
    });
}

if (typeof document !== 'undefined') initializeSizeSuggestions();
