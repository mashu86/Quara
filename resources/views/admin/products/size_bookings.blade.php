@extends('layouts.admin')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h1 class="h4 mb-1">Size Bookings</h1><div class="text-muted small">Each booking reserves only the selected size and quantity.</div></div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">Back to products</a>
    </div>
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Product</th><th>Size</th><th>Booked for</th><th>Quantity</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($bookings as $booking)
            <tr>
                <td><div class="d-flex align-items-center gap-2"><img src="{{ $booking->product->primary_image_url }}" width="44" height="52" class="rounded object-fit-cover" alt=""><span class="fw-semibold">{{ $booking->product->name }}</span></div></td>
                <td>{{ $booking->productSize->size }}</td>
                <td>{{ $booking->booking_type === 'business_whatsapp' ? 'Business stock' : ($booking->booked_by ?: ucfirst(str_replace('_', ' ', $booking->booking_type))) }}</td>
                <td>{{ $booking->remaining_quantity }} remaining / {{ $booking->quantity }} booked</td>
                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                <td><button class="btn btn-sm btn-outline-danger" onclick="releaseSizeBooking('{{ route('admin.products.bookings.release', $booking) }}')">Release remaining</button></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No active size bookings.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $bookings->links() }}</div>
</div>
@endsection

@section('scripts')
<script>
async function releaseSizeBooking(url) {
    if (!confirm('Release the remaining reserved units back to online stock?')) return;
    const response = await fetch(url, {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'}});
    const result = await response.json();
    if (!response.ok || !result.success) { alert(result.message || 'Could not release booking.'); return; }
    window.location.reload();
}
</script>
@endsection
