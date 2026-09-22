<div class="row g-2 align-items-end mb-3" id="product-size-suggestion-controls">
    <div class="col-12 col-md-7">
        <label for="size_master_id_select" class="form-label small fw-bold mb-1 text-dark">
            <i class="fa-solid fa-ruler-combined text-warning me-1"></i> Product Size Master Category <span class="text-danger">*</span>
            <span class="text-muted fw-normal fs-7">(Auto-detected)</span>
        </label>
        <select name="size_master_id" id="size_master_id_select" class="form-select form-select-sm rounded-3 fw-medium" data-size-master-select>
            <option value="">-- Select Size Master Category --</option>
            @if(isset($sizeMasters) && $sizeMasters->isNotEmpty())
                @foreach($sizeMasters as $master)
                    <option value="{{ $master->id }}" 
                        data-slug="{{ $master->slug }}" 
                        data-name="{{ strtolower($master->name) }}"
                        {{ (old('size_master_id', $product->size_master_id ?? '') == $master->id) ? 'selected' : '' }}>
                        {{ $master->name }} ({{ $master->rows->count() }} size levels)
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="col-12 col-md-5">
        <div class="form-check form-switch p-1.5 px-2 bg-light rounded-3 border d-flex align-items-center mb-0" style="min-height: 31px;">
            <input class="form-check-input ms-0 me-2 mt-0" type="checkbox" role="switch" name="display_size_chart" id="displaySizeChartSwitch" value="1" {{ old('display_size_chart', $product->display_size_chart ?? 0) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold text-dark small mb-0" for="displaySizeChartSwitch" style="font-size: 0.78rem;">
                <i class="fa-solid fa-chart-simple text-warning me-1"></i> Display Size Chart on Storefront Page
            </label>
        </div>
    </div>
</div>
