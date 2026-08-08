@extends('layouts.admin')

@section('title', 'Rental Management')

@section('page-header')
<div class="admin-pagehead">
    <div class="admin-pagehead__title">
        <h1>Rentals</h1>
        <p>Track and manage all bicycle rentals</p>
    </div>
</div>
@endsection

@section('content')
<div class="admin-table-wrap">
    {{-- Toolbar: search + GET filters --}}
    <div class="admin-table-toolbar">
        <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search rentals..."></div>
        <form method="GET" action="{{ route('admin.rentals.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <select name="status" class="form-select" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" aria-label="From date">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" aria-label="To date">
            <select name="bicycle_id" class="form-select" aria-label="Filter by bicycle">
                <option value="">All Bicycles</option>
                @foreach($bicyclesList ?? [] as $b)
                    <option value="{{ $b->id }}" {{ request('bicycle_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
            <select name="rider_id" class="form-select" aria-label="Filter by rider">
                <option value="">All Riders</option>
                @foreach($ridersList ?? [] as $r)
                    <option value="{{ $r->id }}" {{ request('rider_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">Rental ID <span class="sort-ind"></span></th>
                <th class="sortable">Rider <span class="sort-ind"></span></th>
                <th class="sortable">Bicycle <span class="sort-ind"></span></th>
                <th class="sortable">Start <span class="sort-ind"></span></th>
                <th class="sortable">End <span class="sort-ind"></span></th>
                <th class="sortable">Duration <span class="sort-ind"></span></th>
                <th class="sortable">Fee <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Payment <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rentals as $rental)
                <tr>
                    <td data-label="Rental ID"><code>#{{ $rental->id }}</code></td>
                    <td data-label="Rider">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2" style="width:28px;height:28px;font-size:11px;">
                                {{ strtoupper(substr($rental->rider->name ?? 'U', 0, 1)) }}
                            </div>
                            {{ $rental->rider->name ?? 'Unknown' }}
                        </div>
                    </td>
                    <td data-label="Bicycle">{{ $rental->bicycle->name ?? 'Unknown' }}</td>
                    <td data-label="Start"><small>{{ $rental->startTime?->format('M d, Y h:i A') ?? '—' }}</small></td>
                    <td data-label="End"><small>{{ $rental->endTime?->format('M d, Y h:i A') ?? '—' }}</small></td>
                    <td data-label="Duration">{{ $rental->durationMinutes ? floor($rental->durationMinutes / 60) . 'h ' . ($rental->durationMinutes % 60) . 'm' : '—' }}</td>
                    <td data-label="Fee" class="fw-semibold">₱{{ number_format($rental->totalFee ?? 0, 2) }}</td>
                    <td data-label="Status">
                        @if($rental->status === 'active')
                            <x-admin.badge type="success" label="Active"/>
                        @elseif($rental->status === 'completed')
                            <x-admin.badge type="success" label="Completed"/>
                        @elseif($rental->status === 'pending')
                            <x-admin.badge type="warning" label="Pending"/>
                        @elseif($rental->status === 'overdue')
                            <x-admin.badge type="danger" label="Overdue"/>
                        @elseif($rental->status === 'cancelled')
                            <x-admin.badge type="neutral" label="Cancelled"/>
                        @else
                            <x-admin.badge :label="ucfirst($rental->status)"/>
                        @endif
                    </td>
                    <td data-label="Payment">
                        @if($rental->paymentStatus === 'paid')
                            <x-admin.badge type="success" label="Paid"/>
                        @elseif($rental->paymentStatus === 'pending')
                            <x-admin.badge type="warning" label="Pending"/>
                        @else
                            <x-admin.badge :label="ucfirst($rental->paymentStatus ?? 'unpaid')"/>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn-admin btn-admin--secondary btn-admin--sm"
                                    onclick="PedalyaModal.open('rentalDetailModal{{ $rental->id }}')" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($rental->status === 'pending')
                                <form action="{{ route('admin.rentals.approve', $rental->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            @endif
                            @if(in_array($rental->status, ['active', 'pending', 'overdue']))
                                <form action="{{ route('admin.rentals.cancel', $rental->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="reason" value="Cancelled by administrator">
                                    <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm"
                                            data-confirm="Are you sure you want to cancel this rental?" title="Cancel">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- Detail Modal --}}
                <div class="admin-modal" id="rentalDetailModal{{ $rental->id }}">
                    <div class="admin-modal__backdrop"></div>
                    <div class="admin-modal__dialog admin-modal__dialog--lg">
                        <div class="admin-modal__head">
                            <h3><i class="bi bi-key me-2"></i>Rental #{{ $rental->id }} Details</h3>
                            <button type="button" class="admin-icon-btn" data-modal-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="admin-modal__body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Rental Information</h6>
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted" style="width:140px;">Status</td>
                                                <td>
                                                    @if($rental->status === 'active')
                                                        <x-admin.badge type="success" label="Active"/>
                                                    @elseif($rental->status === 'completed')
                                                        <x-admin.badge type="success" label="Completed"/>
                                                    @elseif($rental->status === 'pending')
                                                        <x-admin.badge type="warning" label="Pending"/>
                                                    @elseif($rental->status === 'overdue')
                                                        <x-admin.badge type="danger" label="Overdue"/>
                                                    @else
                                                        <x-admin.badge :label="ucfirst($rental->status)"/>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Start Time</td>
                                                <td>{{ $rental->startTime?->format('M d, Y h:i A') ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">End Time</td>
                                                <td>{{ $rental->endTime?->format('M d, Y h:i A') ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Duration</td>
                                                <td>{{ $rental->durationMinutes ? floor($rental->durationMinutes / 60) . 'h ' . ($rental->durationMinutes % 60) . 'm' : '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Created</td>
                                                <td>{{ $rental->created_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Payment & Participants</h6>
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted" style="width:140px;">Rider</td>
                                                <td>{{ $rental->rider->name ?? 'Unknown' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Email</td>
                                                <td>{{ $rental->rider->email ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Bicycle</td>
                                                <td>{{ $rental->bicycle->name ?? 'Unknown' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Hourly Rate</td>
                                                <td>₱{{ number_format($rental->bicycle->hourlyRate ?? 0, 2) }}/hr</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Total Fee</td>
                                                <td class="fw-bold text-success">₱{{ number_format($rental->totalFee ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Payment</td>
                                                <td>
                                                    @if($rental->paymentStatus === 'paid')
                                                        <x-admin.badge type="success" label="Paid"/>
                                                    @else
                                                        <x-admin.badge type="warning" :label="ucfirst($rental->paymentStatus ?? 'Unpaid')"/>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                @if($rental->notes)
                                    <div class="col-12">
                                        <h6 class="text-muted mb-2">Notes</h6>
                                        <p class="mb-0">{{ $rental->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="admin-modal__foot">
                            <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Close</button>
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="10">
                        <x-admin.empty-state icon="bi-key" title="No rentals found" message="Adjust your filters or try a different search."/>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="admin-table-foot">
        <span>Showing {{ $rentals->total() }} records</span>
        @if(method_exists($rentals, 'links'))
            {{ $rentals->withQueryString()->links() }}
        @endif
    </div>
</div>
@endsection
