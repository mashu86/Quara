<style>
    @media (max-width: 576px) {
        #whatsappFollowupModal .modal-header { padding: 0.65rem 1rem !important; }
        #whatsappFollowupModal .modal-title { font-size: 0.82rem !important; }
        #whatsappFollowupModal .modal-body { padding: 0.75rem 0.85rem !important; }
        #whatsappFollowupModal .form-label { font-size: 0.72rem !important; margin-bottom: 0.25rem !important; }
        #whatsappFollowupModal .btn-group label { font-size: 0.68rem !important; padding: 0.35rem 0.4rem !important; }
        #whatsappFollowupModal .badge { font-size: 0.62rem !important; padding: 0.2em 0.45em !important; }
        #whatsappFollowupModal .form-control, 
        #whatsappFollowupModal textarea { font-size: 0.72rem !important; padding: 0.35rem 0.5rem !important; }
        #whatsappFollowupModal #waMessageTextarea { rows: 6; height: 140px; }
        #whatsappFollowupModal #waSendBtn { font-size: 0.75rem !important; padding: 0.45rem 0.75rem !important; }
        #whatsappFollowupModal #waCustomerPhoneDisplay { font-size: 0.72rem !important; }
    }
</style>

<!-- WhatsApp Order Follow-Up Modal -->
<div class="modal fade" id="whatsappFollowupModal" tabindex="-1" aria-labelledby="whatsappFollowupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white py-2.5 py-sm-3">
                <h5 class="modal-title fw-bold fs-6" id="whatsappFollowupModalLabel">
                    <i class="fa-brands fa-whatsapp me-2 fs-5"></i> WhatsApp Order Follow-Up
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-sm-4">
                <!-- Follow-up Type Selector -->
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Select Follow-Up Message Type</label>
                    <div class="btn-group w-100 gap-1" role="group">
                        <input type="radio" class="btn-check" name="wa_type" id="waTypeThankYou" value="thank_you" checked onchange="updateWhatsappTemplate()">
                        <label class="btn btn-outline-success rounded-3 btn-sm py-2 fw-bold d-flex align-items-center justify-content-center gap-1" for="waTypeThankYou">
                            <i class="fa-solid fa-heart me-1"></i> Thank You
                            <span id="waThankYouBadge" class="badge bg-success text-white ms-1 rounded-pill" style="font-size: 0.72rem;">0</span>
                        </label>

                        <input type="radio" class="btn-check" name="wa_type" id="waTypeCouriered" value="couriered" onchange="updateWhatsappTemplate()">
                        <label class="btn btn-outline-success rounded-3 btn-sm py-2 fw-bold d-flex align-items-center justify-content-center gap-1" for="waTypeCouriered">
                            <i class="fa-solid fa-truck-fast me-1"></i> Couriered / Dispatched
                            <span id="waCourieredBadge" class="badge bg-success text-white ms-1 rounded-pill" style="font-size: 0.72rem;">0</span>
                        </label>
                    </div>
                </div>

                <!-- Courier Information Inputs (Dynamic for Couriered type) -->
                <div id="waCourierInputsBox" class="p-3 bg-light rounded-3 border mb-3 d-none">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold mb-1">Courier Partner</label>
                            <input type="text" id="waCourierPartner" class="form-control form-control-sm rounded-2" placeholder="e.g. DTDC / Speed Post" oninput="updateWhatsappTemplate()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold mb-1">Tracking / AWB Code</label>
                            <input type="text" id="waTrackingNumber" class="form-control form-control-sm rounded-2" placeholder="e.g. D123456789" oninput="updateWhatsappTemplate()">
                        </div>
                    </div>
                </div>

                <!-- Customer Phone & Details Preview -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Recipient Phone:</span>
                    <span class="fw-bold text-dark font-monospace small" id="waCustomerPhoneDisplay">+91 ------------</span>
                </div>

                <!-- Message Box -->
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark mb-1">Message Preview (Editable)</label>
                    <textarea id="waMessageTextarea" class="form-control rounded-3 font-monospace small" rows="8" oninput="syncWhatsappUrl()"></textarea>
                </div>

                <a id="waSendBtn" href="#" target="_blank" onclick="triggerFollowupEmailAndOpenWhatsapp()" class="btn btn-success text-white rounded-pill w-100 py-2.5 fw-bold shadow-sm" style="font-size: 0.85rem;">
                    <i class="fa-brands fa-whatsapp me-1 fs-5 align-middle"></i> Send Message on WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    let currentWaOrder = null;

    function openWhatsappModal(order) {
        currentWaOrder = order;

        // Auto check courier tab if courier info exists or status is shipped/dispatched
        const isCouriered = order.courier_partner || order.tracking_number || ['shipped', 'delivered'].includes(order.order_status) || order.is_dispatched_to_courier;
        
        if (isCouriered) {
            document.getElementById('waTypeCouriered').checked = true;
        } else {
            document.getElementById('waTypeThankYou').checked = true;
        }

        document.getElementById('waCourierPartner').value = order.courier_partner || '';
        document.getElementById('waTrackingNumber').value = order.tracking_number || '';

        // Display customer phone formatted
        let cleanPhone = (order.customer_phone || '').replace(/\D/g, '');
        if (cleanPhone.length === 10) cleanPhone = '91' + cleanPhone;
        document.getElementById('waCustomerPhoneDisplay').textContent = cleanPhone ? '+' + cleanPhone : 'N/A';

        // Update count badges
        document.getElementById('waThankYouBadge').textContent = order.wa_thank_you_count || 0;
        document.getElementById('waCourieredBadge').textContent = order.wa_couriered_count || 0;

        updateWhatsappTemplate();

        const modalEl = document.getElementById('whatsappFollowupModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function updateWhatsappTemplate() {
        if (!currentWaOrder) return;

        const isCouriered = document.getElementById('waTypeCouriered').checked;
        const courierBox = document.getElementById('waCourierInputsBox');
        
        if (isCouriered) {
            courierBox.classList.remove('d-none');
        } else {
            courierBox.classList.add('d-none');
        }

        const courierPartner = document.getElementById('waCourierPartner').value.trim() || currentWaOrder.courier_partner || 'Courier Service';
        const trackingNo = document.getElementById('waTrackingNumber').value.trim() || currentWaOrder.tracking_number || 'N/A';

        const name = currentWaOrder.customer_name || 'Customer';
        const orderNo = currentWaOrder.order_number || '';
        const grandTotal = currentWaOrder.grand_total ? parseFloat(currentWaOrder.grand_total).toFixed(2) : '0.00';

        // Construct complete address string
        let addressStr = '';
        if (currentWaOrder.house_building) addressStr += currentWaOrder.house_building + ', ';
        if (currentWaOrder.street) addressStr += currentWaOrder.street + ', ';
        if (currentWaOrder.city) addressStr += currentWaOrder.city + ', ';
        if (currentWaOrder.district) addressStr += currentWaOrder.district + ', ';
        if (currentWaOrder.state) addressStr += currentWaOrder.state + ' - ';
        if (currentWaOrder.pin_code) addressStr += currentWaOrder.pin_code;
        if (!addressStr) addressStr = currentWaOrder.shipping_address || 'As specified in order';

        const storePhone = "918078037591";
        const storePhoneFormatted = "+91 8078037591";
        const storeWaLink = "https://wa.me/" + storePhone;
        const orderSuccessUrl = "{{ url('/checkout/success') }}/" + orderNo;

        const storeName = @js($siteName);
        let msg = `*Official Message from ${storeName}* (${storePhoneFormatted})\n\n`;
        msg += `Dear ${name},\n\n`;

        if (isCouriered) {
            msg += `Your order #${orderNo} has been dispatched and is on its way to you!\n\n`;
            msg += `*Order ID:* ${orderNo}\n`;
            msg += `*Total Amount:* ₹${grandTotal}\n`;
            msg += `*Courier Partner:* ${courierPartner}\n`;
            msg += `*Tracking / AWB ID:* ${trackingNo}\n\n`;
            msg += `*Order Address:*\n${addressStr}\n\n`;
            msg += `*View Order Details & Invoice:*\n${orderSuccessUrl}\n\n`;
            msg += `You can track your package using the link above.\n\n`;
        } else {
            msg += `Thank you for purchasing from ${storeName}! We have received your order #${orderNo}.\n\n`;
            msg += `*Order ID:* ${orderNo}\n`;
            msg += `*Total Amount:* ₹${grandTotal}\n\n`;
            msg += `*Order Address:*\n${addressStr}\n\n`;
            msg += `*View Order Details & Invoice:*\n${orderSuccessUrl}\n\n`;
            msg += `We are preparing your items with care and will notify you as soon as it ships.\n\n`;
        }

        msg += `*Website:* https://quarawaldrop.shop/\n`;
        msg += `*Instagram:* https://www.instagram.com/quara_waldrop?igsi=MXZlNzVtOGdhZjdoNA==\n\n`;
        msg += `*Direct WhatsApp Support:*\n`;
        msg += `*Phone:* ${storePhoneFormatted}\n`;
        msg += `*WhatsApp Link:* ${storeWaLink}\n\n`;
        msg += `Warm regards,\n${storeName}`;

        document.getElementById('waMessageTextarea').value = msg;
        syncWhatsappUrl();
    }

    function triggerFollowupEmailAndOpenWhatsapp() {
        if (!currentWaOrder) return;

        const isCouriered = document.getElementById('waTypeCouriered').checked;
        const type = isCouriered ? 'couriered' : 'thank_you';

        // Increment WhatsApp follow-up count in database via AJAX
        fetch(`/admin/orders/${currentWaOrder.id}/increment-wa-count`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ type: type })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                currentWaOrder.wa_thank_you_count = data.wa_thank_you_count;
                currentWaOrder.wa_couriered_count = data.wa_couriered_count;
                document.getElementById('waThankYouBadge').textContent = data.wa_thank_you_count;
                document.getElementById('waCourieredBadge').textContent = data.wa_couriered_count;
            }
        }).catch(err => console.error('Count update error:', err));

        // If customer email exists, send follow-up email via AJAX
        if (currentWaOrder.customer_email) {
            fetch(`/admin/orders/${currentWaOrder.id}/send-followup-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    is_couriered: isCouriered,
                    courier_partner: document.getElementById('waCourierPartner').value,
                    tracking_number: document.getElementById('waTrackingNumber').value
                })
            }).catch(err => console.error('Email send error:', err));
        }

        return true;
    }

    function syncWhatsappUrl() {
        if (!currentWaOrder) return;

        let cleanPhone = (currentWaOrder.customer_phone || '').replace(/\D/g, '');
        if (cleanPhone.length === 10) cleanPhone = '91' + cleanPhone;

        const text = document.getElementById('waMessageTextarea').value;
        const encodedText = encodeURIComponent(text);

        const sendBtn = document.getElementById('waSendBtn');
        sendBtn.href = `https://wa.me/${cleanPhone}?text=${encodedText}`;
    }
</script>
