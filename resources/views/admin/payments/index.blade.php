@extends('layouts.admin')

@section('title', 'Payment Management')

@section('styles')
<style>
    .payment-status-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 999px;
        font-weight: 600;
    }
    .payment-method-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    .amount-cell { font-family: 'Inter', monospace; font-variant-numeric: tabular-nums; }
</style>
@endsection

@section('page-header')
    <h1>Payment Management</h1>
    <p>Monitor and manage all PayMongo payment transactions</p>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.payments.create') }}" class="btn-admin btn-admin--primary">
            <i class="bi bi-plus-circle me-1"></i>New Payment
        </a>
        <a href="{{ route('admin.payments.index') }}?status=paid" class="btn-admin btn-admin--secondary btn-admin--sm">
            <i class="bi bi-filter me-1"></i>Paid Only
        </a>
    </div>
@endsection

@section('content')
{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-admin.kpi title="Total Payments" value="{{ $stats['total'] }}" icon="bi-credit-card" color="var(--brand)" foot="all time" />
    </div>
    <div class="col-6 col-md-3">
        <x-admin.kpi title="Successful" value="{{ $stats['paid'] }}" icon="bi-check-circle" color="var(--success)" foot="completed" />
    </div>
    <div class="col-6 col-md-3">
        <x-admin.kpi title="Pending" value="{{ $stats['pending'] }}" icon="bi-clock" color="var(--warning)" foot="awaiting payment" />
    </div>
    <div class="col-6 col-md-3">
        <x-admin.kpi title="Failed" value="{{ $stats['failed'] }}" icon="bi-x-circle" color="var(--danger)" foot="failed/expired" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-admin.kpi title="Total Revenue" value="₱{{ number_format($stats['totalRevenue'], 0) }}" icon="bi-cash-stack" color="var(--success)" foot="lifetime" />
    </div>
    <div class="col-6 col-md-3">
        <x-admin.kpi title="Today's Revenue" value="₱{{ number_format($stats['todayRevenue'], 0) }}" icon="bi-calendar-check" color="var(--accent)" foot="today only" />
    </div>
    <div class="col-6 col-md-3">
        <x-admin.kpi title="Success Rate" value="{{ $stats['total'] > 0 ? round(($stats['paid'] / $stats['total']) * 100, 1) : 0 }}%" icon="bi-graph-up" color="var(--info)" foot="paid / total" />
    </div>
</div>

{{-- Filters --}}
<x-admin.card title="Payments" :flush="false">
    <x-slot:tools>
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end" style="max-width: 100%;">
            <div class="flex-grow-1" style="min-width: 200px;">
                <label class="form-label form-label-sm">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Reference, ID, customer..." value="{{ request('search') }}">
                </div>
            </div>
            <div style="min-width: 150px;">
                <label class="form-label form-label-sm">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['pending', 'processing', 'paid', 'failed', 'expired', 'cancelled', 'refunded'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 150px;">
                <label class="form-label form-label-sm">Method</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All Methods</option>
                    @foreach(['gcash', 'maya', 'grabpay', 'card', 'online_banking'] as $m)
                        <option value="{{ $m }}" {{ request('payment_method') === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 160px;">
                <label class="form-label form-label-sm">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div style="min-width: 160px;">
                <label class="form-label form-label-sm">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm">Filter</button>
            <a href="{{ route('admin.payments.index') }}" class="btn-admin btn-admin--secondary btn-admin--sm">Clear</a>
        </form>
    </x-slot:tools>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Bicycle</th>
                    <th>Method</th>
                    <th class="amount-cell">Amount</th>
                    <th>Status</th>
                    <th>Rental</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td class="cell-title">
                            <strong>{{ $payment->paymentReference }}</strong>
                            @if($payment->paymongoPaymentId)
                                <div class="cell-sub">{{ Str::limit($payment->paymongoPaymentId, 20) }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-sm">{{ strtoupper($payment->user->name[0]) }}</div>
                                <div>
                                    <div class="fw-semibold text-truncate" style="max-width: 180px;">{{ $payment->user->name }}</div>
                                    <div class="cell-sub">{{ $payment->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($payment->bicycle)
                                <div class="fw-semibold">{{ $payment->bicycle->name }}</div>
                                <div class="cell-sub">{{ $payment->bicycle->serialNumber }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $pmBg = match($payment->paymentMethod) {
                                    'gcash' => '#007AFF22', 'maya' => '#00B8E622', 'grabpay' => '#00AA1322',
                                    'card' => '#6366F122', 'online_banking' => '#0EA5E922', default => 'var(--surface-3)'
                                };
                                $pmColor = match($payment->paymentMethod) {
                                    'gcash' => '#007AFF', 'maya' => '#00B8E6', 'grabpay' => '#00AA13',
                                    'card' => '#6366F1', 'online_banking' => '#0EA5E9', default => 'var(--text-3)'
                                };
                                $pmIcon = match($payment->paymentMethod) {
                                    'gcash' => 'bi-phone', 'maya' => 'bi-phone', 'grabpay' => 'bi-bag',
                                    'card' => 'bi-credit-card', 'online_banking' => 'bi-bank',
                                    default => 'bi-currency-exchange'
                                };
                            @endphp
                            <span class="payment-method-icon" style="background: {{ $pmBg }}; color: {{ $pmColor }};">
                                <i class="bi {{ $pmIcon }}"></i>
                            </span>
                            <span class="ms-2 fw-medium">{{ $payment->getPaymentMethodLabel() }}</span>
                        </td>
                        <td class="amount-cell fw-semibold">₱{{ number_format($payment->totalAmount, 2) }}
                            @if($payment->convenienceFee > 0)
                                <div class="cell-sub">Fee: ₱{{ number_format($payment->convenienceFee, 2) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="payment-status-badge" style="background: var(--{{ $payment->getStatusColor() }}-soft); color: var(--{{ $payment->getStatusColor() }});">
                                {{ $payment->getStatusLabel() }}
                            </span>
                        </td>
                        <td>
                            @if($payment->rental)
                                <a href="{{ route('admin.rentals.show', $payment->rental) }}" class="text-decoration-none">
                                    <i class="bi bi-key me-1"></i>{{ $payment->rental->id }}
                                    <span class="badge-admin badge-admin--success badge-admin--plain ms-1">{{ ucfirst($payment->rental->status) }}</span>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="cell-sub">{{ $payment->created_at->format('M j, Y g:i A') }}
                            @if($payment->paidAt)
                                <div class="text-success">Paid: {{ $payment->paidAt->format('g:i A') }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="btn-admin btn-admin--ghost btn-admin--sm" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($payment->status === 'paid')
                                    <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn-admin btn-admin--ghost btn-admin--sm" title="View Receipt" target="_blank">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                @endif
                                @if($payment->status === 'pending' || $payment->status === 'processing')
                                    <a href="{{ route('admin.payments.verify', $payment) }}" class="btn-admin btn-admin--ghost btn-admin--sm" title="Verify with PayMongo">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"><x-admin.empty-state icon="bi-credit-card" title="No payments found" message="No payment transactions match your filters." /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-table-foot">
        <div>{{ $payments->total() }} payments</div>
        {{ $payments->links() }}
    </div>
</x-admin.card>
@endsection