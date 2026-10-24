const DEFAULT_INSTA_SETTINGS = {
    format: 'price_cwl', // 'price_cwl', 'price_cwl_size', 'size_price_only', 'auto_fallback', 'price_all_sizes'
    form: 'short', // 'short', 'full'
    bgColor: '#000000',
    textColor: '#ffffff',
    position: 'top-left', // 'top-left', 'top-right', 'bottom-left', 'bottom-right'
    includeCode: true
};

window.getInstaSettings = function() {
    try {
        const raw = localStorage.getItem('insta_label_settings_v2');
        if (raw) {
            const parsed = JSON.parse(raw);
            return Object.assign({}, DEFAULT_INSTA_SETTINGS, parsed);
        }
        const legacyFormat = localStorage.getItem('insta_overlay_format');
        if (legacyFormat === 'full') {
            DEFAULT_INSTA_SETTINGS.form = 'full';
        }
    } catch(e) {}
    return Object.assign({}, DEFAULT_INSTA_SETTINGS);
};

window.saveInstaSettings = function(settings) {
    localStorage.setItem('insta_label_settings_v2', JSON.stringify(settings));
    if (window.currentPreviewImgSrc) {
        window.updateInstaPreviewBox(window.currentPreviewPrice, window.currentPreviewSizes);
    }
};

window.updateInstaSettingFromDrawer = function(key, val) {
    if (!val && typeof val !== 'boolean') return;
    const settings = window.getInstaSettings();
    settings[key] = val;
    window.saveInstaSettings(settings);
    window.syncInstaSettingsUI(settings);
    
    const notice = document.getElementById('instaSavedNotice');
    if (notice) {
        notice.classList.remove('d-none');
        setTimeout(() => notice.classList.add('d-none'), 1800);
    }
};

window.setPresetColor = function(key, hex) {
    window.updateInstaSettingFromDrawer(key, hex);
};

window.syncInstaSettingsUI = function(settings) {
    const s = settings || window.getInstaSettings();
    
    const formatRadio = document.querySelector(`input[name="insta_drawer_format"][value="${s.format}"]`);
    if (formatRadio) formatRadio.checked = true;

    const formRadio = document.querySelector(`input[name="insta_drawer_form"][value="${s.form}"]`);
    if (formRadio) formRadio.checked = true;

    const posRadio = document.querySelector(`input[name="insta_drawer_position"][value="${s.position}"]`);
    if (posRadio) posRadio.checked = true;

    const codeCheck = document.getElementById('instaIncludeCode');
    if (codeCheck) codeCheck.checked = s.includeCode !== false;

    const bgPicker = document.getElementById('instaBgColorPicker');
    const bgText = document.getElementById('instaBgColorText');
    const bgHex = document.getElementById('instaBgHexVal');
    if (bgPicker) bgPicker.value = s.bgColor;
    if (bgText && document.activeElement !== bgText) bgText.value = s.bgColor;
    if (bgHex) bgHex.textContent = s.bgColor;

    const textPicker = document.getElementById('instaTextColorPicker');
    const textText = document.getElementById('instaTextColorText');
    const textHex = document.getElementById('instaTextHexVal');
    if (textPicker) textPicker.value = s.textColor;
    if (textText && document.activeElement !== textText) textText.value = s.textColor;
    if (textHex) textHex.textContent = s.textColor;
};

// "Custom Size" is stored for a blank size label: it means no size was stated, so it is never printed.
const UNSTATED_SIZE_LABEL = 'custom size';
const statedSizeLabel = (sz) => {
    const label = String(sz.size || '').trim();
    return label.toLowerCase() === UNSTATED_SIZE_LABEL ? '' : label;
};

window.buildInstaTextLines = function(price, sizes, settings) {
    let bVals = [], lVals = [], wVals = [], sizeNames = [];
    if (Array.isArray(sizes)) {
        sizes.forEach(sz => {
            if (sz.chest) bVals.push(sz.chest);
            if (sz.length) lVals.push(sz.length);
            if (sz.waist) wVals.push(sz.waist);
            if (statedSizeLabel(sz)) sizeNames.push(statedSizeLabel(sz));
        });
    }

    const bStr = bVals.length ? [...new Set(bVals)].join(', ') : '';
    const lStr = lVals.length ? [...new Set(lVals)].join(', ') : '';
    const wStr = wVals.length ? [...new Set(wVals)].join(', ') : '';
    const sizeStr = sizeNames.length ? [...new Set(sizeNames)].join(', ') : '';

    const hasChest = bStr !== '';
    const hasLength = lStr !== '';
    const hasWaist = wStr !== '';
    const hasMeasurements = hasChest || hasLength || hasWaist;

    let activeFormat = settings.format || 'price_cwl';
    if (activeFormat === 'auto_fallback') {
        activeFormat = hasMeasurements ? 'price_cwl' : 'size_price_only';
    }

    const isFull = settings.form === 'full';
    const chestLabel = isFull ? 'Chest: ' : 'C: ';
    const waistLabel = isFull ? 'Waist: ' : 'W: ';
    const lengthLabel = isFull ? 'Length: ' : 'L: ';

    const lines = [];

    // Line 1: Price
    lines.push('Price: ' + (price || '--'));

    if (activeFormat === 'price_all_sizes') {
        // One line per size: its label plus only the measurements that are filled in.
        const hipLabel = isFull ? 'Hip: ' : 'H: ';
        (Array.isArray(sizes) ? sizes : []).forEach(sz => {
            const parts = [
                sz.chest ? chestLabel + sz.chest : '',
                sz.waist ? waistLabel + sz.waist : '',
                sz.hip ? hipLabel + sz.hip : '',
                sz.length ? lengthLabel + sz.length : '',
            ].filter(Boolean);
            // Size label on its own line, each filled-in measurement on a line below it.
            const label = statedSizeLabel(sz);
            if (label) lines.push('Size: ' + label);
            lines.push(...parts);
        });
    } else if (activeFormat === 'size_price_only') {
        if (sizeStr) lines.push('Size: ' + sizeStr);
    } else if (activeFormat === 'price_cwl_size') {
        lines.push(chestLabel + (bStr || '-'));
        lines.push(waistLabel + (wStr || '-'));
        lines.push(lengthLabel + (lStr || '-'));
        if (sizeStr) lines.push('Size: ' + sizeStr);
    } else { // price_cwl
        lines.push(chestLabel + (bStr || '-'));
        lines.push(waistLabel + (wStr || '-'));
        lines.push(lengthLabel + (lStr || '-'));
    }

    // Product code sits with the size details (only when the product has one).
    if (settings.includeCode !== false && window.currentPreviewCode) lines.push('Code: ' + window.currentPreviewCode);

    return lines;
};

/** With several sizes the label shows one size at a time; the modal arrows pick which. */
window.labelSizes = function(sizes) {
    const all = Array.isArray(sizes) ? sizes : [];
    if (all.length <= 1) return all;
    const index = ((window.currentPreviewSizeIndex || 0) % all.length + all.length) % all.length;
    return [all[index]];
};

window.stepPreviewSize = function(delta) {
    window.currentPreviewSizeIndex = (window.currentPreviewSizeIndex || 0) + delta;
    window.updateInstaPreviewBox(window.currentPreviewPrice, window.currentPreviewSizes);
};

window.updateInstaPreviewBox = function(price, sizes) {
    const settings = window.getInstaSettings();
    const lines = window.buildInstaTextLines(price, window.labelSizes(sizes), settings);

    const boxElem = document.getElementById('productPreviewInstaBox');
    if (!boxElem) return;
    // The wrapper carries the size arrows beside the label; position it as one unit.
    const posElem = document.getElementById('productPreviewInstaWrap') || boxElem;
    const hasManySizes = Array.isArray(sizes) && sizes.length > 1;
    ['productPreviewSizePrev', 'productPreviewSizeNext'].forEach(id => {
        document.getElementById(id)?.classList.toggle('d-none', !hasManySizes);
    });

    posElem.style.top = 'auto';
    posElem.style.bottom = 'auto';
    posElem.style.left = 'auto';
    posElem.style.right = 'auto';
    posElem.style.margin = '14px';

    if (settings.position === 'top-right') {
        posElem.style.top = '0';
        posElem.style.right = '0';
    } else if (settings.position === 'bottom-left') {
        posElem.style.bottom = '0';
        posElem.style.left = '0';
    } else if (settings.position === 'bottom-right') {
        posElem.style.bottom = '0';
        posElem.style.right = '0';
    } else { // top-left
        posElem.style.top = '0';
        posElem.style.left = '0';
    }

    boxElem.style.backgroundColor = settings.bgColor || '#000000';
    boxElem.style.color = settings.textColor || '#ffffff';

    boxElem.replaceChildren();
    lines.forEach((line) => {
        const isPriceLine = line.toLowerCase().startsWith('price');
        const lineStyle = isPriceLine ? 'font-weight: 800; font-size: 0.64rem; margin-bottom: 1px;' : 'font-weight: 600; opacity: 0.95;';
        const lineElem = document.createElement('div');
        lineElem.style.cssText = lineStyle;
        lineElem.textContent = line;
        boxElem.appendChild(lineElem);
    });

    boxElem.classList.remove('d-none');
};

window.openProductPreview = function(imgSrc, title, price, sizes, code, startSizeIndex) {
    if (!imgSrc) return;
    window.currentPreviewImgSrc = imgSrc;
    window.currentPreviewTitle = title || 'product';
    window.currentPreviewPrice = price || '';
    window.currentPreviewSizes = sizes || [];
    window.currentPreviewCode = code || '';
    window.currentPreviewSizeIndex = startSizeIndex || 0;

    const modalElem = document.getElementById('productPreviewModal');
    const imgElem = document.getElementById('productPreviewModalImg');
    const titleElem = document.getElementById('productPreviewModalLabel');
    const downloadBtn = document.getElementById('productPreviewModalDownloadBtn');
    if (imgElem) {
        imgElem.src = imgSrc;
        imgElem.alt = title || 'Product Image';
    }
    if (titleElem) titleElem.textContent = title || 'Product Image';
    if (downloadBtn) {
        downloadBtn.href = imgSrc;
        const cleanFileName = (title ? title.toLowerCase().replace(/[^a-z0-9]+/g, '-') : 'product-main-image') + '.jpg';
        downloadBtn.setAttribute('download', cleanFileName);
    }

    window.updateInstaPreviewBox(price, sizes);

    if (modalElem) {
        modalElem.scrollTop = 0;
        let modalInstance = bootstrap.Modal.getInstance(modalElem);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(modalElem);
        }
        modalInstance.show();
    }
};

window.downloadInstagramImage = function() {
    const imgSrc = window.currentPreviewImgSrc;
    const price = window.currentPreviewPrice || '';
    // Download exactly the label on screen: the size currently picked with the arrows.
    const sizes = window.labelSizes(window.currentPreviewSizes || []);
    const title = window.currentPreviewTitle || 'product';
    const settings = window.getInstaSettings();

    if (!imgSrc) {
        alert('No image available to download.');
        return;
    }

    const lines = window.buildInstaTextLines(price, sizes, settings);

    const btn = document.getElementById('productPreviewModalInstaBtn');
    const originalBtnHtml = btn ? btn.innerHTML : '';
    if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function() {
        try {
            const canvas = document.createElement('canvas');
            const w = img.naturalWidth || img.width || 800;
            const h = img.naturalHeight || img.height || 1000;
            canvas.width = w;
            canvas.height = h;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, w, h);

            const scale = Math.max(w, h) / 800;
            const fontSize = Math.max(10, Math.round(14 * scale));
            const lineHeight = Math.round(fontSize * 1.35);
            const paddingX = Math.max(14, Math.round(22 * scale));
            const paddingY = Math.max(12, Math.round(18 * scale));
            const margin = Math.max(18, Math.round(26 * scale));
            const borderRadius = Math.max(6, Math.round(10 * scale));

            ctx.font = 'bold ' + fontSize + 'px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

            let maxLineWidth = 0;
            lines.forEach(line => {
                const lw = ctx.measureText(line).width;
                if (lw > maxLineWidth) maxLineWidth = lw;
            });

            const boxWidth = maxLineWidth + (paddingX * 2);
            const boxHeight = (lines.length * lineHeight) - (lineHeight - fontSize) + (paddingY * 2);
            
            let x = margin;
            let y = margin;
            if (settings.position === 'top-right') {
                x = w - boxWidth - margin;
                y = margin;
            } else if (settings.position === 'bottom-left') {
                x = margin;
                y = h - boxHeight - margin;
            } else if (settings.position === 'bottom-right') {
                x = w - boxWidth - margin;
                y = h - boxHeight - margin;
            } else { // top-left
                x = margin;
                y = margin;
            }

            // Draw Background Box
            ctx.fillStyle = settings.bgColor || '#000000';
            ctx.beginPath();
            if (ctx.roundRect) {
                ctx.roundRect(x, y, boxWidth, boxHeight, borderRadius);
            } else {
                ctx.rect(x, y, boxWidth, boxHeight);
            }
            ctx.fill();

            // Draw Text Lines
            ctx.fillStyle = settings.textColor || '#ffffff';
            ctx.textBaseline = 'top';
            lines.forEach((line, index) => {
                ctx.fillText(line, x + paddingX, y + paddingY + (index * lineHeight));
            });

            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
            const a = document.createElement('a');
            a.href = dataUrl;
            const sizePart = (window.currentPreviewSizes || []).length > 1 && sizes[0] ? statedSizeLabel(sizes[0]) : '';
            const cleanName = [title || 'product', sizePart, 'instagram'].filter(Boolean).join(' ')
                .toLowerCase().replace(/[^a-z0-9]+/g, '-') + '.jpg';
            a.download = cleanName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        } catch (err) {
            console.error('Canvas export error:', err);
            alert('Could not generate Instagram download image.');
        } finally {
            if (btn) btn.innerHTML = originalBtnHtml;
        }
    };
    img.onerror = function() {
        if (btn) btn.innerHTML = originalBtnHtml;
        alert('Failed to load image for Instagram download.');
    };
    img.src = imgSrc;
};
