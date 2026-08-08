@extends('layouts.admin')

@section('title', 'Rider Management')

@section('page-header')
    <h1>Riders</h1>
    <p>Manage registered riders and verifications</p>
@endsection

@section('content')
{{-- Riders Table --}}
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <form method="GET" action="{{ route('admin.riders.index') }}" class="d-flex gap-2 align-items-center">
            <div class="grow">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Name, email, or phone..." value="{{ request('search') }}">
            </div>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            <select name="verified" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Verified</option>
                <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Unverified</option>
            </select>
            <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">Name <span class="sort-ind"></span></th>
                <th class="sortable">Email <span class="sort-ind"></span></th>
                <th class="sortable">Phone <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Verified <span class="sort-ind"></span></th>
                <th class="sortable">Total Rentals <span class="sort-ind"></span></th>
                <th class="sortable">Total Spent <span class="sort-ind"></span></th>
                <th class="sortable">Joined <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riders as $rider)
                <tr>
                    <td class="cell-title" data-label="Name">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2" style="width:32px;height:32px;font-size:12px;">
                                {{ strtoupper(substr($rider->name, 0, 1)) }}
                            </div>
                            {{ $rider->name }}
                        </div>
                    </td>
                    <td data-label="Email"><small>{{ $rider->email }}</small></td>
                    <td data-label="Phone">{{ $rider->phoneNumber ?? '—' }}</td>
                    <td data-label="Status">
                        @if($rider->status === 'active')
                            <x-admin.badge type="success" label="Active" />
                        @elseif($rider->status === 'inactive')
                            <x-admin.badge type="neutral" label="Inactive" />
                        @elseif($rider->status === 'suspended')
                            <x-admin.badge type="danger" label="Suspended" />
                        @else
                            <x-admin.badge type="neutral" :label="ucfirst($rider->status ?? 'active')" />
                        @endif
                    </td>
                    <td data-label="Verified">
                        @if($rider->verified ?? false)
                            <x-admin.badge type="success" label="Verified" />
                        @else
                            <x-admin.badge type="warning" label="Pending" />
                        @endif
                    </td>
                    <td data-label="Total Rentals">{{ $rider->totalRentals ?? 0 }}</td>
                    <td data-label="Total Spent">₱{{ number_format($rider->totalSpent ?? 0, 2) }}</td>
                    <td data-label="Joined"><small class="text-muted">{{ $rider->created_at->format('M d, Y') }}</small></td>
                    <td data-label="Actions">
                        <div class="d-flex gap-1">
                            @if(!($rider->verified ?? false) && ($rider->idUploaded ?? false))
                                <form action="{{ route('admin.riders.verify', $rider->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="approved" value="1">
                                    <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm" title="Verify">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            @endif

                            @if(($rider->status ?? 'active') === 'active')
                                <form action="{{ route('admin.riders.status', $rider->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="inactive">
                                    <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm" title="Disable" data-confirm="Disable this rider?">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.riders.status', $rider->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm" title="Enable">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            @endif

                            @if(($rider->status ?? 'active') !== 'suspended')
                                <form action="{{ route('admin.riders.status', $rider->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="suspended">
                                    <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm" title="Suspend" data-confirm="Suspend this rider?">
                                        <i class="bi bi-slash-circle"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <x-admin.empty-state icon="bi-people" title="No riders found" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="admin-table-foot">
        <span>Showing {{ method_exists($riders, 'total') ? $riders->total() : $riders->count() }} records</span>
        @if(method_exists($riders, 'links'))
            {{ $riders->withQueryString()->links() }}
        @endif
    </div>
</div>
@endsection