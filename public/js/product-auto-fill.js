// Shared by product create and edit. Notes never require an AI request.
const aliases = {
    c: 'chest', chest: 'chest', ches: 'chest', chst: 'chest', cheast: 'chest',
    h: 'hip', hip: 'hip', hips: 'hip', hpp: 'hip', hiip: 'hip',
    w: 'waist', waist: 'waist', weist: 'waist', wast: 'waist', wait: 'waist',
    l: 'length', length: 'length', lenght: 'length', lenth: 'length', len: 'length', lwngth: 'length', lengh: 'length',
    price: 'price', rate: 'price', rs: 'price', inr: 'price',
};

function measurementKey(label) {
    if (aliases[label]) return aliases[label];
    if (label.length < 4) return null;
    const close = ['chest', 'waist', 'hip', 'length'].filter(word => {
        if (Math.abs(word.length - label.length) > 1) return false;
        const distance = Array.from({ length: word.length + 1 }, (_, i) => [i]);
        for (let j = 0; j <= label.length; j++) distance[0][j] = j;
        for (let i = 1; i <= word.length; i++) for (let j = 1; j <= label.length; j++) {
            distance[i][j] = Math.min(distance[i - 1][j] + 1, distance[i][j - 1] + 1,
                distance[i - 1][j - 1] + (word[i - 1] === label[j - 1] ? 0 : 1));
            if (i > 1 && j > 1 && word[i - 1] === label[j - 2] && word[i - 2] === label[j - 1]) {
                distance[i][j] = Math.min(distance[i][j], distance[i - 2][j - 2] + 1);
            }
        }
        return distance[word.length][label.length] <= 1;
    });
    return close.length === 1 ? close[0] : null;
}

export function parseProductNotes(text, measurementType = (typeof document !== 'undefined' ? document.querySelector('input[name="measurement_type"]:checked')?.value : null) || 'up') {
    const values = {}, warnings = [], blocked = new Set();
    const assign = (key, raw) => {
        const value = Number(raw.replaceAll(',', ''));
        if (!Number.isFinite(value) || value <= 0 || (key !== 'price' && value > 150)) {
            warnings.push(`Check ${key}: ${raw}.`); return;
        }
        if (blocked.has(key)) return;
        if (values[key] !== undefined && values[key] !== value) {
            delete values[key]; blocked.add(key);
            warnings.push(`Conflicting ${key} values; please enter it manually.`); return;
        }
        values[key] = value;
    };
    let rest = text.trim();
    const downPositional = measurementType === 'down'
        ? rest.match(/^(?:â‚¹\s*)?(\d+(?:\.\d+)?)(?:\s*\/-)?\s*,\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)$/)
        : null;
    if (downPositional) {
        ['price', 'hip', 'length'].forEach((key, index) => assign(key, downPositional[index + 1]));
        return { values, warnings };
    }
    // Four unlabelled comma-separated values have a fixed field order.
    // Match before the labelled parser, which accepts thousands separators.
    const positional = rest.match(/^(?:₹\s*)?(\d+(?:\.\d+)?)(?:\s*\/-)?\s*,\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)$/);
    if (positional) {
        const order = ['price', 'chest', 'waist', 'length'];
        order.forEach((key, index) => assign(key, positional[index + 1]));
        return { values, warnings };
    }
    const leading = rest.match(/^(?:₹\s*)?(\d+(?:,\d{3})*(?:\.\d+)?)(?:\s*\/-)?(?=\s|[,;|]|$)/);
    if (leading) { assign('price', leading[1]); rest = rest.slice(leading[0].length); }
    rest = rest.replace(/([a-z]+)\s*[:=]?\s*(\d+(?:,\d{3})*(?:\.\d+)?)(\s*(?:[-/]\s*\d+|cm\b))?/gi, (whole, label, number, invalid) => {
        const key = measurementKey(label.toLowerCase());
        if (!key || invalid) { warnings.push(`Could not read "${whole.trim()}"; check it manually.`); return ''; }
        assign(key, number); return '';
    });
    rest = rest.replace(/(\d+(?:,\d{3})*(?:\.\d+)?)\s*([a-z]+)/gi, (whole, number, label) => {
        const key = measurementKey(label.toLowerCase());
        if (!key || key === 'price') return whole;
        assign(key, number); return '';
    });
    rest = rest.replace(/inches|inch|in\b|rs\b|₹|[\s,;|/\-"'″.]+/gi, '');
    if (rest) warnings.push(`Unrecognized notes: ${rest}. Please check the fields.`);
    return { values, warnings };
}

export function matchPriceCategories(price, categories) {
    const exact = categories.filter(c => c.name.trim() === String(price));
    if (exact.length) return exact;
    return categories.filter(c => (c.name.match(/\d+(?:,\d{3})*(?:\.\d+)?/g) || [])
        .some(n => Number(n.replaceAll(',', '')) === price));
}

const field = (row, name) => row.querySelector(`input[name="${name}[]"], input[name="new_${name}[]"], input[name^="existing_${name}["]`);
const sizeRows = () => [...document.querySelectorAll('.size-row, #newSizesContainer > div')].filter(row => field(row, 'sizes'));

export async function requestImageAutoFill(url, data) {
    const response = await fetch(url, { method: 'POST', body: data, headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    let result;
    try { result = await response.json(); }
    catch {
        throw new Error(response.status === 419 || response.redirected
            ? 'Your session has expired. Refresh the page and sign in again before retrying.'
            : `Image auto-fill failed (HTTP ${response.status}). Please retry.`);
    }
    if (!response.ok || !result.success) throw new Error(result.message || 'Image auto-fill failed.');
    if (typeof result.name !== 'string' || !result.name.trim()
        || typeof result.description !== 'string' || !result.description.trim()) {
        throw new Error('The image analysis did not return both a product name and description. Please retry with a clearer clothing photo.');
    }
    return result;
}

function setValue(input, value) {
    if (!input || input.disabled || input.readOnly) return;
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

function initialize() {
    const notes = document.getElementById('aiProductNotes');
    if (!notes) return;
    const whatsappBookedCheckbox = document.getElementById('aiWhatsAppBooked');
    const whatsappBookingDetails = document.getElementById('aiWhatsAppBookingDetails');
    const whatsappBookingDate = document.getElementById('aiWhatsAppBookingDate');
    const bookingSwitch = formBookingSwitch();
    const bookingTypeSelect = document.querySelector('#productBookingTypeCreate, #productBookingTypeEdit');
    const bookingCustomerField = document.querySelector('#bookedByCreateCustomerField, #bookedByEditCustomerField');
    const bookingCustomerLabel = document.querySelector('#bookedByCreateLabel, #bookedByEditLabel');
    const bookingCustomerInput = document.querySelector('#bookedByCreateInput, #bookedByEditInput');
    const productBookingDate = document.querySelector('#productBookingDateCreate, #productBookingDateEdit');
    function formBookingSwitch() {
        return document.querySelector('#isOutOfStockCreateSwitch, #isOutOfStockEditSwitch');
    }
    const setWhatsAppBookingDate = () => {
        const today = new Date();
        const localDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        if (whatsappBookingDate) whatsappBookingDate.value = localDate;
    };
    const syncWhatsAppBookingDetails = () => {
        if (!whatsappBookedCheckbox) return;
        whatsappBookingDetails?.classList.toggle('d-none', !whatsappBookedCheckbox.checked);
        if (whatsappBookingDate) whatsappBookingDate.disabled = !whatsappBookedCheckbox.checked;
        if (whatsappBookedCheckbox.checked) {
            setWhatsAppBookingDate();
            if (bookingSwitch && !bookingSwitch.disabled) {
                bookingSwitch.checked = true;
                if (typeof window.toggleBookedByContainer === 'function') window.toggleBookedByContainer(bookingSwitch);
                if (typeof window.toggleBookedByEditContainer === 'function') window.toggleBookedByEditContainer(bookingSwitch);
                if (bookingTypeSelect) bookingTypeSelect.value = 'business_whatsapp';
                if (productBookingDate) productBookingDate.value = whatsappBookingDate?.value || '';
                bookingSwitch.dataset.aiBusinessBooking = 'true';
            }
        } else if (bookingSwitch?.dataset.aiBusinessBooking === 'true') {
            bookingSwitch.checked = false;
            if (typeof window.toggleBookedByContainer === 'function') window.toggleBookedByContainer(bookingSwitch);
            if (typeof window.toggleBookedByEditContainer === 'function') window.toggleBookedByEditContainer(bookingSwitch);
            if (bookingTypeSelect?.value === 'business_whatsapp') bookingTypeSelect.value = '';
            if (productBookingDate) productBookingDate.value = '';
            bookingSwitch.dataset.aiBusinessBooking = '';
        }
        syncProductBookingFields();
    };

    function syncProductBookingFields() {
        if (!bookingSwitch || !bookingTypeSelect || !bookingCustomerInput) return;
        const isBooked = bookingSwitch.checked;
        const bookingType = bookingTypeSelect.value;
        const isBusinessBooking = bookingType === 'business_whatsapp';
        const legacyBooking = bookingTypeSelect.dataset.legacyBooking === 'true' && !bookingType;
        bookingTypeSelect.required = isBooked && !legacyBooking;
        if (productBookingDate) productBookingDate.required = isBooked && !legacyBooking;
        bookingCustomerField?.classList.toggle('d-none', isBusinessBooking);
        bookingCustomerInput.required = isBooked && !!bookingType && !isBusinessBooking;
        if (isBusinessBooking) bookingCustomerInput.value = '';
        if (bookingCustomerLabel) {
            bookingCustomerLabel.innerHTML = bookingType === 'instagram_customer'
                ? 'Instagram name or customer name <span class="text-danger">*</span>'
                : 'Customer name or phone <span class="text-danger">*</span>';
        }
        bookingCustomerInput.placeholder = bookingType === 'instagram_customer' ? 'Instagram name or customer name' : 'Name or phone number';
    }
    bookingSwitch?.addEventListener('change', () => {
        if (bookingSwitch.checked) bookingSwitch.dataset.aiBusinessBooking = '';
        syncProductBookingFields();
    });
    bookingTypeSelect?.addEventListener('change', () => {
        if (bookingSwitch) bookingSwitch.dataset.aiBusinessBooking = '';
        syncProductBookingFields();
    });
    syncProductBookingFields();
    whatsappBookedCheckbox?.addEventListener('change', syncWhatsAppBookingDetails);
    syncWhatsAppBookingDetails();
    const picker = document.getElementById('aiSizeRow');
    const measurementTypeInputs = [...document.querySelectorAll('input[name="measurement_type"]')];
    const syncMeasurementFields = () => {
        const isDown = document.querySelector('input[name="measurement_type"]:checked')?.value === 'down';
        document.querySelectorAll('.measurement-up').forEach(el => el.classList.toggle('d-none', isDown));
        document.querySelectorAll('.measurement-down').forEach(el => el.classList.toggle('d-none', !isDown));
    };
    measurementTypeInputs.forEach(input => input.addEventListener('change', syncMeasurementFields));
    syncMeasurementFields();
    let rowOptions = [];
    const refreshRows = () => {
        const rows = sizeRows();
        const previous = picker.value === '' ? null : rowOptions[Number(picker.value)];
        rowOptions = rows;
        picker.replaceChildren(new Option('Choose a size row', ''));
        rows.forEach((row, i) => picker.add(new Option(`Row ${i + 1}${field(row, 'sizes').value ? ' — ' + field(row, 'sizes').value : ''}`, String(i))));
        const selected = rows.indexOf(previous);
        picker.value = rows.length === 1 ? '0' : selected >= 0 ? String(selected) : '';
        document.getElementById('aiSizeRowPicker').classList.toggle('d-none', rows.length <= 1);
    };
    refreshRows();
    const form = notes.closest('form');
    new MutationObserver(records => {
        if (records.some(r => [...r.addedNodes, ...r.removedNodes].some(n => n.nodeType === 1 && (n.matches('.size-row') || n.querySelector('input[name="new_sizes[]"]'))))) refreshRows();
    }).observe(form, { childList: true, subtree: true });
    window.triggerAiAutoFill = async () => {
        const feedback = document.getElementById('aiProductFeedback');
        const { values, warnings } = parseProductNotes(notes.value);
        const hasMeasurements = ['chest', 'hip', 'waist', 'length'].some(k => values[k] !== undefined);
        if (hasMeasurements && !sizeRows().length) {
            if (typeof window.addNewSizeRow === 'function') window.addNewSizeRow();
            refreshRows();
        }
        const row = picker.value !== '' ? rowOptions[Number(picker.value)] : null;
        if (hasMeasurements && !row) {
            warnings.push('Choose a size row and retry to fill measurements and the size label.');
        }
        const file = document.getElementById('aiDressImageInput')?.files[0] || document.getElementById('mainImageInput')?.files[0];
        const whatsappBooked = document.getElementById('aiWhatsAppBooked')?.checked;
        if (!file && !notes.value.trim() && !whatsappBooked) { feedback.textContent = 'Select a dress image, paste price and measurements, or check WhatsApp Booked.'; return; }
        const button = document.getElementById('btnRunAiAssist');
        const original = button.innerHTML;
        button.disabled = true;
        button.textContent = 'Filling product…';
        feedback.textContent = '';
        const statusBadge = document.getElementById('aiStatusBadge');
        if (file && statusBadge) {
            statusBadge.className = 'badge bg-warning text-dark';
            statusBadge.textContent = 'Analyzing image…';
        }
        try {
            if (whatsappBooked) {
                const bookedSwitch = form.querySelector('input[name="is_out_of_stock"]');
                const bookedBy = form.querySelector('input[name="booked_by"]');
                if (bookedSwitch && !bookedSwitch.disabled && bookedBy && !bookedBy.disabled && !bookedBy.readOnly) {
                    bookedSwitch.checked = true;
                    if (typeof window.toggleBookedByContainer === 'function') window.toggleBookedByContainer(bookedSwitch);
                    if (typeof window.toggleBookedByEditContainer === 'function') window.toggleBookedByEditContainer(bookedSwitch);
                    if (bookingTypeSelect) bookingTypeSelect.value = 'business_whatsapp';
                    if (productBookingDate) productBookingDate.value = whatsappBookingDate?.value || '';
                    setValue(bookedBy, '');
                    bookedBy.required = false;
                    syncProductBookingFields();
                } else {
                    warnings.push('This product cannot be booked from this form.');
                }
            }
            if (values.price !== undefined) {
                setValue(document.getElementById('priceInput'), values.price);
                const categories = [...document.querySelectorAll('.category-checkbox')].map(input => ({ input, name: input.closest('label').textContent.trim() }));
                const matches = matchPriceCategories(values.price, categories);
                if (matches.length === 1) {
                    matches[0].input.checked = true;
                    matches[0].input.dispatchEvent(new Event('change', { bubbles: true }));
                } else if (matches.length > 1) warnings.push(`Multiple categories match ${values.price}: ${matches.map(c => c.name).join(', ')}. Select one manually.`);
            }
            if (row) for (const key of ['chest', 'hip', 'waist', 'length']) {
                const fieldName = key === 'chest' ? 'chests' : key === 'hip' ? 'hips' : `${key}s`;
                if (values[key] !== undefined) setValue(field(row, fieldName), values[key]);
            }
            if (file) {
                try {
                    const data = new FormData();
                    data.append('image', file);
                    data.append('_token', form.querySelector('input[name="_token"]').value);
                    data.append('measurement_type', document.querySelector('input[name="measurement_type"]:checked')?.value || 'up');
                    const result = await requestImageAutoFill(button.dataset.url, data);
                    setValue(form.querySelector('input[name="name"]'), result.name.trim());
                    setValue(form.querySelector('textarea[name="description"]'), result.description.trim());
                    const masterSelect = document.getElementById('size_master_id_select');
                    if (result.size_master_id && masterSelect && [...masterSelect.options].some(option => option.value === String(result.size_master_id))) {
                        setValue(masterSelect, String(result.size_master_id));
                    } else {
                        warnings.push('The image could not be matched to an available Size Master. Please select the garment category.');
                    }
                    if (statusBadge) {
                        statusBadge.className = 'badge bg-success';
                        statusBadge.textContent = 'Product name and description filled';
                    }
                } catch (error) {
                    warnings.push(error.message || 'Image analysis failed. Please try again.');
                    if (statusBadge) {
                        statusBadge.className = 'badge bg-danger';
                        statusBadge.textContent = 'Image auto-fill failed';
                    }
                }
            }
            if (hasMeasurements && row && document.querySelector('input[name="measurement_type"]:checked')?.value !== 'down') {
                const label = await window.suggestProductNoteSize(row);
                if (label) setValue(field(row, 'sizes'), label);
                else warnings.push('Size label could not be filled. A Size Master with matching measurement rows is required.');
            } else if (hasMeasurements && row && document.querySelector('input[name="measurement_type"]:checked')?.value === 'down') {
                warnings.push('Measurements filled. Choose or enter the size label for this lower garment.');
            }
            feedback.textContent = [warnings.length ? 'Auto-fill needs attention.' : 'Auto-fill finished. Review the fields before saving.', ...warnings].join(' ');
            feedback.classList.toggle('text-warning', warnings.length > 0);
            feedback.classList.toggle('text-success', warnings.length === 0);
        } catch (error) {
            feedback.textContent = `Please review the filled fields. ${error.message}`;
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    };
}
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
}
