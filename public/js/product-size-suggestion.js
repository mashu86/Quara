/**
 * Product Size Suggestion & Size Master Auto-Populator Logic
 */

// Auto-detect Size Master category from Product Name input (Ignoring product categories)
export function autoDetectSizeMasterByName(productName, masterOptions) {
    if (!productName || !masterOptions || masterOptions.length === 0) return null;
    const name = productName.toLowerCase().trim();

    // 1. Direct name match in option label
    let matchedOption = Array.from(masterOptions).find(opt => {
        const optName = (opt.dataset.name || opt.textContent || '').toLowerCase().trim();
        return optName && (name.includes(optName) || optName.includes(name));
    });

    if (matchedOption) return matchedOption.value;

    // 2. Matching patterns in priority order:
    // Crop top
    if (name.includes('crop top') || name.includes('croptop') || name.includes('crop')) {
        matchedOption = Array.from(masterOptions).find(opt => opt.dataset.name && opt.dataset.name.includes('crop'));
    }
    
    // Overcoat / Long coat / Jacket
    if (!matchedOption && (name.includes('overcoat') || name.includes('long coat') || name.includes('coat') || name.includes('jacket'))) {
        matchedOption = Array.from(masterOptions).find(opt => opt.dataset.name && (opt.dataset.name.includes('overcoat') || opt.dataset.name.includes('coat')));
    }

    // T-Shirt / Tee
    if (!matchedOption && (name.includes('tshirt') || name.includes('t-shirt') || name.includes('tee'))) {
        matchedOption = Array.from(masterOptions).find(opt => opt.dataset.name && (opt.dataset.name.includes('t-shirt') || opt.dataset.name.includes('tshirt')));
    }

    // Shirt / Shirting
    if (!matchedOption && (name.includes('shirt') || name.includes('shirting'))) {
        matchedOption = Array.from(masterOptions).find(opt => opt.dataset.name && opt.dataset.name.includes('shirt'));
    }

    // Top / Blouse / Tunic
    if (!matchedOption && (name.includes('top') || name.includes('blouse') || name.includes('tunic'))) {
        matchedOption = Array.from(masterOptions).find(opt => opt.dataset.name && opt.dataset.name.includes('top') && !opt.dataset.name.includes('crop'));
    }

    return matchedOption ? matchedOption.value : null;
}

window.autoDetectAndSelectSizeMaster = function(productName) {
    const masterSelect = document.getElementById('size_master_id_select');
    if (!masterSelect || !productName) return;

    const masterOptions = masterSelect.querySelectorAll('option[value]:not([value=""])');
    const detectedId = autoDetectSizeMasterByName(productName, masterOptions);
    if (detectedId) {
        masterSelect.value = detectedId;
        masterSelect.dispatchEvent(new Event('change', { bubbles: true }));

        masterSelect.style.transition = 'background 0.3s ease';
        masterSelect.style.backgroundColor = '#fffbe6';
        setTimeout(() => masterSelect.style.backgroundColor = '', 2500);
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
    if (!str) return null;
    const match = String(str).match(/\d+/);
    return match ? parseInt(match[0], 10) : null;
}

export function findBestMatchingMasterRow(rowInputSize, rowChest, rowWaist, masterRows) {
    if (!masterRows || masterRows.length === 0) return null;

    // 1. Nearest match by chest measurement
    const chestNum = extractNumber(rowChest);
    if (chestNum) {
        let bestRow = masterRows[0];
        let minDiff = Infinity;
        for (const r of masterRows) {
            const masterChestNum = extractNumber(r.chest);
            if (masterChestNum) {
                const diff = Math.abs(masterChestNum - chestNum);
                if (diff < minDiff) {
                    minDiff = diff;
                    bestRow = r;
                }
            }
        }
        return bestRow;
    }

    // 2. Nearest match by waist measurement
    const waistNum = extractNumber(rowWaist);
    if (waistNum) {
        let bestRow = masterRows[0];
        let minDiff = Infinity;
        for (const r of masterRows) {
            const masterWaistNum = extractNumber(r.waist);
            if (masterWaistNum) {
                const diff = Math.abs(masterWaistNum - waistNum);
                if (diff < minDiff) {
                    minDiff = diff;
                    bestRow = r;
                }
            }
        }
        return bestRow;
    }

    // 3. Exact match by size label
    const cleanSizeLabel = (rowInputSize || '').trim().toUpperCase();
    if (cleanSizeLabel) {
        const exactMatch = masterRows.find(r => r.size_label.trim().toUpperCase() === cleanSizeLabel);
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
            const detectedId = autoDetectSizeMasterByName(nameInput.value, masterOptions);
            if (detectedId && !masterSelect.dataset.userManuallySelected) {
                masterSelect.value = detectedId;
                masterSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        masterSelect.addEventListener('change', () => {
            if (document.activeElement === masterSelect) {
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

    rows.forEach((row, index) => {
        const sizeInput = row.querySelector('input[name="sizes[]"], input[name="new_sizes[]"], input[name^="existing_sizes["]');
        if (!sizeInput) return;

        let inputGroup = sizeInput.closest('.input-group');
        if (!inputGroup) {
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
                const masterId = masterSelect ? masterSelect.value : null;
                if (!masterId) {
                    alert('Please select a Product Size Master Category first.');
                    if (masterSelect) masterSelect.focus();
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                const chartData = await fetchSizeMasterChart(masterId);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-dark"></i>';

                if (!chartData || !chartData.rows || chartData.rows.length === 0) {
                    alert('No measurement rows found for this category master.');
                    return;
                }

                const chestInput = row.querySelector('input[name="chests[]"], input[name="new_chests[]"], input[name^="existing_chests["]');
                const waistInput = row.querySelector('input[name="waists[]"], input[name="new_waists[]"], input[name^="existing_waists["]');

                let matchedRow = findBestMatchingMasterRow(
                    sizeInput.value,
                    chestInput ? chestInput.value : '',
                    waistInput ? waistInput.value : '',
                    chartData.rows
                );

                if (!matchedRow) {
                    matchedRow = chartData.rows[index] || chartData.rows[0];
                }

                if (matchedRow) {
                    // Populate ONLY Size Label based on measurements. Chest/Waist/Length remain untouched.
                    sizeInput.value = matchedRow.size_label;

                    sizeInput.style.transition = 'background 0.3s ease';
                    sizeInput.style.backgroundColor = '#fffbe6';
                    setTimeout(() => sizeInput.style.backgroundColor = '', 2000);
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
    if (!chartData || !chartData.rows || chartData.rows.length === 0) {
        alert('No size master rows found for the selected category.');
        return;
    }

    const rows = document.querySelectorAll('.size-row');
    if (rows.length === 0) return;

    chartData.rows.forEach((mRow, index) => {
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

            if (sizeInput) sizeInput.value = mRow.size_label;
            if (chestInput && mRow.chest) chestInput.value = mRow.chest;
            if (waistInput && mRow.waist) waistInput.value = mRow.waist;
            if (lengthInput && mRow.length) lengthInput.value = mRow.length;

            [sizeInput, chestInput, waistInput, lengthInput].forEach(inp => {
                if (inp) {
                    inp.style.transition = 'background 0.3s ease';
                    inp.style.backgroundColor = '#e6f7ff';
                    setTimeout(() => inp.style.backgroundColor = '', 2000);
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
