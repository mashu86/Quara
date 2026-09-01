@foreach($incomes as $inc)
    <tr>
        <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($inc->income_date)->format('M d, Y') }}</td>
        <td class="fw-semibold text-dark">
            {{ $inc->income_name }}
            @if($inc->notes)
                <div class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $inc->notes }}">
                    {{ $inc->notes }}
                </div>
            @endif
        </td>
        <td>
            <span class="badge bg-{{ $inc->type === 'wholesale_selling' ? 'primary' : 'info' }}-subtle text-{{ $inc->type === 'wholesale_selling' ? 'primary' : 'dark' }} border">
                {{ $inc->type_label }}
            </span>
        </td>
        <td class="fw-semibold">₹{{ number_format($inc->income_price, 2) }}</td>
        <td>
            @if($inc->type === 'wholesale_selling')
                <span class="badge bg-dark text-white rounded-pill px-2.5">{{ $inc->selling_pieces }} pcs</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td class="fw-bold text-success fs-6">₹{{ number_format($inc->total_income_amount, 2) }}</td>
        <td>
            <form action="{{ route('admin.incomes.toggle-status', $inc->id) }}" method="POST" class="d-inline mb-0">
                @csrf
                <button type="submit" class="btn btn-xs border-0 p-0 shadow-none" title="Click to toggle status">
                    <span class="badge bg-{{ $inc->status === 'active' ? 'success' : 'secondary' }} rounded-pill px-2.5 py-1">
                        <i class="fa-solid fa-{{ $inc->status === 'active' ? 'check-circle' : 'minus-circle' }} me-1"></i>
                        {{ ucfirst($inc->status) }}
                    </span>
                </button>
            </form>
        </td>
        <td class="text-end pe-3">
            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                <button type="button" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" onclick="showIncomeDetail({{ $inc->id }})" title="View Income Details">
                    <i class="fa-solid fa-eye"></i>
                </button>
                <a href="{{ route('admin.incomes.edit', $inc->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Income">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
                <form action="{{ route('admin.incomes.destroy', $inc->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Are you sure you want to delete this income record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Delete Income">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
