@extends('layouts.admin')
@section('title', 'Master Coupon')
@section('content')
<div class="container-fluid py-3">
    <style>
        .coupon-form-grid > [class*="col-"] { min-width: 0; }
        .coupon-form-grid .form-control, .coupon-form-grid .form-select { min-height: 44px; width: 100%; }
        .coupon-form-grid .form-label { min-height: 1.5rem; margin-bottom: .4rem; font-weight: 600; }
        .coupon-picker-menu { max-height: 280px; overflow-y: auto; }
        .coupon-picker-menu label { cursor: pointer; }
        .category-picker-button { min-height: 44px; width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        @media (max-width: 767.98px) {
            .coupon-form-card .card-body { padding: 1rem; }
            .coupon-form-grid { --bs-gutter-x: .85rem; --bs-gutter-y: .9rem; }
            .coupon-form-grid > [class*="col-"] { width: 100%; }
            .coupon-form-actions { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
            .coupon-form-actions .btn { width: 100%; min-height: 44px; }
        .coupon-list-card table { min-width: 850px; }
        .coupon-drawer .offcanvas-body { overflow-y: auto; }
        .coupon-drawer .coupon-form-actions { position: sticky; bottom: -1rem; background: #fff; padding: .75rem 0 .25rem; border-top: 1px solid #eee; z-index: 2; }
        }
    </style>
    <h3 class="mb-3">Master Coupon</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="d-flex justify-content-end mb-3"><button class="btn btn-warning" type="button" id="addCouponButton"><i class="fa-solid fa-plus me-1"></i> Add Coupon</button></div>
    <div class="offcanvas offcanvas-end coupon-drawer" tabindex="-1" id="couponDrawer" aria-labelledby="couponFormTitle" style="--bs-offcanvas-width: min(680px, 100vw);">
      <div class="offcanvas-header border-bottom"><h5 class="offcanvas-title" id="couponFormTitle">Add Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
      <div class="offcanvas-body">
        <form method="POST" action="{{ route('admin.master-coupons.store') }}" id="couponForm">@csrf
            <input type="hidden" name="_method" id="couponMethod" value="POST">
            <div class="row g-3 coupon-form-grid">
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Code</label><input class="form-control" name="code" required></div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Discount Type</label><select class="form-select" name="discount_type"><option value="fixed">Fixed ₹</option><option value="percentage">Percentage</option></select></div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Discount Value</label><input class="form-control" type="number" step="0.01" min="0.01" name="discount_value" required></div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Minimum Purchase (&gt; ₹)</label><input class="form-control" type="number" step="0.01" min="0" name="minimum_purchase" value="0" required></div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Start</label><input class="form-control" type="datetime-local" name="starts_at" required></div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">End</label><input class="form-control" type="datetime-local" name="ends_at" required></div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Usage Limit</label><select class="form-select" name="usage_limit_mode" id="limitMode"><option value="all">Everyone</option><option value="limited">First N people</option></select><input class="form-control mt-2" type="number" min="1" name="usage_limit" id="limitCount" placeholder="Number of people" disabled></div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Combine with other offers?</label><select class="form-select" name="allow_with_offer"><option value="0">No</option><option value="1">Yes</option></select></div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">Categories</label>
                    <div class="dropdown mt-2" id="categoryPicker">
                        <button class="form-select text-start" type="button" id="categoryPickerButton" data-bs-toggle="dropdown" aria-expanded="false">Select categories</button>
                        <div class="dropdown-menu w-100 p-2 coupon-picker-menu" aria-labelledby="categoryPickerButton" style="max-height: 260px; overflow-y: auto;">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                                <button class="btn btn-sm btn-link p-0 text-decoration-none fw-semibold" type="button" id="selectAllCategories">Select all</button>
                                <button class="btn btn-sm btn-link p-0 text-decoration-none text-danger" type="button" id="removeAllCategories">Remove all</button>
                            </div>
                            @foreach($categories as $category)
                                <label class="d-flex align-items-center gap-2 py-1 mb-1"><input class="form-check-input m-0 category-option" type="checkbox" name="category_ids[]" value="{{ $category->id }}" data-name="{{ $category->name }}"> <span>{{ $category->name }}</span></label>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-text" id="categorySelectionText">All active categories selected</div>
                </div>
                <div class="col-12 col-md-6 col-xl-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                <div class="col-12 coupon-form-actions"><button class="btn btn-warning">Save Coupon</button><button type="reset" class="btn btn-outline-secondary" id="resetCoupon">Clear</button></div>
            </div>
        </form>
      </div>
    </div>
    <div class="card coupon-list-card"><div class="table-responsive"><table class="table table-striped mb-0"><thead><tr><th>Code / Name</th><th>Discount</th><th>Minimum</th><th>Validity</th><th>Usage</th><th>Categories</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($coupons as $coupon)<tr><td><strong>{{ $coupon->code }}</strong><br>{{ $coupon->name }}</td><td>{{ $coupon->discount_type === 'fixed' ? '₹' : '' }}{{ rtrim(rtrim(number_format($coupon->discount_value,2), '0'), '.') }}{{ $coupon->discount_type === 'percentage' ? '%' : '' }}</td><td>&gt; ₹{{ number_format($coupon->minimum_purchase,2) }}</td><td>{{ $coupon->starts_at->format('d M Y H:i') }} – {{ $coupon->ends_at->format('d M Y H:i') }}</td><td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</td><td>{{ $coupon->all_categories ? 'All' : $coupon->categories->pluck('name')->join(', ') }}</td><td><div class="form-check form-switch d-flex align-items-center gap-2 mb-0"><input class="form-check-input coupon-status-switch m-0" type="checkbox" role="switch" aria-label="Toggle {{ $coupon->code }} status" @checked($coupon->status) data-url="{{ route('admin.master-coupons.toggle-status', $coupon) }}"><span class="coupon-status-label small {{ $coupon->status ? 'text-success' : 'text-muted' }}">{{ $coupon->status ? 'Active' : 'Inactive' }}</span></div></td><td><button type="button" class="btn btn-sm btn-outline-primary edit-coupon" data-coupon="{{ json_encode($coupon->only(['id','code','name','discount_type','discount_value','minimum_purchase','starts_at','ends_at','usage_limit','allow_with_offer','all_categories','status']) + ['category_ids' => $coupon->categories->pluck('id')], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}" data-url="{{ route('admin.master-coupons.update',$coupon) }}">Edit</button></td></tr>@empty<tr><td colspan="8" class="text-center py-4">No coupons yet.</td></tr>@endforelse
    </tbody></table></div></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form=document.getElementById('couponForm'),limitMode=document.getElementById('limitMode'),limitCount=document.getElementById('limitCount'),drawer=bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('couponDrawer'));
    const pickerButton=document.getElementById('categoryPickerButton'),selectAll=document.getElementById('selectAllCategories'),removeAll=document.getElementById('removeAllCategories'),categoryOptions=[...document.querySelectorAll('.category-option')],selectionText=document.getElementById('categorySelectionText');
    const updateCategorySummary=()=>{const selected=categoryOptions.filter(option=>option.checked);const allSelected=categoryOptions.length>0&&selected.length===categoryOptions.length;if(selected.length===0){pickerButton.textContent='Select categories';selectionText.textContent='No categories selected';}else if(allSelected){pickerButton.textContent='All categories selected';selectionText.textContent='All active categories selected';}else{pickerButton.textContent=selected.map(option=>option.dataset.name).join(', ');selectionText.textContent=selected.length+' categor'+(selected.length===1?'y':'ies')+' selected';}};
    const sync=()=>{limitCount.disabled=limitMode.value!=='limited';if(limitMode.value==='all')limitCount.value='';updateCategorySummary();};
    limitMode.addEventListener('change',sync);categoryOptions.forEach(option=>option.addEventListener('change',updateCategorySummary));selectAll.addEventListener('click',()=>{categoryOptions.forEach(option=>option.checked=true);updateCategorySummary();});removeAll.addEventListener('click',()=>{categoryOptions.forEach(option=>option.checked=false);updateCategorySummary();});
    const resetForm=()=>{form.reset();form.action="{{ route('admin.master-coupons.store') }}";document.getElementById('couponMethod').value='POST';document.getElementById('couponFormTitle').textContent='Add Coupon';form.elements.minimum_purchase.value='0';categoryOptions.forEach(option=>option.checked=false);sync();};
    document.getElementById('addCouponButton').addEventListener('click',()=>{resetForm();drawer.show();});
    document.querySelectorAll('.edit-coupon').forEach(button=>button.addEventListener('click',()=>{const data=JSON.parse(button.dataset.coupon);resetForm();form.action=button.dataset.url;document.getElementById('couponMethod').value='PUT';document.getElementById('couponFormTitle').textContent='Edit Coupon';for(const key of ['code','name','discount_type','discount_value','minimum_purchase','allow_with_offer','status'])form.elements[key].value=data[key];form.elements.starts_at.value=data.starts_at.slice(0,16);form.elements.ends_at.value=data.ends_at.slice(0,16);limitMode.value=data.usage_limit===null?'all':'limited';limitCount.value=data.usage_limit??'';categoryOptions.forEach(option=>option.checked=data.all_categories||data.category_ids.includes(Number(option.value)));sync();drawer.show();}));
    document.querySelectorAll('.coupon-status-switch').forEach(toggle=>toggle.addEventListener('change',async()=>{const previous=!toggle.checked;toggle.disabled=true;try{const response=await fetch(toggle.dataset.url,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}});const data=await response.json();if(!response.ok||!data.success)throw new Error(data.message||'Could not update coupon status.');toggle.checked=data.status;const label=toggle.parentElement.querySelector('.coupon-status-label');label.textContent=data.label;label.classList.toggle('text-success',data.status);label.classList.toggle('text-muted',!data.status);}catch(error){toggle.checked=previous;alert(error.message);}finally{toggle.disabled=false;}}));
    document.getElementById('resetCoupon').addEventListener('click',()=>setTimeout(resetForm,0));sync();
    @if($errors->any()) drawer.show(); @endif
});
</script>
@endsection
