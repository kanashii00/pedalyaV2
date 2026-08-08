@extends('layouts.admin')

@section('title', 'Rental Details')

@section('page-header')
    <h1>Rental Details</h1>
    <p>Reference: {{ $rental->rentalId }}</p>
@endsection

@section('actions')
<a href="{{ route('admin.rentals.index') }}" class="btn-admin btn-admin--secondary btn-admin--sm">
    <i class="bi bi-arrow-left me-1"></i>Back to Rentals
</a>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Status Overview -->
    <div class="col-12">
        <x-admin.card :title="'Rental #' . $rental->id" sub="{{ $rental->created_at->format('M d, Y g:i A') }}">
            <x-slot:tools>
                @switch($rental->status)
                    @case('active')<x-admin.badge type="success" label="Active"/>@break
                    @case('pending')<x-admin.badge type="warning" label="Pending"/>@break
                    @case('completed')<x-admin.badge type="success" label="Completed"/>@break
                    @case('cancelled')<x-admin.badge type="neutral" label="Cancelled"/>@break
                    @case('overdue')<x-admin.badge type="danger" label="Overdue"/>@break
                    @default<x-admin.badge :label="ucfirst($rental->status)"/>@endswitch
            </x-slot:tools>
            <div class="d-flex gap-2 flex-wrap">
                @if($rental->status === 'pending')
                    <form action="{{ route('admin.rentals.approve', $rental->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-admin btn-admin--primary" data-confirm="Approve this rental?">
                            <i class="bi bi-check-lg me-1"></i>Approve Rental
                        </button>
                    </form>
                @endif
                @if(in_array($rental->status, ['pending', 'active', 'overdue'], true))
                    <form action="{{ route('admin.rentals.cancel', $rental->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="reason" value="Cancelled by administrator">
                        <button type="submit" class="btn-admin btn-admin--danger btn-admin--soft"
                                data-confirm="Cancel this rental? The bicycle will be released.">
                            <i class="bi bi-x-lg me-1"></i>Cancel Rental
                        </button>
                    </form>
                @endif
            </div>
        </x-admin.card>
    </div>

    <!-- Rental Info -->
    <div class="col-lg-6">
        <x-admin.card title="Rental Information" bodyClass="h-100">
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><th class="text-muted" style="width:45%">Rental ID</th><td>{{ $rental->rentalId }}</td></tr>
                    <tr><th class="text-muted">Bicycle</th><td>{{ $rental->bicycle->name ?? $rental->bicycleName }} ({{ $rental->bicycleSerial ?? '—' }})</td></tr>
                    <tr><th class="text-muted">Rate Per Hour</th><td>₱{{ number_format($rental->ratePerHour, 2) }}</td></tr>
                    <tr><th class="text-muted">Start Time</th><td>{{ $rental->startTime ? $rental->startTime->format('M d, Y g:i A') : '—' }}</td></tr>
                    <tr><th class="text-muted">End Time</th><td>{{ $rental->endTime ? $rental->endTime->format('M d, Y g:i A') : '—' }}</td></tr>
                    <tr><th class="text-muted">Duration</th><td>{{ $rental->durationFormatted ?? ($rental->durationMinutes ? floor($rental->durationMinutes / 60) . 'h ' . ($rental->durationMinutes % 60) . 'm' : '—') }}</td></tr>
                    <tr><th class="text-muted">Charged Hours</th><td>{{ $rental->chargedHours }}</td></tr>
                    <tr><th class="text-muted">Distance</th><td>{{ $rental->totalDistance }} km</td></tr>
                </tbody>
            </table>
        </x-admin.card>
    </div>

    <!-- Billing & Rider -->
    <div class="col-lg-6">
        <x-admin.card title="Billing" bodyClass="mb-4">
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><th class="text-muted" style="width:45%">Total Fee</th><td class="fw-bold">₱{{ number_format($rental->totalFee, 2) }}</td></tr>
                    <tr><th class="text-muted">Payment Status</th>
                        <td>@if($rental->paymentStatus === 'paid')<x-admin.badge type="success" label="Paid"/>@else<x-admin.badge type="warning" label="Pending"/>@endif</td>
                    </tr>
                    <tr><th class="text-muted">Payment Method</th><td>{{ $rental->paymentMethod ? ucfirst($rental->paymentMethod) : '—' }}</td></tr>
                    <tr><th class="text-muted">Reference</th><td>{{ $rental->paymentReference ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Overdue</th><td>{{ $rental->isOverdue ? 'Yes' : 'No' }}</td></tr>
                </tbody>
            </table>
        </x-admin.card>

        <x-admin.card title="Rider">
            <p class="mb-1 fw-semibold">{{ $rental->riderName }}</p>
            <p class="mb-1 text-muted" style="font-size:0.85rem;">{{ $rental->riderEmail }}</p>
            @if($rental->notes)
                <hr>
                <small class="text-muted">Notes:</small>
                <p class="mb-0" style="font-size:0.88rem;">{{ $rental->notes }}</p>
            @endif
        </x-admin.card>
    </div>

    <!-- Location History -->
    <div class="col-12">
        <x-admin.card title="Locations">
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Start Location</strong>
                    <p class="text-muted mb-0" style="font-size:0.88rem;">
                        @if($rental->startLocation)
                            {{ $rental->startLocation['lat'] }}, {{ $rental->startLocation['lng'] }}
                        @else
                            — Not recorded
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <strong>End Location</strong>
                    <p class="text-muted mb-0" style="font-size:0.88rem;">
                        @if($rental->endLocation)
                            {{ $rental->endLocation['lat'] }}, {{ $rental->endLocation['lng'] }}
                        @else
                            — Not recorded
                        @endif
                    </p>
                </div>
            </div>
        </x-admin.card>
    </div>
</div>
@endsection
