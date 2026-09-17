@extends('layouts.admin')

@section('title', 'Ladies Size Guide - ' . $siteName)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark"><i class="fa-solid fa-ruler-combined text-primary me-2"></i>Ladies Size Guide</h1>
            <p class="text-muted small mb-0">The exact reference used by product size suggestions and customers.</p>
        </div>
        <a href="{{ route('products.size-guide') }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Open customer guide</a>
    </div>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        @include('partials.ladies_size_guide', ['guideId' => 'adminSizeGuideCard'])
    </div>
</div>
@endsection
