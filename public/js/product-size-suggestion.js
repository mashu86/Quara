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
    if (!matchedOption && (name.includes('tshirt') || name.includes('t-shirt') || name.includes('tee'))) {
        matchedOption = optionsArray.find(opt => hasKeyword(opt, 't-shirt') || hasKeyword(opt, 'tshirt'));
    }

    // Shirt / Shirting
    if (!matchedOption && (name.includes('shirt') || name.includes('shirting'))) {
        matchedOption = optionsArray.find(opt => hasKeyword(opt, 'shirt'));
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

// Match closest numeric chest value
function extractNumber(str) {
    if (str === null || str === undefined) return null;
    const match = String(str).match(/\d+/);
    return match ? parseInt(match[0], 10) : null;
}

export function findBestMatchingMasterRow(rowInputSize, rowChest, rowWaist, masterRows) {
    if (!Array.isArray(masterRows) || masterRows.length === 0) return null;

    // 1. Nearest match by chest measurement
    const chestNum = extractNumber(rowChest);
    if (chestNum !== null) {
        let bestRow = masterRows[0];
        let minDiff = Infinity;
        for (const r of masterRows) {
            if (!r) continue;
            const masterChestNum = extractNumber(r.chest);
            if (masterChestNum !== null) {
                const diff = Math.abs(masterChestNum - chestNum);
                if (diff < minDiff) {
                    minDiff = diff;
                    bestRow = r;
                }
            }
        }
        if (bestRow) return bestRow;
    }

    // 2. Nearest match by waist measurement
    const waistNum = extractNumber(rowWaist);
    if (waistNum !== null) {
        let bestRow = masterRows[0];
        let minDiff = Infinity;
        for (const r of masterRows) {
            if (!r) continue;
            const masterWaistNum = extractNumber(r.waist);
            if (masterWaistNum !== null) {
                const diff = Math.abs(masterWaistNum - waistNum);
                if (diff < minDiff) {
                    minDiff = diff;
                    bestRow = r;
                }
            }
        }
        if (bestRow) return bestRow;
    }

    // 3. Exact match by size label
    const cleanSizeLabel = String(rowInputSize || '').trim().toUpperCase();
    if (cleanSizeLabel) {
        const exactMatch = masterRows.find(r => r && r.size_label && String(r.size_label).trim().toUpperCase() === cleanSizeLabel);
        if (exactMatch) return exactMatch;
    }

    return null;
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

    // Observe dynamically added rows
    const containers = [
        document.getElementById('sizeRowsContainer'),
        document.getElementById('existingSizesContainer'),
        document.getElementById('newSizesContainer')
    ];

    containers.forEach(c => {
        if (c) {
            const observer = new MutationObserver(() => enhanceSizeRows());
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
                    matchedRow = chartData.rows[index] || chartData.rows[0];
                }

                if (matchedRow && matchedRow.size_label && sizeInput) {
                    sizeInput.value = matchedRow.size_label;

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
        if (!row && typeof window.addSizeRow === 'function') {
            window.addSizeRow();
            const updatedRows = document.querySelectorAll('.size-row');
            row = updatedRows[updatedRows.length - 1];
        }

        if (row) {
            const sizeInput = row.querySelector('input[name="sizes[]"], input[name="new_sizes[]"], input[name^="existing_sizes["]');
            const chestInput = row.querySelector('input[name="chests[]"], input[name="new_chests[]"], input[name^="existing_chests["]');
            const waistInput = row.querySelector('input[name="waists[]"], input[name="new_waists[]"], input[name^="existing_waists["]');
            const lengthInput = row.querySelector('input[name="lengths[]"], input[name="new_lengths[]"], input[name^="existing_lengths["]');

            if (sizeInput && mRow.size_label) sizeInput.value = mRow.size_label;
            if (chestInput && mRow.chest) chestInput.value = mRow.chest;
            if (waistInput && mRow.waist) waistInput.value = mRow.waist;
            if (lengthInput && mRow.length) lengthInput.value = mRow.length;

            [sizeInput, chestInput, waistInput, lengthInput].forEach(inp => {
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
