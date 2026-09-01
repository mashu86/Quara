@foreach($expenses as $expense)
    <tr>
        <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
        <td class="fw-semibold">
            {{ $expense->title ?? $expense->expense_name }}
        </td>
        <td><span class="badge bg-light text-dark border">{{ $expense->category ?? 'General' }}</span></td>
        <td class="fw-bold text-danger">₹{{ number_format($expense->amount, 2) }}</td>
        <td>
            @if($expense->receipt_image)
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-primary fw-semibold" onclick="showExpenseDetail({{ $expense->id }})">
                    <i class="fa-solid fa-file-invoice text-success me-1"></i> View Bill
                </button>
            @else
                <span class="text-muted extra-small">No Receipt</span>
            @endif
        </td>
        <td class="small text-muted text-truncate" style="max-width: 180px;">{{ $expense->notes ?? '-' }}</td>
        <td class="text-end pe-3">
            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                <button type="button" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" onclick="showExpenseDetail({{ $expense->id }})" title="View Expense">
                    <i class="fa-solid fa-eye"></i>
                </button>
                <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Expense">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
                <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Are you sure you want to delete this expense record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Delete Expense">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
