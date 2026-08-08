@extends('layouts.admin')

@section('title', 'Payment Successful')

@section('page-header')
    <h1>Payment Successful</h1>
    <p>Your payment has been processed successfully</p>
@endsection

@section('content')
<div class="row justify-center">
    <div class="col-12 col-md-8 col-lg-6">
        <x-admin.card class="text-center py-5">
            <div class="kpi__icon mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2.5rem; background: var(--success-soft); color: var(--success);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="mb-2">Payment Confirmed!</h2>
            <p class="text-muted mb-4">Your bicycle rental payment has been successfully processed.</p>

            <div class="row g-3 mb-4 text-start">
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Payment Reference</div>
                        <div class="fw-semibold">{{ $payment->paymentReference }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Amount Paid</div>
                        <div class="fw-bold text-success">₱{{ number_format($payment->totalAmount, 2) }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Payment Method</div>
                        <div class="fw-semibold">{{ $payment->getPaymentMethodLabel() }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Bicycle</div>
                        <div class="fw-semibold">{{ $payment->bicycle->name ?? '—' }}</div>
                    </div>
                </div>
            </div>

            @if($payment->rental)
                <div class="alert alert-success mb-4">
                    <i class="bi bi-key me-2"></i>
                    <strong>Rental Activated!</strong>
                    <div class="mt-2">
                        <a href="{{ route('admin.rentals.show', $payment->rental) }}" class="btn-admin btn-admin--success btn-admin--sm">
                            <i class="bi bi-key me-1"></i>View Rental
                        </a>
                        <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn-admin btn-admin--secondary btn-admin--sm ms-2" target="_blank">
                            <i class="bi bi-receipt me-1"></i>View Receipt
                        </a>
                    </div>
                </div>
            @endif

            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('admin.payments.index') }}" class="btn-admin btn-admin--primary">
                    <i class="bi bi-credit-card me-1"></i>All Payments
                </a>
                <a href="{{ route('admin.dashboard') }}" class="btn-admin btn-admin--secondary">
                    <i class="bi bi-house-door me-1"></i>Dashboard
                </a>
            </div>
        </x-admin.card>
    </div>
</div>
@endsection