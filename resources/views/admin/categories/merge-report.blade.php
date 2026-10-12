@extends('layouts.admin')

@section('title', 'Category Merge Report - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Category Merge Report</h4>
    <a class="btn btn-warning rounded-pill" href="{{ route('admin.categories.merge') }}">Merge Categories</a>
</div>
<div class="card border-0 rounded-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>New Category</th><th>Old Categories</th><th>Merged Date</th></tr></thead>
            <tbody>
            @forelse($reports as $report)
                <tr>
                    <td class="fw-semibold">{{ $report->new_category_name }}</td>
                    <td>{{ implode(', ', json_decode($report->old_categories, true) ?: []) }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($report->created_at)->format('d M Y, h:i A') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-5">No category merges yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $reports->links() }}</div>
</div>
@endsection
