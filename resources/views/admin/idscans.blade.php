@extends('layouts.admin')

@section('title', 'ID Scanner')

@section('page-header')
    <h1>ID Scanner</h1>
    <p>Automated ID scanning and verification records</p>
@endsection

@section('actions')
<a href="{{ route('admin.id-scans.create') }}" class="btn-admin btn-admin--primary">
    <i class="bi bi-camera-video me-1"></i>Scan New ID
</a>
@endsection

@section('content')
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search this list..."></div>
        <form method="GET" action="{{ route('admin.id-scans.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, ID number, or linked rider..." value="{{ request('search') }}" style="width:230px;">
            <select name="status" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Needs Review</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <select name="documentType" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Types</option>
                @foreach([
                    'national_id' => 'National ID (PhilSys)',
                    'drivers_license' => "Driver's License",
                    'passport' => 'Passport',
                    'umid' => 'UMID',
                    'philhealth_id' => 'PhilHealth ID',
                    'student_id' => 'Student ID',
                    'voters_id' => "Voter's ID",
                    'other' => 'Other',
                ] as $value => $label)
                    <option value="{{ $value }}" {{ request('documentType') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="sortable">ID <span class="sort-ind"></span></th>
                    <th class="sortable">Document <span class="sort-ind"></span></th>
                    <th class="sortable">Full Name <span class="sort-ind"></span></th>
                    <th class="sortable">ID Number <span class="sort-ind"></span></th>
                    <th class="sortable">Linked Rider <span class="sort-ind"></span></th>
                    <th class="sortable">Confidence <span class="sort-ind"></span></th>
                    <th class="sortable">Status <span class="sort-ind"></span></th>
                    <th class="sortable">Scanned At <span class="sort-ind"></span></th>
                    <th>Actions <span class="sort-ind"></span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($scans as $scan)
                    <tr>
                        <td data-label="ID">#{{ $scan->id }}</td>
                        <td data-label="Document"><x-admin.badge type="neutral" label="{{ $scan->documentTypeLabel }}"/></td>
                        <td data-label="Full Name" class="cell-title">{{ $scan->fullName ?? '—' }}</td>
                        <td data-label="ID Number"><small class="text-muted">{{ $scan->idNumber ? '••••' . substr(preg_replace('/[^A-Z0-9]/', '', $scan->idNumber), -4) : '—' }}</small></td>
                        <td data-label="Linked Rider">
                            @if($scan->user)
                                <a href="{{ route('admin.riders.index', ['search' => $scan->user->email]) }}" class="text-decoration-none">
                                    <i class="bi bi-person-fill me-1"></i>{{ $scan->user->name }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Confidence">
                            @if($scan->ocrConfidence !== null)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;width:70px;">
                                        <div class="progress-bar {{ $scan->ocrConfidence >= 70 ? 'bg-success' : ($scan->ocrConfidence >= 40 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width: {{ $scan->ocrConfidence }}%"></div>
                                    </div>
                                    <small>{{ number_format($scan->ocrConfidence, 0) }}%</small>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            @switch($scan->status)
                                @case('approved')<x-admin.badge type="success" label="Approved"/>@break
                                @case('rejected')<x-admin.badge type="danger" label="Rejected"/>@break
                                @case('review')<x-admin.badge type="warning" label="Needs Review"/>@break
                                @default<x-admin.badge type="warning" label="Pending"/>@break
                            @endswitch
                        </td>
                        <td data-label="Scanned At"><small class="text-muted">{{ $scan->created_at->format('M d, Y g:i A') }}</small></td>
                        <td data-label="Actions">
                            <a href="{{ route('admin.id-scans.show', $scan->id) }}" class="btn-admin btn-admin--soft btn-admin--sm">
                                <i class="bi bi-eye me-1"></i>Review
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <x-admin.empty-state icon="bi-person-badge" title="No ID scans found" message="Scan your first ID to start verifying customer documents.">
                                <a href="{{ route('admin.id-scans.create') }}" class="btn-admin btn-admin--secondary btn-admin--sm mt-2">
                                    <i class="bi bi-camera-video me-1"></i>Scan Your First ID
                                </a>
                            </x-admin.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($scans, 'links'))
        <div class="admin-table-foot">
            <span>Showing {{ $scans->total() }} records</span>
            {{ $scans->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
