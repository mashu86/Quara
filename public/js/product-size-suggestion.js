/**
 * Product Size Suggestion & Size Master Auto-Populator Logic
 */

// Auto-detect Size Master category from Product Name input
export function autoDetectSizeMasterByName(productName, masterOptions) {
    if (!productName || typeof productName !== 'string' || !masterOptions || masterOptions.length === 0) return null;
    const name = productName.toLowerCase().trim();
    const optionsArray = Array.from(masterOptions);

    const getOptName = (opt) => (opt?.dataset?.name || opt?.textContent || '').toLowerCase().trim();
    const hasKeyword = (opt, keyword) => getOptName(opt).includes(keyword);

    // 1. Direct name match in option label
    let matchedOption = optionsArray.find(opt => {
        const optName = getOptName(opt);
        return optName && (name.includes(optName) || optName.includes(name));
    });

    if (matchedOption) return matchedOption.value;

    // 2. Matching patterns in priority order:
    // Crop top
    if (name.includes('crop top') || name.includes('croptop') || name.includes('crop')) {
        matchedOption = optionsArray.find(opt => hasKeyword(opt, 'crop'));
    }
    
    // Overcoat / Long coat / Jacket
    if (!matchedOption && (name.includes('overcoat') || name.includes('long coat') || name.includes('coat') || name.includes('jacket'))) {
        matchedOption = optionsArray.find(opt => hasKeyword(opt, 'overcoat') || hasKeyword(opt, 'coat'));
    }

    // T-Shirt / Tee
    if (!matchedOption && (name.includes('tshirt') || name.includes('t-shirt') || name.includes('t shirt') || name.includes('tee'))) {
        matchedOption = optionsArray.find(opt => hasKeyword(opt, 't-shirt') || hasKeyword(opt, 'tshirt'));
    }

    // Sweater / Jumper
    if (!matchedOption && /sweater|sweter|jumper|cardigan/.test(name)) {
        matchedOption = optionsArray.find(opt => hasKeyword(opt, 'sweater') || hasKeyword(opt, 'sweter'));
    }

    // Shirt / Shirting
    if (!matchedOption && (name.includes('shirt') || name.includes('shirting'))) {
        matchedOption = optionsArray.find(opt => hasKeyword(opt, 'shirt') && !/t-shirt|tshirt/.test(getOptName(opt)));
    }

    // Top / Blouse / Tunic
    if (!matchedOption && (name.includes('top') || name.includes('blouse') || name.includes('tunic'))) {
        matchedOption = optionsArray.find(opt => hasKeyword(opt, 'top') && !hasKeyword(opt, 'crop'));
    }

    return matchedOption ? matchedOption.value : null;
}

window.autoDetectAndSelectSizeMaster = function(productName) {
    if (!productName) return;
    const masterSelect = document.getElementById('size_master_id_select');
    if (!masterSelect) return;

    const masterOptions = masterSelect.querySelectorAll('option[value]:not([value=""])');
    if (!masterOptions || masterOptions.length === 0) return;

    const detectedId = autoDetectSizeMasterByName(productName, masterOptions);
    if (detectedId) {
        masterSelect.value = detectedId;
        masterSelect.dispatchEvent(new Event('change', { bubbles: true }));

        masterSelect.style.transition = 'background 0.3s ease';
        masterSelect.style.backgroundColor = '#fffbe6';
        setTimeout(() => {
            if (masterSelect) masterSelect.style.backgroundColor = '';
        }, 2500);
    }
};

// Fetch Size Master chart rows by category ID
export async function fetchSizeMasterChart(masterId) {
    if (!masterId) return null;
    try {
        const response = await fetch(`/admin/size-masters/chart/${masterId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        if (!response.ok) return null;
        return await response.json();
    } catch (err) {
        console.error('Error fetching size master chart:', err);
        return null;
    }
}

// Measurements are full circumferences in inches; ranges are inclusive.
function measurementRange(value) {
    const match = String(value ?? '').trim().match(/^(\d+(?:\.\d+)?)\s*(?:[-\u2013\u2014]\s*(\d+(?:\.\d+)?))?\s*(?:"|\u2033|in|inches)?$/i);
    if (!match) return null;
    const low = Number(match[1]);
    const high = Number(match[2] ?? match[1]);
    return low > 0 && high >= low ? [low, high] : null;
}

export function findBestMatchingMasterRow(rowInputSize, rowChest, rowWaist, masterRows) {
    if (!Array.isArray(masterRows)) return null;
    const inputs = { chest: measurementRange(rowChest), waist: measurementRange(rowWaist) };
    const fields = ['chest', 'waist'].filter(field => String(field === 'chest' ? rowChest ?? '' : rowWaist ?? '').trim());
    if (fields.some(field => !inputs[field])) return null;

    if (fields.length) {
        const candidates = masterRows.flatMap(row => {
            if (!row) return [];
            let score = 0;
            for (const field of fields) {
                const range = measurementRange(row[field]);
                if (!range) return [];
                const [low, high] = inputs[field];
                const distance = Math.max(range[0] - low, high - range[1], 0);
                // Allow small measuring/rounding differences, never force an unrelated size.
                if (distance > 0.5) return [];
                score += distance;
            }
            return [{ row, score }];
        }).sort((a, b) => a.score - b.score);
        // Overlapping or identical measurements cannot uniquely identify a size.
        if (!candidates.length || (candidates[1] && candidates[0].score === candidates[1].score)) return null;
        return candidates[0].row;
    }

    const label = String(rowInputSize || '').trim().toUpperCase();
    return label ? masterRows.find(row => String(row?.size_label || '').trim().toUpperCase() === label) || null : null;
}

// Reuse the shop's measurement chart without the manual button's row fallback.
window.suggestProductNoteSize = async function(row) {
    const select = document.getElementById('size_master_id_select');
    const masterId = select?.value;
    if (!masterId) return null;
    const embeddedChart = select.selectedOptions[0]?.dataset.chart;
    const chart = embeddedChart ? { rows: JSON.parse(embeddedChart) } : await fetchSizeMasterChart(masterId);
    const read = name => row.querySelector(`input[name="${name}[]"], input[name="new_${name}[]"], input[name^="existing_${name}["]`)?.value || '';
    return findBestMatchingMasterRow('', read('chests'), read('waists'), chart?.rows)?.size_label || null;
};

function initializeSizeChartHints() {
    if (!window.bootstrap?.Popover) return;

    document.querySelectorAll('[data-size-chart-hint]').forEach(button => {
        if (window.bootstrap.Popover.getInstance(button)) return;
        const popover = new window.bootstrap.Popover(button, {
            container: 'body',
            placement: 'auto',
            trigger: 'hover focus',
            html: true,
            title: 'Size chart (inches)',
            content: () => {
                const content = document.createElement('div');
                const select = document.getElementById('size_master_id_select');
                const option = select?.selectedOptions[0];
                if (!select?.value) {
                    content.textContent = 'Select a Product Size Master Category to view its chart.';
                    return content;
                }

                let rows = [];
                try { rows = JSON.parse(option?.dataset.chart || '[]'); } catch { /* Show empty state. */ }
                const heading = document.createElement('div');
                heading.className = 'fw-bold mb-2';
                heading.textContent = option.textContent.trim();
                content.append(heading);
                if (!Array.isArray(rows) || !rows.length) {
                    content.append('No measurements available for this category.');
                    return content;
                }

                const table = document.createElement('table');
                table.className = 'table table-sm table-bordered text-center align-middle mb-2';
                const header = table.createTHead().insertRow();
                ['Size', 'Chest / Bust', 'Waist'].forEach(label => {
                    const cell = document.createElement('th');
                    cell.scope = 'col';
                    cell.textContent = label;
                    header.append(cell);
                });
                const body = table.createTBody();
                rows.forEach(row => {
                    const tr = body.insertRow();
                    [row.size_label, row.chest, row.waist].forEach(value => {
                        tr.insertCell().textContent = value || '-';
                    });
                });
                const scroll = document.createElement('div');
                scroll.style.maxHeight = '320px';
                scroll.style.overflowY = 'auto';
                scroll.append(table);
                content.append(scroll);
                const note = document.createElement('div');
                note.className = 'small text-muted';
                note.textContent = 'Body measurements, not garment dimensions. Compare both measurements and enter a size label manually if needed.';
                content.append(note);
                return content;
            },
        });
        button.addEventListener('click', () => {
            button.focus();
            popover.show();
        });
        button.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                popover.hide();
                button.blur();
            }
        });
    });
}

function initializeSizeMasterScript() {
    const nameInput = document.querySelector('input[name="name"]');
    const masterSelect = document.getElementById('size_master_id_select');

    if (nameInput && masterSelect) {
        const masterOptions = masterSelect.querySelectorAll('option[value]:not([value=""])');

        // Auto-detect on input
        nameInput.addEventListener('input', () => {
            if (!nameInput || !masterSelect) return;
            const detectedId = autoDetectSizeMasterByName(nameInput.value, masterOptions);
            if (detectedId && !masterSelect.dataset.userManuallySelected) {
                masterSelect.value = detectedId;
                masterSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        masterSelect.addEventListener('change', () => {
            if (masterSelect && document.activeElement === masterSelect) {
                masterSelect.dataset.userManuallySelected = '1';
            }
        });
    }

    enhanceSizeRows();
    initializeSizeChartHints();
    masterSelect?.addEventListener('change', () => {
        document.querySelectorAll('[data-size-chart-hint]').forEach(button => {
            window.bootstrap?.Popover.getInstance(button)?.hide();
        });
    });

    // Observe dynamically added rows
    const containers = [
        document.getElementById('sizeRowsContainer'),
        document.getElementById('existingSizesContainer'),
        document.getElementById('newSizesContainer')
    ];

    containers.forEach(c => {
        if (c) {
            const observer = new MutationObserver(() => {
                enhanceSizeRows();
                initializeSizeChartHints();
            });
            observer.observe(c, { childList: true, subtree: true });
        }
    });
}

export function enhanceSizeRows() {
    const masterSelect = document.getElementById('size_master_id_select');
    const rows = document.querySelectorAll('.size-row, #sizeRowsContainer > div, #newSizesContainer > div');
    if (!rows || rows.length === 0) return;

    rows.forEach((row, index) => {
        if (!row) return;
        const sizeInput = row.querySelector('input[name="sizes[]"], input[name="new_sizes[]"], input[name^="existing_sizes["]');
        if (!sizeInput) return;

        if (!sizeInput.dataset.suggestionBound) {
            sizeInput.dataset.suggestionBound = '1';
            let revision = 0;
            const refreshSuggestion = async () => {
                const requestRevision = ++revision;
                const previous = sizeInput.value;
                if (previous && previous !== sizeInput.dataset.suggestedSize) return;
                const label = await window.suggestProductNoteSize(row);
                if (revision !== requestRevision || sizeInput.value !== previous) return;
                sizeInput.value = label || '';
                sizeInput.dataset.suggestedSize = label || '';
            };
            ['chests', 'waists'].forEach(field => {
                row.querySelector(`input[name="${field}[]"], input[name="new_${field}[]"], input[name^="existing_${field}["]`)
                    ?.addEventListener('input', refreshSuggestion);
            });
            sizeInput.addEventListener('input', () => {
                revision++;
                delete sizeInput.dataset.suggestedSize;
            });
            masterSelect?.addEventListener('change', refreshSuggestion);
        }

        let inputGroup = sizeInput.closest('.input-group');
        if (!inputGroup) {
            if (!sizeInput.parentNode) return;
            inputGroup = document.createElement('div');
            inputGroup.className = 'input-group input-group-sm flex-nowrap';
            sizeInput.parentNode.insertBefore(inputGroup, sizeInput);
            inputGroup.appendChild(sizeInput);
            sizeInput.classList.remove('form-control-sm');
            sizeInput.classList.add('form-control-sm');
        }

        if (!inputGroup.querySelector('.magic-row-btn')) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-warning magic-row-btn px-2 shadow-sm';
            btn.title = 'Auto-detect Size Label from entered measurements';
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-dark"></i>';

            btn.addEventListener('click', async () => {
                const currentMasterSelect = document.getElementById('size_master_id_select');
                const masterId = currentMasterSelect ? currentMasterSelect.value : null;
                if (!masterId) {
                    alert('Please select a Product Size Master Category first.');
                    if (currentMasterSelect) currentMasterSelect.focus();
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                const chartData = await fetchSizeMasterChart(masterId);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-dark"></i>';

                if (!chartData || !Array.isArray(chartData.rows) || chartData.rows.length === 0) {
                    alert('No measurement rows found for this category master.');
                    return;
                }

                const chestInput = row.querySelector('input[name="chests[]"], input[name="new_chests[]"], input[name^="existing_chests["]');
                const waistInput = row.querySelector('input[name="waists[]"], input[name="new_waists[]"], input[name^="existing_waists["]');

                let matchedRow = findBestMatchingMasterRow(
                    sizeInput ? sizeInput.value : '',
                    chestInput ? chestInput.value : '',
                    waistInput ? waistInput.value : '',
                    chartData.rows
                );

                if (!matchedRow) {
                    alert('No unique size matches the entered chest and waist. Check the measurements or enter a size label manually.');
                    return;
                }

                if (matchedRow && matchedRow.size_label && sizeInput) {
                    sizeInput.value = matchedRow.size_label;
                    sizeInput.dataset.suggestedSize = matchedRow.size_label;

                    sizeInput.style.transition = 'background 0.3s ease';
                    sizeInput.style.backgroundColor = '#fffbe6';
                    setTimeout(() => {
                        if (sizeInput) sizeInput.style.backgroundColor = '';
                    }, 2000);
                }
            });

            inputGroup.append(btn);
        }
    });
}

// Global button to fill all rows from Size Master
window.applyMagicSizeMasterToAllRows = async function() {
    const masterSelect = document.getElementById('size_master_id_select');
    const masterId = masterSelect ? masterSelect.value : null;

    if (!masterId) {
        alert('Please select a Product Size Master Category first.');
        if (masterSelect) masterSelect.focus();
        return;
    }

    const chartData = await fetchSizeMasterChart(masterId);
    if (!chartData || !Array.isArray(chartData.rows) || chartData.rows.length === 0) {
        alert('No size master rows found for the selected category.');
        return;
    }

    const rows = document.querySelectorAll('.size-row');
    if (!rows || rows.length === 0) return;

    chartData.rows.forEach((mRow, index) => {
        if (!mRow) return;
        let row = rows[index];

        // If not enough rows, click add size row if function exists
        if (!row && (typeof window.addSizeRow === 'function' || typeof window.addNewSizeRow === 'function')) {
            (window.addSizeRow || window.addNewSizeRow)();
            const updatedRows = document.querySelectorAll('.size-row');
            row = updatedRows[updatedRows.length - 1];
        }

        if (row) {
            const sizeInput = row.querySelector('input[name="sizes[]"], input[name="new_sizes[]"], input[name^="existing_sizes["]');
            const chestInput = row.querySelector('input[name="chests[]"], input[name="new_chests[]"], input[name^="existing_chests["]');
            const waistInput = row.querySelector('input[name="waists[]"], input[name="new_waists[]"], input[name^="existing_waists["]');

            if (sizeInput && mRow.size_label) sizeInput.value = mRow.size_label;
            if (chestInput && mRow.chest) chestInput.value = mRow.chest;
            if (waistInput && mRow.waist) waistInput.value = mRow.waist;

            [sizeInput, chestInput, waistInput].forEach(inp => {
                if (inp) {
                    inp.style.transition = 'background 0.3s ease';
                    inp.style.backgroundColor = '#e6f7ff';
                    setTimeout(() => {
                        if (inp) inp.style.backgroundColor = '';
                    }, 2000);
                }
            });
        }
    });
};

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSizeMasterScript);
    } else {
        initializeSizeMasterScript();
    }
}
