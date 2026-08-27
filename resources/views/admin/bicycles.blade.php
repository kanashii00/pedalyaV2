@extends('layouts.admin')

@section('title', 'Bicycle Management')

@section('page-header')
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Bicycles</h1>
            <p>Manage your bicycle fleet</p>
        </div>
    </div>
@endsection

@section('content')
{{-- Bicycles Table --}}
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <div class="grow">
            <i class="bi bi-search"></i>
            <input type="text" data-table-search placeholder="Search...">
        </div>
        <form method="GET" action="{{ route('admin.bicycles.index') }}" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="rented" {{ request('status') === 'rented' ? 'selected' : '' }}>Rented</option>
                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
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
                <th class="sortable">Serial <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Battery <span class="sort-ind"></span></th>
                <th class="sortable">Lock <span class="sort-ind"></span></th>
                <th class="sortable">Condition <span class="sort-ind"></span></th>
                <th class="sortable">Hourly Rate <span class="sort-ind"></span></th>
                <th class="sortable">Last Updated <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bicycles as $bike)
                @php
                    $isLocked = $bike->lockStatus === 'locked';
                    $isRented = $bike->status === 'rented';
                    $inMaintenance = $bike->status === 'maintenance';
                @endphp
                <tr>
                    <td class="cell-title" data-label="Name">{{ $bike->name }}</td>
                    <td data-label="Serial"><code>{{ $bike->serialNumber }}</code></td>
                    <td data-label="Status">
                        @if($bike->status === 'available')
                            <x-admin.badge type="success" label="Available" />
                        @elseif($bike->status === 'rented')
                            <x-admin.badge type="info" label="Rented" />
                        @elseif($bike->status === 'maintenance')
                            <x-admin.badge type="warning" label="Maintenance" />
                        @else
                            <x-admin.badge type="neutral" :label="ucfirst($bike->status)" />
                        @endif
                    </td>
                    <td data-label="Battery">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px; max-width:80px;">
                                <div class="progress-bar bg-{{ $bike->batteryLevel <= 20 ? 'danger' : ($bike->batteryLevel <= 50 ? 'warning' : 'success') }}"
                                     style="width:{{ $bike->batteryLevel }}%"></div>
                            </div>
                            <small>{{ $bike->batteryLevel }}%</small>
                        </div>
                    </td>
                    <td data-label="Lock">
                        @if($isLocked)
                            <x-admin.badge type="danger" label="Locked" />
                        @else
                            <x-admin.badge type="success" label="Unlocked" />
                        @endif
                    </td>
                    <td data-label="Condition">{{ ucfirst($bike->condition ?? 'good') }}</td>
                    <td data-label="Hourly Rate">₱{{ number_format($bike->hourlyRate, 2) }}/hr</td>
                    <td data-label="Last Updated"><small class="text-muted">{{ $bike->updated_at->diffForHumans() }}</small></td>
                    <td data-label="Actions">
                        <div class="d-flex gap-1">
                            @if($inMaintenance)
                                {{-- Under maintenance: all row actions disabled --}}
                                <button class="btn-admin btn-admin--secondary btn-admin--sm" type="button" disabled
                                        title="Disabled while bicycle is under maintenance"
                                        aria-label="Edit disabled, bicycle under maintenance">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-admin btn-admin--secondary btn-admin--sm" type="button" disabled
                                        title="Disabled while bicycle is under maintenance"
                                        aria-label="Lock control disabled, bicycle under maintenance">
                                    <i class="bi bi-{{ $isLocked ? 'lock' : 'unlock' }}-fill"></i>
                                    <span>{{ $isLocked ? 'Lock' : 'Unlock' }}</span>
                                </button>
                                <button class="btn-admin btn-admin--danger btn-admin--sm" type="button" disabled
                                        title="Disabled while bicycle is under maintenance"
                                        aria-label="Delete disabled, bicycle under maintenance">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @else
                                <button class="btn-admin btn-admin--secondary btn-admin--sm" type="button"
                                        onclick="PedalyaModal.open('editBicycleModal{{ $bike->id }}')" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                @if($isRented)
                                    {{-- Rented: lock is controlled by the active rider --}}
                                    <button class="btn-admin btn-admin--secondary btn-admin--sm" type="button" disabled
                                            title="Bicycle is currently rented - the smart lock is controlled by the rider"
                                            aria-label="Bicycle in use, lock control unavailable">
                                        <i class="bi bi-bicycle"></i>
                                        <span>In Use</span>
                                    </button>
                                @elseif(!$isLocked)
                                    <form action="{{ route('admin.bicycles.lock', $bike->id) }}" method="POST">
                                        @csrf
                                        {{-- Label and icon mirror the Lock badge; clicking toggles the state --}}
                                        <input type="hidden" name="action" value="lock">
                                        <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm"
                                                title="Unlocked - click to lock"
                                                aria-label="Unlocked bicycle, click to lock">
                                            <i class="bi bi-unlock-fill"></i>
                                            <span>Unlock</span>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.bicycles.lock', $bike->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="unlock">
                                        <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm"
                                                title="Locked - click to unlock"
                                                aria-label="Locked bicycle, click to unlock">
                                            <i class="bi bi-lock-fill"></i>
                                            <span>Lock</span>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.bicycles.destroy', $bike->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm" title="Delete"
                                            data-confirm="Are you sure you want to delete this bicycle?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- Edit Modal --}}
                <div class="admin-modal" id="editBicycleModal{{ $bike->id }}">
                    <div class="admin-modal__backdrop" data-modal-close></div>
                    <div class="admin-modal__dialog admin-modal__dialog--lg">
                        <form action="{{ route('admin.bicycles.update', $bike->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="admin-modal__head">
                                <h3>Edit Bicycle - {{ $bike->name }}</h3>
                                <button type="button" class="admin-icon-btn" data-modal-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <div class="admin-modal__body">
                                <div class="admin-form">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $bike->name }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Model</label>
                                            <input type="text" name="model" class="form-control" value="{{ $bike->model }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Serial Number</label>
                                            <input type="text" name="serialNumber" class="form-control" value="{{ $bike->serialNumber }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Hourly Rate (₱)</label>
                                            <input type="number" name="hourlyRate" class="form-control" value="{{ $bike->hourlyRate }}" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Latitude</label>
                                            <input type="number" name="currentLat" class="form-control" value="{{ $bike->currentLat }}" step="any">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Longitude</label>
                                            <input type="number" name="currentLng" class="form-control" value="{{ $bike->currentLng }}" step="any">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Battery Level (%)</label>
                                            <input type="number" name="batteryLevel" class="form-control" value="{{ $bike->batteryLevel }}" min="0" max="100">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="available" {{ $bike->status === 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="rented" {{ $bike->status === 'rented' ? 'selected' : '' }}>Rented</option>
                                                <option value="maintenance" {{ $bike->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3">{{ $bike->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="admin-modal__foot">
                                <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Cancel</button>
                                <button type="submit" class="btn-admin btn-admin--primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="9">
                        <x-admin.empty-state icon="bi-bicycle" title="No bicycles found" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="admin-table-foot">
        <span>Showing {{ method_exists($bicycles, 'total') ? $bicycles->total() : $bicycles->count() }} records</span>
        @if(method_exists($bicycles, 'links'))
            {{ $bicycles->withQueryString()->links() }}
        @endif
    </div>
</div>
@endsection