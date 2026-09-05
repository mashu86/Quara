<!-- COURIER ADDRESS PREVIEW MODAL -->
<div class="modal fade" id="courierAddressPreviewModal" tabindex="-1" aria-labelledby="courierAddressPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-2.5">
                <h5 class="modal-title font-serif fw-bold fs-6" id="courierAddressPreviewModalLabel">
                    <i class="fa-solid fa-truck-fast text-warning me-2"></i> Courier Address Label (10.5 &times; 14.5 cm)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white" id="courierAddressPreviewModalBody" style="overflow-x: auto;">
                <!-- Uses the same fixed-size sheet as printing. -->
            </div>
            <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 px-sm-4 py-2 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 btn-sm" style="font-size: 0.78rem;" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning rounded-pill px-3 px-sm-4 py-1.5 fw-bold text-dark shadow-sm btn-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold); font-size: 0.78rem;" id="modalPrintCourierBtn">
                    <i class="fa-solid fa-print me-1"></i>
                    <span class="d-none d-sm-inline">Print / Download Label</span>
                    <span class="d-inline d-sm-none">Print</span>
                </button>
            </div>
        </div>
    </div>
</div>


<div id="courier-parcel-print-area" aria-hidden="true">
    <div class="courier-parcel-sheet">
        <div class="parcel-to-section">
            <div class="parcel-label">To ,</div>
            <div class="parcel-to-space parcel-indent-box">
                <div class="parcel-to-address"></div>
            </div>
        </div>
        <div class="parcel-from-container">
            <div class="parcel-from-wrapper">
                <div class="parcel-label">From ,</div>
                <div class="parcel-indent-box">
                    akarsha Bakker<br>
                    TK HOUSE VILAKKANNUR<br>
                    NADUVIL PO:670582<br>
                    PHN:8078037591<br>
                    Kannur, Kerala
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #courier-parcel-print-area {
        position: fixed;
        top: 0;
        left: 0;
        visibility: hidden;
        pointer-events: none;
        z-index: -1;
    }

    .courier-parcel-sheet {
        width: 105mm;
        min-width: 105mm;
        max-width: 105mm;
        height: 145mm;
        min-height: 145mm;
        max-height: 145mm;
        box-sizing: border-box;
        padding: 8mm 8mm 6mm;
        overflow: hidden;
        background: #fff;
        color: #000;
        font: 12.5pt/1.25 Arial, "Segoe UI", Helvetica, sans-serif;
        display: flex;
        flex-direction: column;
        gap: 5mm;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .courier-parcel-sheet * { box-sizing: border-box; }
    .courier-parcel-sheet .parcel-to-section {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
    }
    .courier-parcel-sheet .parcel-label {
        flex-shrink: 0;
        font-size: 15pt;
        font-weight: 700;
        margin-bottom: 2mm;
    }
    .courier-parcel-sheet .parcel-indent-box {
        margin-left: 5mm;
        font-weight: 400;
        overflow-wrap: anywhere;
    }
    .courier-parcel-sheet .parcel-to-space {
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }
    .courier-parcel-sheet .parcel-from-container {
        display: flex;
        justify-content: flex-end;
        flex-shrink: 0;
    }
    .courier-parcel-sheet .parcel-from-wrapper { width: 75mm; }
    #courierAddressPreviewModalBody .courier-parcel-sheet {
        margin: 0 auto;
        outline: 1px dashed #ccc;
    }

    @media print {
        @page { size: auto; margin: 0; }
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: auto !important;
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
            background: #fff !important;
        }
        body > :not(#courier-parcel-print-area) { display: none !important; }
        #courier-parcel-print-area {
            display: block !important;
            position: static !important;
            visibility: visible !important;
            width: 105mm !important;
            height: 145mm !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
    }
</style>

<script>
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatOrderToAddressHtml(order) {
    if (!order) return '';
    let lines = [];

    // Line 1: Name
    if (order.customer_name) {
        lines.push(escapeHtml(order.customer_name));
    }

    // Line 2: House/Building Name/No - Street/Road
    let house = (order.house_building || '').toString().trim();
    let street = (order.street || '').toString().trim();
    if (house && street) {
        lines.push(escapeHtml(house) + ' - ' + escapeHtml(street));
    } else if (house) {
        lines.push(escapeHtml(house));
    } else if (street) {
        lines.push(escapeHtml(street));
    }

    // Line 3: Area / Landmark, City/Town
    let area = (order.area || '').toString().trim();
    let city = (order.city || '').toString().trim();
    if (area && city) {
        lines.push(escapeHtml(area) + ', ' + escapeHtml(city));
    } else if (area) {
        lines.push(escapeHtml(area));
    } else if (city) {
        lines.push(escapeHtml(city));
    }

    // Line 4: District, State, Pincode
    let district = (order.district || '').toString().trim();
    let state = (order.state || '').toString().trim();
    let pin = (order.pin_code || order.pincode || '').toString().trim();
    
    let line4Parts = [];
    if (district) line4Parts.push(district);
    if (state) line4Parts.push(state);
    if (pin) line4Parts.push(pin);
    if (line4Parts.length > 0) {
        lines.push(escapeHtml(line4Parts.join(', ')));
    }

    // Line 5: Phone Number
    if (order.customer_phone) {
        lines.push('PHN:' + escapeHtml(order.customer_phone));
    }

    return lines.join('<br>');
}

// Keep the print sheet outside the admin layout so hidden UI cannot add pages.
const courierPrintArea = document.getElementById('courier-parcel-print-area');
document.body.appendChild(courierPrintArea);

function fitCourierAddress(sheet) {
    const address = sheet.querySelector('.parcel-to-address');
    const space = sheet.querySelector('.parcel-to-space');
    address.style.fontSize = '12.5pt';
    if (!space.clientHeight || address.scrollHeight <= space.clientHeight) return;

    // Reduce only overflowing recipient text; retain every address line.
    let low = 0;
    let high = 12.5;
    for (let step = 0; step < 16; step++) {
        const size = (low + high) / 2;
        address.style.fontSize = size + 'pt';
        if (address.scrollHeight <= space.clientHeight && address.scrollWidth <= space.clientWidth) {
            low = size;
        } else {
            high = size;
        }
    }
    address.style.fontSize = low + 'pt';
}

function setCourierAddress(order) {
    courierPrintArea.querySelector('.parcel-to-address').innerHTML = order ? formatOrderToAddressHtml(order) : '';
    fitCourierAddress(courierPrintArea.querySelector('.courier-parcel-sheet'));
}

function previewCourierAddress(order) {
    setCourierAddress(order);
    const body = document.getElementById('courierAddressPreviewModalBody');
    body.replaceChildren(courierPrintArea.querySelector('.courier-parcel-sheet').cloneNode(true));
    document.getElementById('modalPrintCourierBtn').onclick = () => printOrderCourierLabel(order);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('courierAddressPreviewModal')).show();
}

function previewBlankCourierAddress() {
    previewCourierAddress(null);
}

function printOrderCourierLabel(order) {
    setCourierAddress(order);
    window.print();
}

window.addEventListener('beforeprint', () => fitCourierAddress(courierPrintArea.querySelector('.courier-parcel-sheet')));
</script>
