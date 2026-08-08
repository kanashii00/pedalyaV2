@extends('layouts.admin')

@section('title', 'Payment Details — {{ $payment->paymentReference }}')

@section('styles')
<style>
    .detail-row { display: flex; margin-bottom: 8px; }
    .detail-label { min-width: 180px; font-weight: 600; color: var(--text-2); font-size: 13px; }
    .detail-value { flex: 1; font-family: 'Inter', monospace; font-variant-numeric: tabular-nums; }
    .payment-method-badge { font-size: 0.75rem; padding: 4px 10px; border-radius: 999px; font-weight: 600; }
    .status-badge { font-size: 0.75rem; padding: 4px 10px; border-radius: 999px; font-weight: 600; }
    .json-view { max-height: 300px; overflow: auto; background: var(--surface-3); border-radius: 8px; padding: 12px; font-size: 12px; font-family: monospace; white-space: pre-wrap; }
</style>
@endsection

@section('page-header')
    <h1>Payment Details</h1>
    <p>{{ $payment->paymentReference }} · {{ $payment->getStatusLabel() }}</p>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        @if($payment->status === 'paid')
            <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn-admin btn-admin--primary" target="_blank">
                <i class="bi bi-receipt me-1"></i>View Receipt
            </a>
            <button class="btn-admin btn-admin--secondary" onclick="downloadReceipt()">
                <i class="bi bi-download me-1"></i>Download PDF
            </button>
        @endif
        @if($payment->status === 'pending' || $payment->status === 'processing')
            <a href="{{ route('admin.payments.verify', $payment) }}" class="btn-admin btn-admin--secondary">
                <i class="bi bi-arrow-clockwise me-1"></i>Verify Status
            </a>
        @endif
        @if($payment->status === 'paid' && !$payment->refund)
            <a href="{{ route('admin.refunds.create', $payment) }}" class="btn-admin btn-admin--warning">
                <i class="bi bi-arrow-return-left me-1"></i>Refund
            </a>
        @endif
        <a href="{{ route('admin.payments.index') }}" class="btn-admin btn-admin--ghost">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
@endsection

@section('content')
<div class="row g-4">
    {{-- Overview --}}
    <div class="col-12 col-xl-4">
        <x-admin.card title="Payment Overview">
            <div class="detail-row">
                <span class="detail-label">Reference</span>
                <span class="detail-value fw-semibold">{{ $payment->paymentReference }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">PayMongo ID</span>
                <span class="detail-value text-truncate" style="max-width: 200px;">{{ $payment->paymongoPaymentId ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <span class="status-badge" style="background: var(--{{ $payment->getStatusColor() }}-soft); color: var(--{{ $payment->getStatusColor() }});">
                        {{ $payment->getStatusLabel() }}
                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value">
                    <span class="payment-method-badge" style="background: {{ match($payment->paymentMethod) {
                        'gcash' => '#007AFF22', 'maya' => '#00B8E622', 'grabpay' => '#00AA1322',
                        'card' => '#6366F122', 'online_banking' => '#0EA5E922', default => 'var(--surface-3)'
                    }}; color: {{ match($payment->paymentMethod) {
                        'gcash' => '#007AFF', 'maya' => '#00B8E6', 'grabpay' => '#00AA13',
                        'card' => '#6366F1', 'online_banking' => '#0EA5E9', default => 'var(--text-3)'
                    }}; }}">
                        {{ $payment->getPaymentMethodLabel() }}
                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Base Amount</span>
                <span class="detail-value text-success fw-semibold">₱{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Convenience Fee</span>
                <span class="detail-value text-warning">₱{{ number_format($payment->convenienceFee, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value fw-bold" style="font-size: 1.1rem;">₱{{ number_format($payment->totalAmount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Currency</span>
                <span class="detail-value">{{ $payment->currency }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value">{{ $payment->created_at->format('M j, Y g:i A') }}</span>
            </div>
            @if($payment->paidAt)
                <div class="detail-row">
                    <span class="detail-label">Paid At</span>
                    <span class="detail-value text-success">{{ $payment->paidAt->format('M j, Y g:i A') }}</span>
                </div>
            @endif
            @if($payment->expiredAt)
                <div class="detail-row">
                    <span class="detail-label">Expired At</span>
                    <span class="detail-value text-danger">{{ $payment->expiredAt->format('M j, Y g:i A') }}</span>
                </div>
            @endif
        </x-admin.card>

        @if($payment->billingInfo)
            <x-admin.card title="Billing Information" class="mt-4">
                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">{{ $payment->billingInfo['name'] ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $payment->billingInfo['email'] ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ $payment->billingInfo['phone'] ?? '—' }}</span>
                </div>
            </x-admin.card>
        @endif
    </div>

    <div class="col-12 col-xl-8">
        {{-- Customer & Bicycle --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <x-admin.card title="Customer">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="user-avatar-lg bg-success">{{ strtoupper($payment->user->name[0]) }}</div>
                        <div>
                            <h5 class="mb-1">{{ $payment->user->name }}</h5>
                            <p class="text-muted mb-0">{{ $payment->user->email }}</p>
                        </div>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Verified</span>
                        <span class="detail-value">
                            @if($payment->user->verified)
                                <span class="badge-admin badge-admin--success">Verified</span>
                            @else
                                <span class="badge-admin badge-admin--warning">Pending</span>
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">{{ $payment->user->phone ?? '—' }}</span>
                    </div>
                    <a href="{{ route('admin.riders.show', $payment->user) }}" class="btn-admin btn-admin--ghost btn-admin--sm mt-2">
                        <i class="bi bi-person me-1"></i>View Profile
                    </a>
                </x-admin.card>
            </div>

            <div class="col-12 col-md-6">
                <x-admin.card title="Bicycle">
                    @if($payment->bicycle)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="kpi__icon bg-primary"><i class="bi bi-bicycle"></i></div>
                            <div>
                                <h5 class="mb-1">{{ $payment->bicycle->name }}</h5>
                                <p class="text-muted mb-0">{{ $payment->bicycle->serialNumber }}</p>
                            </div>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="badge-admin badge-admin--{{ match($payment->bicycle->status) {
                                    'available' => 'success', 'rented' => 'primary',
                                    'maintenance' => 'warning', 'locked' => 'danger',
                                    default => 'secondary'
                                }} }}">{{ ucfirst($payment->bicycle->status) }}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Battery</span>
                            <span class="detail-value">{{ $payment->bicycle->batteryLevel }}%</span>
                        </div>
                        <a href="{{ route('admin.bicycles.show', $payment->bicycle) }}" class="btn-admin btn-admin--ghost btn-admin--sm mt-2">
                            <i class="bi bi-bicycle me-1"></i>View Bicycle
                        </a>
                    @else
                        <p class="text-muted">No bicycle assigned</p>
                    @endif
                </x-admin.card>
            </div>
        </div>

        {{-- Rental --}}
        @if($payment->rental)
            <x-admin.card title="Rental Information" class="mb-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="detail-row">
                            <span class="detail-label">Rental ID</span>
                            <span class="detail-value"><a href="{{ route('admin.rentals.show', $payment->rental) }}">{{ $payment->rental->id }}</a></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="badge-admin badge-admin--{{ match($payment->rental->status) {
                                    'active' => 'success', 'pending' => 'warning',
                                    'completed' => 'info', 'cancelled' => 'danger', default => 'secondary'
                                }} }}">{{ ucfirst($payment->rental->status) }}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Started</span>
                            <span class="detail-value">{{ $payment->rental->startedAt?->format('M j, Y g:i A') ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-row">
                            <span class="detail-label">Ends</span>
                            <span class="detail-value">{{ $payment->rental->endsAt?->format('M j, Y g:i A') ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Fee</span>
                            <span class="detail-value fw-semibold text-success">₱{{ number_format($payment->rental->totalFee, 2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration</span>
                            <span class="detail-value">{{ $payment->rental->startedAt && $payment->rental->endsAt ? $payment->rental->startedAt->diffInHours($payment->rental->endsAt) : '—' }} hours</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.rentals.show', $payment->rental) }}" class="btn-admin btn-admin--secondary btn-admin--sm">
                    <i class="bi bi-key me-1"></i>View Rental Details
                </a>
            </x-admin.card>
        @else
            <x-admin.card class="mb-4">
                <p class="text-muted mb-0">No rental created yet. Payment must be completed first.</p>
            </x-admin.card>
        @endif

        {{-- PayMongo Response --}}
        @if($payment->paymentDetails)
            <x-admin.card title="PayMongo Response" class="mb-4">
                <div class="json-view">{{ json_encode($payment->paymentDetails, JSON_PRETTY_PRINT) }}</div>
            </x-admin.card>
        @endif

        {{-- Refund --}}
        @if($payment->refund)
            <x-admin.card title="Refund Information">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="detail-row">
                            <span class="detail-label">Refund Reference</span>
                            <span class="detail-value">{{ $payment->refund->refundReference }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Amount</span>
                            <span class="detail-value text-warning">₱{{ number_format($payment->refund->amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="badge-admin badge-admin--{{ $payment->refund->getStatusColor() }}">
                                    {{ $payment->refund->getStatusLabel() }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reason</span>
                            <span class="detail-value">{{ $payment->refund->getReasonLabel() }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.refunds.show', $payment->refund) }}" class="btn-admin btn-admin--ghost btn-admin--sm mt-2">
                    <i class="bi bi-arrow-return-left me-1"></i>View Refund Details
                </a>
            </x-admin.card>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function downloadReceipt() {
        window.open('{{ route('admin.payments.receipt', $payment) }}?download=1', '_blank');
    }
</script>
@endsection