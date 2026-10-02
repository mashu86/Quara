@extends('layouts.admin')

@section('title', $category->name . ' Products - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <a href="{{ route('admin.categories.index') }}" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i> Categories</a>
        <h4 class="fw-bold mb-0 mt-1">{{ $category->name }} <span class="text-muted fw-normal">· Products</span></h4>
    </div>
</div>

<form method="GET" action="{{ route('admin.categories.products', $category) }}" class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="product-filter">Products to show</label>
            <select id="product-filter" name="filter" class="form-select rounded-3">
                <option value="all" @selected($filter === 'all')>All products</option>
                <option value="not_sold_out" @selected($filter === 'not_sold_out')>Not sold out (booked or available)</option>
                <option value="available" @selected($filter === 'available')>Available only (not booked)</option>
            </select>
        </div>
        <div class="col-md-5">
            <div class="small text-muted">Choose a filter, then search separately inside either product list.</div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-dark rounded-3 flex-grow-1" type="submit">Apply</button>
            <a class="btn btn-outline-secondary rounded-3" href="{{ route('admin.categories.products', $category) }}" title="Reset filters"><i class="fa-solid fa-rotate-left"></i></a>
        </div>
    </div>
</form>

<div class="row g-4">
    @foreach([
        ['key' => 'inside', 'title' => 'Products in this category', 'products' => $insideProducts, 'icon' => 'fa-xmark', 'button' => 'btn-outline-danger', 'url' => 'admin.categories.products.detach'],
        ['key' => 'outside', 'title' => 'Products not in this category', 'products' => $outsideProducts, 'icon' => 'fa-plus', 'button' => 'btn-outline-success', 'url' => 'admin.categories.products.attach'],
    ] as $box)
        <div class="col-xl-6">
            <section class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">{{ $box['title'] }}</h5>
                    <span class="badge bg-light text-dark border">{{ $box['products'] instanceof \Illuminate\Pagination\AbstractPaginator ? $box['products']->total() : 0 }}</span>
                </div>
                <div class="card-body px-4 product-list" data-side="{{ $box['key'] }}" data-next-url="{{ $box['products'] instanceof \Illuminate\Pagination\AbstractPaginator ? $box['products']->nextPageUrl() : '' }}" data-action-url="{{ route($box['url'], [$category, '__PRODUCT_ID__']) }}" data-action-method="{{ $box['key'] === 'inside' ? 'DELETE' : 'POST' }}">
                    <div class="d-flex flex-wrap gap-3 small text-muted border-bottom pb-3 mb-2" aria-label="Stock status legend">
                        <span class="d-inline-flex align-items-center gap-2"><span class="rounded-circle" style="width:10px;height:10px;background:#198754"></span> Available</span>
                        <span class="d-inline-flex align-items-center gap-2"><span class="rounded-circle" style="width:10px;height:10px;background:#fd7e14"></span> Booked</span>
                        <span class="d-inline-flex align-items-center gap-2"><span class="rounded-circle" style="width:10px;height:10px;background:#dc3545"></span> Sold out</span>
                    </div>
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="search" class="form-control category-product-search" placeholder="Search {{ $box['key'] === 'inside' ? 'products in this category' : 'products not in this category' }}" aria-label="Search {{ $box['title'] }}" data-side="{{ $box['key'] }}" value="{{ request('search_side') === $box['key'] ? request('side_search') : '' }}">
                    </div>
                    @if($box['products'] instanceof \Illuminate\Pagination\AbstractPaginator && $box['products']->count())
                        <div class="list-group list-group-flush product-items-scroll" style="max-height:65vh;overflow-y:auto;overscroll-behavior:contain">
                            @include('admin.categories.partials.product_items', ['products' => $box['products'], 'category' => $category, 'side' => $box['key']])
                        </div>
                        <div class="product-load-sentinel text-center small text-muted py-3" aria-live="polite">{{ $box['products']->hasMorePages() ? 'Scroll down to load more…' : 'All matching products loaded.' }}</div>
                    @else
                        <div class="list-group list-group-flush product-items-scroll" style="max-height:65vh;overflow-y:auto;overscroll-behavior:contain">@include('admin.categories.partials.product_items', ['products' => collect(), 'category' => $category, 'side' => $box['key']])</div>
                        <div class="product-load-sentinel text-center small text-muted py-3" aria-live="polite">No products match this filter.</div>
                    @endif
                </div>
            </section>
        </div>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bindActionButtons = (root = document) => root.querySelectorAll('.product-category-action').forEach(button => {
        if (button.dataset.actionBound === 'true') return;
        button.dataset.actionBound = 'true';
        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                await moveProduct(button.closest('.category-product-row'), button.dataset.url, button.dataset.method);
            } catch (error) {
                button.disabled = false;
                alert(error.message || 'Could not update category products.');
            }
        });
    });

    bindActionButtons();

    const categoryProductsUrl = @json(route('admin.categories.products', $category));
    document.querySelectorAll('.category-product-search').forEach(input => {
        let timer;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => searchProductList(input), 250);
        });
    });

    const searchProductList = async (input) => {
        const side = input.dataset.side;
        const list = document.querySelector('.product-list[data-side="' + side + '"]');
        const scroller = list.querySelector('.product-items-scroll');
        const sentinel = list.querySelector('.product-load-sentinel');
        const url = new URL(categoryProductsUrl, window.location.origin);
        url.searchParams.set('ajax', '1');
        url.searchParams.set('side', side);
        url.searchParams.set('search_side', side);
        url.searchParams.set('side_search', input.value.trim());
        url.searchParams.set('filter', document.getElementById('product-filter').value);
        list.dataset.loading = 'true';
        if (sentinel) sentinel.textContent = 'Searching…';
        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Search failed.');
            scroller.innerHTML = data.html;
            list.dataset.nextUrl = data.next_page_url || '';
            if (sentinel) sentinel.textContent = data.has_more ? 'Scroll down to load more…' : (data.total ? 'All matching products loaded.' : 'No products match this search.');
            const badge = list.closest('.card').querySelector('.card-header .badge');
            if (badge) badge.textContent = data.total;
            bindActionButtons(scroller);
            bindDraggableRows(scroller);
            list.dataset.loading = 'false';
        } catch (error) {
            list.dataset.loading = 'false';
            if (sentinel) sentinel.textContent = error.message || 'Search failed.';
        }
    };

    const moveProduct = async (row, url, method) => {
        const sourceList = row.closest('.product-list');
        const destinationSide = sourceList.dataset.side === 'inside' ? 'outside' : 'inside';
        const destinationList = document.querySelector('.product-list[data-side="' + destinationSide + '"]');
        const destinationItems = destinationList.querySelector('.list-group');
        const response = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Could not update category products.');

        const productId = row.dataset.productId;
        const destinationUrl = destinationList.dataset.actionUrl.replace('__PRODUCT_ID__', productId);
        const nextRow = row.cloneNode(true);
        delete nextRow.dataset.dragBound;
        delete nextRow.dataset.actionBound;
        nextRow.dataset.url = destinationUrl;
        nextRow.dataset.method = destinationList.dataset.actionMethod;
        const previousAction = nextRow.querySelector('.product-category-action');
        const actionButton = document.createElement('button');
        actionButton.type = 'button';
        actionButton.className = 'btn ' + (destinationSide === 'inside' ? 'btn-outline-danger' : 'btn-outline-success') + ' rounded-circle product-category-action';
        actionButton.style.cssText = 'width:38px;height:38px';
        actionButton.title = destinationSide === 'inside' ? 'Remove from category' : 'Add to category';
        actionButton.setAttribute('aria-label', (destinationSide === 'inside' ? 'Remove ' : 'Add ') + row.dataset.productName + ' ' + (destinationSide === 'inside' ? 'from' : 'to') + ' category');
        actionButton.dataset.url = destinationUrl;
        actionButton.dataset.method = destinationList.dataset.actionMethod;
        actionButton.innerHTML = '<i class="fa-solid ' + (destinationSide === 'inside' ? 'fa-xmark' : 'fa-plus') + '"></i>';
        previousAction.replaceWith(actionButton);
        destinationItems.querySelector('.text-center.text-muted')?.remove();
        destinationItems.prepend(nextRow);
        row.remove();
        bindActionButtons(destinationItems);
        bindDraggableRows(destinationItems);
        bindDragAndDrop(destinationList);
        updateListCount(sourceList, -1);
        updateListCount(destinationList, 1);
    };

    const updateListCount = (list, delta) => {
        const badge = list.closest('.card').querySelector('.card-header .badge');
        if (badge) badge.textContent = Math.max(0, (parseInt(badge.textContent, 10) || 0) + delta);
    };

    const bindDragAndDrop = (list) => {
        const scroller = list.querySelector('.product-items-scroll');
        if (!scroller || scroller.dataset.dragBound === 'true') return;
        scroller.dataset.dragBound = 'true';
        scroller.addEventListener('dragenter', event => {
            if (event.dataTransfer.types.includes('text/plain')) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                list.classList.add('border', 'border-primary', 'rounded-3');
            }
        });
        scroller.addEventListener('dragover', event => {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            list.classList.add('border', 'border-primary', 'rounded-3');
        });
        scroller.addEventListener('dragleave', event => {
            if (!scroller.contains(event.relatedTarget)) list.classList.remove('border', 'border-primary', 'rounded-3');
        });
        scroller.addEventListener('drop', async event => {
            event.preventDefault();
            list.classList.remove('border', 'border-primary', 'rounded-3');
            let payload;
            try { payload = JSON.parse(event.dataTransfer.getData('text/plain') || '{}'); } catch (_) { payload = {}; }
            const productId = payload.productId;
            const sourceSide = payload.sourceSide;
            if (!productId || sourceSide === list.dataset.side) return;
            const sourceRow = document.querySelector('.product-list[data-side="' + sourceSide + '"] [data-product-id="' + productId + '"]');
            if (!sourceRow) return;
            try {
                await moveProduct(sourceRow, list.dataset.actionUrl.replace('__PRODUCT_ID__', productId), list.dataset.actionMethod);
            } catch (error) {
                alert(error.message || 'Could not move product.');
            }
        });
    };

    const bindDraggableRows = (root = document) => root.querySelectorAll('.category-product-row').forEach(row => {
        if (row.dataset.dragBound === 'true') return;
        row.dataset.dragBound = 'true';
        row.addEventListener('dragstart', event => {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', JSON.stringify({
                productId: row.dataset.productId,
                sourceSide: row.closest('.product-list').dataset.side
            }));
            row.classList.add('opacity-50');
        });
        row.addEventListener('dragend', () => row.classList.remove('opacity-50'));
    });

    document.querySelectorAll('.product-list').forEach(list => bindDragAndDrop(list));
    bindDraggableRows();

    const loadMore = async (list) => {
        if (!list.dataset.nextUrl || list.dataset.loading === 'true') return;
        list.dataset.loading = 'true';
        const sentinel = list.querySelector('.product-load-sentinel');
        if (sentinel) sentinel.textContent = 'Loading more products…';
        try {
            const url = new URL(list.dataset.nextUrl, window.location.origin);
            url.searchParams.set('ajax', '1');
            url.searchParams.set('side', list.dataset.side);
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Could not load more products.');
            const data = await response.json();
            const items = list.querySelector('.list-group');
            const template = document.createElement('template');
            template.innerHTML = data.html;
            items.append(template.content);
            bindActionButtons(items);
            bindDraggableRows(items);
            list.dataset.nextUrl = data.next_page_url || '';
            if (sentinel) sentinel.textContent = data.has_more ? 'Scroll down to load more…' : 'All matching products loaded.';
        } catch (error) {
            if (sentinel) sentinel.textContent = error.message || 'Could not load more products.';
        } finally {
            list.dataset.loading = 'false';
        }
    };

    if ('IntersectionObserver' in window) {
        document.querySelectorAll('.product-list').forEach(list => {
            const sentinel = list.querySelector('.product-load-sentinel');
            if (!sentinel) return;
            const observer = new IntersectionObserver(entries => entries.forEach(entry => {
                if (entry.isIntersecting) loadMore(list);
            }), { root: list.querySelector('.product-items-scroll'), rootMargin: '180px' });
            observer.observe(sentinel);
        });
    } else {
        document.querySelectorAll('.product-items-scroll').forEach(scroller => scroller.addEventListener('scroll', () => {
            const list = scroller.closest('.product-list');
            const sentinel = list.querySelector('.product-load-sentinel');
            if (sentinel && scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 180) loadMore(list);
        }));
    }
});
</script>
@endsection
