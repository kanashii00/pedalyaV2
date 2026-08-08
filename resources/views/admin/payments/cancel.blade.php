@extends('layouts.admin')

@section('title', 'Payment Cancelled')

@section('page-header')
    <h1>Payment Cancelled</h1>
    <p>The payment process was cancelled or not completed</p>
@endsection

@section('content')
<div class="row justify-center">
    <div class="col-12 col-md-8 col-lg-6">
        <x-admin.card class="text-center py-5">
            <div class="kpi__icon mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2.5rem; background: var(--warning-soft); color: var(--warning);">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <h2 class="mb-2">Payment Cancelled</h2>
            <p class="text-muted mb-4">The payment process was cancelled or the session expired.</p>

            <div class="row g-3 mb-4 text-start">
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Payment Reference</div>
                        <div class="fw-semibold">{{ $payment->paymentReference }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Amount</div>
                        <div class="fw-bold text-warning">₱{{ number_format($payment->totalAmount, 2) }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Method</div>
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

            <div class="alert alert-warning">
                <i class="bi bi-info-circle me-2"></i>
                The bicycle remains available for rental. No rental was created.
            </div>

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