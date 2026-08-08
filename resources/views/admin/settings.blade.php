@extends('layouts.admin')

@section('title', 'System Settings')

@section('page-header')
    <h1>System Settings</h1>
    <p>Configure system parameters</p>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm" class="admin-form">
    @csrf
    @method('PUT')

    <!-- Company Section -->
    <x-admin.card>
        <x-slot name="title"><i class="bi bi-building me-2"></i>Company</x-slot>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control" name="companyName"
                    value="{{ old('companyName', $settings->companyName) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Support Email</label>
                <input type="email" class="form-control" name="companyEmail"
                    value="{{ old('companyEmail', $settings->companyEmail) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Phone</label>
                <input type="text" class="form-control" name="companyPhone"
                    value="{{ old('companyPhone', $settings->companyPhone) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" name="companyAddress"
                    value="{{ old('companyAddress', $settings->companyAddress) }}">
            </div>
        </div>
    </x-admin.card>

    <!-- Pricing Section -->
    <x-admin.card>
        <x-slot name="title"><i class="bi bi-cash-stack me-2"></i>Pricing</x-slot>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Rental Rate Per Hour (₱)</label>
                <input type="number" class="form-control" name="rentalRatePerHour"
                    step="0.01" min="0" value="{{ old('rentalRatePerHour', $settings->rentalRatePerHour) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Max Rental Duration (hours)</label>
                <input type="number" class="form-control" name="rentalMaxDurationHours"
                    min="1" value="{{ old('rentalMaxDurationHours', $settings->rentalMaxDurationHours) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Deposit Amount (₱)</label>
                <input type="number" class="form-control" name="depositAmount"
                    step="0.01" min="0" value="{{ old('depositAmount', $settings->depositAmount) }}">
            </div>
        </div>
    </x-admin.card>

    <!-- Geofence Section -->
    <x-admin.card>
        <x-slot name="title"><i class="bi bi-geo-alt me-2"></i>Geofence — Riding Zone (Circular Geofence)</x-slot>
        <p class="text-muted mb-3" style="font-size:0.85rem;">
            This circular boundary defines the maximum distance bicycles are allowed to travel. Bicycles outside the boundary trigger a potential theft alert.
        </p>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Enabled</label>
                <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="geofenceEnabled"
                        name="geofenceEnabled" value="1"
                        {{ old('geofenceEnabled', $settings->geofenceEnabled ? '1' : '0') ? 'checked' : '' }}>
                    <label class="form-check-label" for="geofenceEnabled" style="font-size: 0.85rem;">Enable Geofence</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Center Latitude</label>
                <input type="number" class="form-control" name="geofenceCenterLat"
                    step="0.000001" min="-90" max="90" value="{{ old('geofenceCenterLat', $settings->geofenceCenterLat) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Center Longitude</label>
                <input type="number" class="form-control" name="geofenceCenterLng"
                    step="0.000001" min="-180" max="180" value="{{ old('geofenceCenterLng', $settings->geofenceCenterLng) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Radius (meters)</label>
                <input type="number" class="form-control" name="geofenceRadius"
                    min="10" max="50000" value="{{ old('geofenceRadius', $settings->geofenceRadius) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Warning Threshold (m)</label>
                <input type="number" class="form-control" name="geofenceWarningThreshold"
                    min="1" value="{{ old('geofenceWarningThreshold', $settings->geofenceWarningThreshold) }}">
                <small class="text-muted">Distance from boundary where warning triggers.</small>
            </div>
        </div>
        <div class="mt-3 p-3 rounded" style="background: var(--surface-2); border: 1px solid var(--border-subtle);">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Current center: <strong>{{ $geofence['centerLat'] }}, {{ $geofence['centerLng'] }}</strong> &mdash;
                Radius: <strong>{{ $geofence['radius'] }}m</strong>
            </small>
        </div>
    </x-admin.card>

    <!-- Operations Section -->
    <x-admin.card>
        <x-slot name="title"><i class="bi bi-gear me-2"></i>Operations</x-slot>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Device Timeout (minutes)</label>
                <input type="number" class="form-control" name="deviceTimeoutMinutes"
                    min="1" value="{{ old('deviceTimeoutMinutes', $settings->deviceTimeoutMinutes) }}">
                <small class="text-muted">Mark device inactive after no heartbeat.</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Low Battery Threshold (%)</label>
                <input type="number" class="form-control" name="lowBatteryThreshold"
                    min="0" max="100" value="{{ old('lowBatteryThreshold', $settings->lowBatteryThreshold) }}">
                <small class="text-muted">Bicycles below this level are flagged.</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Overdue Buzzer (minutes before expiry)</label>
                <input type="number" class="form-control" name="overdueBuzzerMinutes"
                    min="0" value="{{ old('overdueBuzzerMinutes', $settings->overdueBuzzerMinutes) }}">
                <small class="text-muted">Activate LCD/buzzer warning before rental expires.</small>
            </div>
        </div>
    </x-admin.card>

    <!-- Save Button -->
    <x-admin.card>
        <div class="d-flex justify-content-end gap-2">
            <button type="reset" class="btn-admin btn-admin--secondary">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </button>
            <button type="submit" class="btn-admin btn-admin--primary">
                <i class="bi bi-check-lg me-1"></i>Save Settings
            </button>
        </div>
    </x-admin.card>
</form>
@endsection

@section('scripts')
<script>
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to save these settings? Changes take effect immediately.')) {
            e.preventDefault();
        }
    });
</script>
@endsection