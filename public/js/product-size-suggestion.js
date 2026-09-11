const garmentTypes = {
    kurti: 'Kurti / Kurta / Salwar set',
    top: 'Top / Shirt / Blouse',
    dress: 'Dress / Gown / Maxi',
    bottom: 'Pants / Skirt / Bottom',
    abaya: 'Abaya',
};

const letterSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL', '6XL'];
const unsupported = /\b(kids?|child(?:ren)?|baby|boys?|girls?|mens?|men's|sarees?|saris?|unstitched|kaftans?|caftans?|oversized?|free[ -]?size)\b/i;
const typePatterns = [
    ['abaya', /\babayas?\b/i],
    ['kurti', /\b(kurt[ai]s?|kurtis|salwar|churidar|anarkali)\b/i],
    ['dress', /\b(dress(?:es)?|gowns?|maxi|midi|frocks?)\b/i],
    ['bottom', /\b(pants?|trousers?|jeans|skirts?|palazzos?|leggings?|bottoms?|shorts?)\b/i],
    ['top', /\b(tops?|shirts?|blouses?|t[ -]?shirts?|tunics?)\b/i],
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
        throw new Error(`${label}: enter one positive measurement in inches, e.g. 38 or 38.5. Ranges need a manual size label.`);
    }
    return number;
}

export function suggestSize({ name, categories = [], type = 'auto', basis = 'circumference', chest, waist, length }) {
    const detectedType = type === 'auto' ? detectGarmentType(name, categories) : type;
    if (!Object.hasOwn(garmentTypes, detectedType)) {
        throw new Error('Could not identify a supported adult garment type. Select the product type, or enter its supplier size manually.');
    }
    if (!['circumference', 'flat'].includes(basis)) throw new Error('Choose how Chest / Waist were measured.');
    const multiplier = basis === 'flat' ? 2 : 1;
    const chestInches = parseMeasurement(chest, 'Chest');
    const waistInches = parseMeasurement(waist, 'Waist');
    const lengthInches = parseMeasurement(length, 'Length');
    const fullChest = chestInches === null ? null : chestInches * multiplier;
    const fullWaist = waistInches === null ? null : waistInches * multiplier;
    const measurementText = [
        fullChest === null ? null : `Chest ${fullChest}″`,
        fullWaist === null ? null : `Waist ${fullWaist}″`,
        lengthInches === null ? null : `Length ${lengthInches}″`,
    ].filter(Boolean).join(', ');
    let size;
    let explanation;

    if (detectedType === 'abaya') {
        if (lengthInches === null) throw new Error('Enter Length to suggest an abaya size.');
        if (lengthInches < 48 || lengthInches > 62) throw new Error('This length is outside the adult abaya guide (48–62 inches). Enter the supplier size manually.');
        size = String(Math.round(lengthInches / 2) * 2);
        explanation = 'Length-based abaya size; verify chest and waist fit with the supplier.';
    } else if (detectedType === 'bottom') {
        if (fullWaist === null) throw new Error('Enter Waist to suggest a bottom size.');
        if (fullWaist < 24 || fullWaist > 52) throw new Error('This waist is outside the adult bottom guide (24–52 inches). Enter the supplier size manually.');
        size = String(Math.ceil(fullWaist / 2) * 2);
        explanation = 'Numeric waist size; length does not determine the waist label.';
    } else {
        if (fullChest === null) throw new Error('Enter Chest to suggest this garment size.');
        if (fullChest < 34 || fullChest > 52) throw new Error('This chest is outside the suggested chart (34–52 inches). Check the measurement basis or enter the supplier size.');
        const chestIndex = Math.ceil((fullChest - 34) / 2);
        const waistIndex = fullWaist === null ? chestIndex : Math.ceil((fullWaist - 30) / 2);
        if (fullWaist !== null && (fullWaist < 26 || fullWaist > 48 || Math.abs(chestIndex - waistIndex) > 2)) {
            throw new Error('Chest and waist do not match this regular-fit chart. Check the measurements or enter the supplier size manually.');
        }
        size = letterSizes[Math.max(chestIndex, waistIndex)];
        explanation = 'Approximate regular-fit size from chest and waist; length varies by style.';
    }

    return { size, type: detectedType, message: `${garmentTypes[detectedType]} · ${measurementText}. ${explanation}` };
}

function initializeSizeSuggestions() {
    const controls = document.getElementById('product-size-suggestion-controls');
    if (!controls) return;
    const form = controls.closest('form');
    const typeSelect = controls.querySelector('[data-size-type]');
    const basisSelect = controls.querySelector('[data-size-basis]');
    const typeStatus = controls.querySelector('[data-size-type-status]');
    const categoryNames = () => Array.from(form.querySelectorAll('.category-checkbox:checked')).map((checkbox) => checkbox.closest('label').textContent.trim());
    const currentName = () => form.querySelector('input[name="name"]').value;
    const updateType = () => {
        const detected = typeSelect.value === 'auto' ? detectGarmentType(currentName(), categoryNames()) : typeSelect.value;
        typeStatus.textContent = detected ? `Type: ${garmentTypes[detected]}` : 'Add a product name/category or select its type.';
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
            button.title = 'Suggest size from product type and measurements';
            button.setAttribute('aria-label', 'Suggest size from product type and measurements');
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
                if (event.target === input) {
                    message.textContent = '';
                } else if (['chest', 'waist', 'length'].some((field) => event.target === measurement(field)) && message.textContent) {
                    message.className = 'small mt-2 text-muted';
                    message.textContent = 'Measurements changed. Click the magic button again to update the size.';
                }
            });
        });
    };

    controls.addEventListener('change', markChanged);
    form.querySelector('input[name="name"]').addEventListener('input', markChanged);
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
