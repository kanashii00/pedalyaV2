@extends('layouts.admin')

@section('title', 'Add Bicycle')

@section('page-header')
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Add Bicycle</h1>
            <p>Register a new Beach Cruiser to the fleet</p>
        </div>
        <div class="admin-pagehead__actions">
            <a href="{{ route('admin.bicycles.index') }}" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Inventory
            </a>
        </div>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.bicycles.store') }}" class="admin-form">
    @csrf

    <div class="row g-4">

        {{-- ── System-Generated Information ──────────────── --}}
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="admin-card__head">
                    <i class="bi bi-shield-lock me-2" style="color:var(--info)"></i>
                    <span class="admin-card__title">System-Generated</span>
                    <span class="badge-admin badge-admin--brand ms-auto" style="font-size:10px">Auto</span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Bicycle Type</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <i class="bi bi-bicycle me-1" style="color:var(--brand)"></i>Beach Cruiser
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Serial Number</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <code class="vr-mono">{{ $nextSerial }}</code>
                            </div>
                            <small class="text-muted">Auto-generated, unique. Cannot be changed.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">QR Code</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <code class="vr-mono">{{ $nextQr }}</code>
                            </div>
                            <small class="text-muted">Auto-generated, unique. Cannot be changed.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── System Defaults ───────────────────────────── --}}
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="admin-card__head">
                    <i class="bi bi-gear me-2" style="color:var(--text-3)"></i>
                    <span class="admin-card__title">System Defaults</span>
                    <span class="badge-admin badge-admin--neutral ms-auto" style="font-size:10px">Defaults</span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Initial Status</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <x-admin.badge type="success" label="Available" />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Condition</label>
                            <div class="form-control-plaintext vr-readonly-field">Good</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Battery Level</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <span class="vr-battery-pill">100%</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Smart Lock</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <x-admin.badge type="danger" label="Locked" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Bicycle Details (Admin Input) ─────────────── --}}
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card__head">
                    <i class="bi bi-pencil-square me-2" style="color:var(--brand)"></i>
                    <span class="admin-card__title">Bicycle Details</span>
                    <span class="badge-admin badge-admin--warning ms-auto" style="font-size:10px">Required</span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bicycle Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Alpha, Bravo, Charlie..." required autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model / Variant</label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror"
                                   value="{{ old('model', 'Beach Cruiser') }}" placeholder="Beach Cruiser">
                            @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Defaults to Beach Cruiser if left unchanged.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hourly Rate (&#8369;) <span class="text-danger">*</span></label>
                            <input type="number" name="hourlyRate" class="form-control @error('hourlyRate') is-invalid @enderror"
                                   value="{{ old('hourlyRate') }}" step="0.01" min="0" placeholder="0.00" required>
                            @error('hourlyRate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                                   value="{{ old('description') }}" placeholder="Optional notes...">
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── GPS / Initial Location ────────────────────── --}}
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card__head">
                    <i class="bi bi-geo-alt me-2" style="color:var(--warning)"></i>
                    <span class="admin-card__title">GPS / Initial Location</span>
                    <small class="text-muted ms-auto">Optional</small>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Latitude</label>
                            <input type="number" name="currentLat" class="form-control @error('currentLat') is-invalid @enderror"
                                   value="{{ old('currentLat') }}" step="any" placeholder="e.g. 10.3157">
                            @error('currentLat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Longitude</label>
                            <input type="number" name="currentLng" class="form-control @error('currentLng') is-invalid @enderror"
                                   value="{{ old('currentLng') }}" step="any" placeholder="e.g. 123.8854">
                            @error('currentLng')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <small class="text-muted">Set the bicycle's starting GPS coordinates. Location updates automatically via device telemetry.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Form Actions ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Serial Number <strong>{{ $nextSerial }}</strong> and QR Code <strong>{{ $nextQr }}</strong> will be assigned on save.
        </small>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bicycles.index') }}" class="btn-admin btn-admin--secondary">
                <i class="bi bi-x-lg me-1"></i>Cancel
            </a>
            <button type="submit" class="btn-admin btn-admin--primary">
                <i class="bi bi-plus-lg me-1"></i>Add Bicycle
            </button>
        </div>
    </div>
</form>
@endsection
