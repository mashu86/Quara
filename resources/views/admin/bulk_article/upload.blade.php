@extends('layouts.admin')

@section('title', 'Upload Courier File')

@section('content')
<div class="container-fluid py-3">
    <h3>Selected Orders &amp; Courier File</h3>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Selected Orders ({{ $orders->count() }})</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Order ID</th><th>Client</th><th>Address</th><th>Amount</th></tr></thead>
                        <tbody>
                        @foreach($orders as $o)
                            <tr><td>{{ $o->order_number }}</td><td>{{ $o->customer_name }}</td><td>{{ $o->full_address }}</td><td>₹{{ number_format($o->grand_total, 2) }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Upload Courier File</div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" action="{{ route('admin.bulk-article.parse') }}">
                        @csrf
                        <label class="form-label">Upload XLSX / XLS / CSV File</label>
                        <input required type="file" name="courier_file" class="form-control" accept=".xlsx,.xls,.csv,.txt">
                        <button class="btn btn-primary mt-3">Upload File</button>
                    </form>
                </div>
            </div>

            @php($headers = session('article_import.headers'))
            @if($headers)
                <div class="card mt-3">
                    <div class="card-header">Map Courier Columns</div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.bulk-article.match') }}">
                            @csrf
                            @foreach(['receiver_name' => 'Receiver Name', 'receiver_address' => 'Receiver Address', 'pin_code' => 'Destination PIN (optional)', 'article_number' => 'Article Number', 'tracking_number' => 'Tracking Number (optional)', 'weight' => 'Weight', 'courier_charge' => 'Courier Charge', 'courier_name' => 'Courier (optional)'] as $key => $label)
                                @php($required = !str_contains($label, '(optional)'))
                                <div class="mb-2">
                                    <label class="form-label">{{ $label }}</label>
                                    <select class="form-select" name="map[{{ $key }}]" @if($required) required @endif>
                                        <option value="">Not mapped</option>
                                        @foreach($headers as $i => $header)
                                            @php($headerText = strtolower($header))
                                            @php($suggested = str_contains($headerText, str_replace('_', ' ', $key)) || ($key === 'article_number' && str_contains($headerText, 'article')) || ($key === 'courier_charge' && (str_contains($headerText, 'charge') || str_contains($headerText, 'tariff') || str_contains($headerText, 'tarrif'))) || ($key === 'receiver_name' && str_contains($headerText, 'receiver-name')) || ($key === 'receiver_address' && str_contains($headerText, 'receiver-address')) || ($key === 'pin_code' && (str_contains($headerText, 'destination-pin') || str_contains($headerText, 'pin code'))))
                                            <option value="{{ $i }}" @selected($suggested)>{{ $header }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                            <button class="btn btn-success mt-2">Match Records</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
