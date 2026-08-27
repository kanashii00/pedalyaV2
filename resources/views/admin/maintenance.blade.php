@extends('layouts.admin')

@section('title', 'Maintenance Records')

@section('page-header')
    <h1>Maintenance Records</h1>
    <p>Track and manage bicycle maintenance</p>
@endsection

@section('actions')
    <button class="btn-admin btn-admin--primary" onclick="PedalyaModal.open('addMaintenanceModal')">
        <i class="bi bi-plus-lg me-1"></i>Add Maintenance
    </button>
@endsection

@section('content')
{{-- Active Maintenance Schedule --}}
@if(!$showHistory)
<div class="admin-table-wrap mb-4">
    <div class="admin-table-toolbar">
        <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search active maintenance..."></div>
        <form method="GET" action="{{ route('admin.maintenance.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <select class="form-select form-select-sm" name="bicycleId" style="flex:0 0 auto;max-width:190px;">
                <option value="">All Bicycles</option>
                @foreach($bicycles ?? [] as $bicycle)
                    <option value="{{ $bicycle->id }}" {{ request('bicycleId') == $bicycle->id ? 'selected' : '' }}>
                        {{ $bicycle->name }} ({{ $bicycle->id }})
                    </option>
                @endforeach
            </select>
            <select class="form-select form-select-sm" name="status" style="flex:0 0 auto;max-width:150px;">
                <option value="">All Active</option>
                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
            </select>
            <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <a href="{{ route('admin.maintenance.index') }}" class="btn-admin btn-admin--ghost btn-admin--sm">
                <i class="bi bi-x-lg me-1"></i>Clear
            </a>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">Bicycle <span class="sort-ind"></span></th>
                <th class="sortable">Type <span class="sort-ind"></span></th>
                <th class="sortable">Severity <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Scheduled <span class="sort-ind"></span></th>
                <th class="sortable">Technician <span class="sort-ind"></span></th>
                <th class="sortable">Est. Cost <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activeMaintenance as $m)
            <tr>
                <td data-label="Bicycle" class="cell-title">
                    <a href="{{ route('admin.bicycles.show', $m->bicycleId) }}" style="text-decoration:none;">
                        {{ $m->bicycle->name ?? $m->bicycleId }}
                    </a>
                </td>
                <td data-label="Type">{{ ucfirst($m->type) }}</td>
                <td data-label="Severity">
                    @switch($m->severity)
                        @case('low')
                            <x-admin.badge type="info" label="Low"/>
                            @break
                        @case('medium')
                            <x-admin.badge type="warning" label="Medium"/>
                            @break
                        @case('high')
                            <x-admin.badge type="danger" label="High"/>
                            @break
                        @case('critical')
                            <x-admin.badge type="danger" label="Critical"/>
                            @break
                        @default
                            <x-admin.badge :label="ucfirst($m->severity)"/>
                    @endswitch
                </td>
                <td data-label="Status">
                    @switch($m->status)
                        @case('scheduled')
                            <x-admin.badge type="warning" label="Scheduled"/>
                            @break
                        @case('in_progress')
                            <x-admin.badge type="info" label="In Progress"/>
                            @break
                        @default
                            <x-admin.badge :label="ucfirst($m->status)"/>
                    @endswitch
                </td>
                <td data-label="Scheduled">{{ $m->scheduledDate ? \Carbon\Carbon::parse($m->scheduledDate)->format('M d, Y') : '—' }}</td>
                <td data-label="Technician">{{ $m->technician ?? '—' }}</td>
                <td data-label="Est. Cost" class="fw-semibold">{{ $m->estimatedCost !== null ? '₱' . number_format($m->estimatedCost, 2) : '—' }}</td>
                <td data-label="Actions">
                    <div class="dropdown">
                        <button class="btn-admin btn-admin--soft btn-admin--sm dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Status
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="{{ route('admin.maintenance.updateStatus', $m->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="scheduled">
                                    <button class="dropdown-item" type="submit" {{ $m->status === 'scheduled' ? 'disabled' : '' }}>
                                        <i class="bi bi-clock text-warning me-2"></i>Scheduled
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('admin.maintenance.updateStatus', $m->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="in_progress">
                                    <button class="dropdown-item" type="submit" {{ $m->status === 'in_progress' ? 'disabled' : '' }}>
                                        <i class="bi bi-gear text-primary me-2"></i>In Progress
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('admin.maintenance.updateStatus', $m->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button class="dropdown-item" type="submit">
                                        <i class="bi bi-check-circle text-success me-2"></i>Completed
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('admin.maintenance.updateStatus', $m->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="dropdown-item" type="submit">
                                        <i class="bi bi-x-circle text-secondary me-2"></i>Cancelled
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">
                    <x-admin.empty-state icon="bi-tools" title="No active maintenance" message="All bicycles are cleared for service." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(method_exists($activeMaintenance, 'links'))
    <div class="admin-table-foot">
        <span>Showing {{ $activeMaintenance->total() }} active records</span>
        {{ $activeMaintenance->withQueryString()->links() }}
    </div>
    @endif
</div>
@endif

{{-- Maintenance History --}}
@if($showHistory || $maintenanceHistory->count() > 0)
<div class="admin-table-wrap">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--text-2);">
            <i class="bi bi-clock-history me-2"></i>Maintenance History
        </h5>
        @if(!$showHistory)
            <a href="{{ route('admin.maintenance.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
               class="btn-admin btn-admin--ghost btn-admin--sm">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        @endif
    </div>

    @if($showHistory)
    <div class="admin-table-toolbar">
        <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search history..."></div>
        <form method="GET" action="{{ route('admin.maintenance.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <select class="form-select form-select-sm" name="bicycleId" style="flex:0 0 auto;max-width:190px;">
                <option value="">All Bicycles</option>
                @foreach($bicycles ?? [] as $bicycle)
                    <option value="{{ $bicycle->id }}" {{ request('bicycleId') == $bicycle->id ? 'selected' : '' }}>
                        {{ $bicycle->name }} ({{ $bicycle->id }})
                    </option>
                @endforeach
            </select>
            <select class="form-select form-select-sm" name="status" style="flex:0 0 auto;max-width:150px;">
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <a href="{{ route('admin.maintenance.index') }}" class="btn-admin btn-admin--ghost btn-admin--sm">
                <i class="bi bi-x-lg me-1"></i>Clear
            </a>
        </form>
    </div>
    @endif

    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">Bicycle <span class="sort-ind"></span></th>
                <th class="sortable">Type <span class="sort-ind"></span></th>
                <th class="sortable">Severity <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Scheduled <span class="sort-ind"></span></th>
                <th class="sortable">Completed <span class="sort-ind"></span></th>
                <th class="sortable">Technician <span class="sort-ind"></span></th>
                <th class="sortable">Est. Cost <span class="sort-ind"></span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($maintenanceHistory as $m)
            <tr>
                <td data-label="Bicycle" class="cell-title">
                    <a href="{{ route('admin.bicycles.show', $m->bicycleId) }}" style="text-decoration:none;">
                        {{ $m->bicycle->name ?? $m->bicycleId }}
                    </a>
                </td>
                <td data-label="Type">{{ ucfirst($m->type) }}</td>
                <td data-label="Severity">
                    @switch($m->severity)
                        @case('low')
                            <x-admin.badge type="info" label="Low"/>
                            @break
                        @case('medium')
                            <x-admin.badge type="warning" label="Medium"/>
                            @break
                        @case('high')
                            <x-admin.badge type="danger" label="High"/>
                            @break
                        @case('critical')
                            <x-admin.badge type="danger" label="Critical"/>
                            @break
                        @default
                            <x-admin.badge :label="ucfirst($m->severity)"/>
                    @endswitch
                </td>
                <td data-label="Status">
                    @switch($m->status)
                        @case('completed')
                            <x-admin.badge type="success" label="Completed"/>
                            @break
                        @case('cancelled')
                            <x-admin.badge type="neutral" label="Cancelled"/>
                            @break
                        @default
                            <x-admin.badge :label="ucfirst($m->status)"/>
                    @endswitch
                </td>
                <td data-label="Scheduled">{{ $m->scheduledDate ? \Carbon\Carbon::parse($m->scheduledDate)->format('M d, Y') : '—' }}</td>
                <td data-label="Completed">{{ $m->completedDate ? \Carbon\Carbon::parse($m->completedDate)->format('M d, Y') : '—' }}</td>
                <td data-label="Technician">{{ $m->technician ?? '—' }}</td>
                <td data-label="Est. Cost" class="fw-semibold">{{ $m->estimatedCost !== null ? '₱' . number_format($m->estimatedCost, 2) : '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">
                    <x-admin.empty-state icon="bi-clock-history" title="No history yet" message="Completed and cancelled records will appear here." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(method_exists($maintenanceHistory, 'links'))
    <div class="admin-table-foot">
        <span>{{ $maintenanceHistory->total() }} history records</span>
        {{ $maintenanceHistory->withQueryString()->links() }}
    </div>
    @endif
</div>
@endif

<!-- Add Maintenance Modal -->
<div class="admin-modal" id="addMaintenanceModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <form action="{{ route('admin.maintenance.store') }}" method="POST">
            @csrf
            <div class="admin-modal__head">
                <h3><i class="bi bi-tools me-2"></i>Add Maintenance Record</h3>
                <button type="button" class="admin-icon-btn" data-modal-close><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="admin-modal__body">
                <div class="admin-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bicycle <span class="text-danger">*</span></label>
                            <select class="form-select @error('bicycleId') is-invalid @enderror"
                                name="bicycleId" required>
                                <option value="">Select Bicycle</option>
                                @foreach($bicycles ?? [] as $bicycle)
                                    <option value="{{ $bicycle->id }}" {{ old('bicycleId') == $bicycle->id ? 'selected' : '' }}>
                                        {{ $bicycle->name }} ({{ $bicycle->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('bicycleId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror"
                                name="type" required>
                                <option value="">Select Type</option>
                                <option value="routine" {{ old('type') == 'routine' ? 'selected' : '' }}>Routine</option>
                                <option value="repair" {{ old('type') == 'repair' ? 'selected' : '' }}>Repair</option>
                                <option value="battery" {{ old('type') == 'battery' ? 'selected' : '' }}>Battery Service</option>
                                <option value="lock_mechanism" {{ old('type') == 'lock_mechanism' ? 'selected' : '' }}>Lock Mechanism</option>
                                <option value="gps_module" {{ old('type') == 'gps_module' ? 'selected' : '' }}>GPS Module</option>
                                <option value="frame" {{ old('type') == 'frame' ? 'selected' : '' }}>Frame</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                name="description" rows="3" required
                                placeholder="Describe the maintenance work needed...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Severity <span class="text-danger">*</span></label>
                            <select class="form-select @error('severity') is-invalid @enderror"
                                name="severity" required>
                                <option value="">Select Severity</option>
                                <option value="low" {{ old('severity') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('severity') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('severity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimated Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control @error('estimatedCost') is-invalid @enderror"
                                    name="estimatedCost" step="0.01" min="0"
                                    value="{{ old('estimatedCost') }}" placeholder="0.00">
                                @error('estimatedCost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Technician</label>
                            <input type="text" class="form-control @error('technician') is-invalid @enderror"
                                name="technician" value="{{ old('technician') }}"
                                placeholder="Technician name">
                            @error('technician')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Scheduled Date</label>
                            <input type="date" class="form-control @error('scheduledDate') is-invalid @enderror"
                                name="scheduledDate" value="{{ old('scheduledDate') }}">
                            @error('scheduledDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                name="notes" rows="3"
                                placeholder="Additional notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin-modal__foot">
                <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-admin btn-admin--primary">
                    <i class="bi bi-check-lg me-1"></i>Save Record
                </button>
            </div>
        </form>
    </div>
</div>
@endsection