@if($modalItems->isEmpty())
    <span class="text-muted">—</span>
@else
    <div class="th-thumbs" style="padding-left:6px">
        @foreach($modalItems->take(3) as $idx => $itemData)
            <img src="{{ $itemData['image_url'] }}" alt="{{ $itemData['product_name'] }}" title="{{ $itemData['product_name'] }}"
                 class="rounded-2 shadow-sm {{ $itemData['is_returned'] ? 'is-returned' : '' }}"
                 style="margin-left:{{ $idx ? '-12px' : '0' }};z-index:{{ 10 - $idx }}"
                 onclick="openOrderImageModal({{ json_encode($modalItems) }}, {{ $idx }})">
        @endforeach
        @if($modalItems->count() > 3)
            <span class="badge bg-dark text-warning rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                  style="width:22px;height:22px;font-size:.6rem;margin-left:-8px;z-index:15;cursor:pointer"
                  onclick="openOrderImageModal({{ json_encode($modalItems) }}, 3)">+{{ $modalItems->count() - 3 }}</span>
        @endif
    </div>
@endif
