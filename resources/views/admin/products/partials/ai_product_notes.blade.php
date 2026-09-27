<div class="text-white-50 small mt-2">Select a dress image and click Auto Fill Product to generate the product name and description from the image. Price and measurements are optional.</div>
<label for="aiProductNotes" class="form-label small mt-2">Price & measurements (optional)</label>
<textarea id="aiProductNotes" name="ai_product_notes" class="form-control bg-dark text-white border-secondary" rows="4" placeholder="169/-&#10;c40&#10;w40&#10;L40">{{ old('ai_product_notes') }}</textarea>
<div class="text-white-50 small mt-1">Paste price first, then chest/c, waist/w and length/l in inches. Or use 129,30,30,30 (price, chest, waist, length). Short names and common spelling mistakes are supported.</div>
<div id="aiSizeRowPicker" class="d-none mt-2">
    <label for="aiSizeRow" class="form-label small">Apply measurements to</label>
    <select id="aiSizeRow" class="form-select form-select-sm"></select>
</div>
<div class="form-check mt-2 small">
    <input type="checkbox" class="form-check-input" id="aiWhatsAppBooked" name="ai_whatsapp_booked" value="1" {{ old('ai_whatsapp_booked') ? 'checked' : '' }}>
    <label class="form-check-label" for="aiWhatsAppBooked">WhatsApp Booked (optional)</label>
</div>
<div id="aiProductFeedback" class="small mt-2" role="status" aria-live="polite"></div>
