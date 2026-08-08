@extends('layouts.admin')

@section('title', 'ID Scan Review')

@section('page-header')
    <h1>ID Scan Review</h1>
    <p>Scan record #{{ $scan->id }} • {{ $scan->created_at->format('M d, Y g:i A') }}</p>
@endsection

@section('actions')
<a href="{{ route('admin.id-scans.index') }}" class="btn-admin btn-admin--secondary">
    <i class="bi bi-arrow-left me-1"></i>Back to Records
</a>
@endsection

@section('content')
@if(session('warning'))
    <div class="alert alert-pedalya alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Left: ID images --}}
    <div class="col-lg-5">
        <x-admin.card>
            <x-slot name="title"><i class="bi bi-image me-2"></i>Captured Document</x-slot>
            <div class="row g-2">
                @if($scan->frontImagePath)
                    <div class="col-12">
                        <small class="text-muted text-uppercase fw-semibold">Front</small>
                        <img src="{{ route('admin.id-scans.image', [$scan->id, 'front']) }}" alt="Front of ID"
                             class="img-fluid rounded border" style="max-height:260px;width:100%;object-fit:contain;background:#111;">
                    </div>
                @endif
                @if($scan->backImagePath)
                    <div class="col-12">
                        <small class="text-muted text-uppercase fw-semibold">Back</small>
                        <img src="{{ route('admin.id-scans.image', [$scan->id, 'back']) }}" alt="Back of ID"
                             class="img-fluid rounded border" style="max-height:260px;width:100%;object-fit:contain;background:#111;">
                    </div>
                @else
                    <div class="col-12 text-muted">
                        <i class="bi bi-info-circle me-1"></i>No back image captured.
                    </div>
                @endif
            </div>
        </x-admin.card>

        <x-admin.card>
            <x-slot name="title"><i class="bi bi-sliders me-2"></i>Quality &amp; Confidence</x-slot>
            <div class="d-flex justify-content-between mb-2">
                <small class="text-muted">OCR Confidence</small>
                <strong>{{ $scan->ocrConfidence !== null ? number_format($scan->ocrConfidence, 1) . '%' : '—' }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <small class="text-muted">Overall Quality</small>
                <strong>{{ $scan->qualityScore !== null ? number_format($scan->qualityScore, 1) . '%' : '—' }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <small class="text-muted">Blur</small>
                <strong>{{ $scan->blurScore !== null ? number_format($scan->blurScore, 1) . '%' : '—' }}</strong>
            </div>
            <div class="d-flex justify-content-between">
                <small class="text-muted">Glare</small>
                <strong>{{ $scan->glareScore !== null ? number_format($scan->glareScore, 1) . '%' : '—' }}</strong>
            </div>

            <hr>

            <small class="text-muted">Raw OCR Text</small>
            <pre style="font-size:0.75rem;background:var(--surface-2);border:1px solid var(--border-subtle);padding:10px;border-radius:var(--radius-sm);max-height:160px;overflow:auto;white-space:pre-wrap;" class="mb-0">{{ $scan->rawOcrText ?? 'No OCR text captured.' }}</pre>
        </x-admin.card>
    </div>

    {{-- Right: details & review --}}
    <div class="col-lg-7">
        <x-admin.card>
            <x-slot name="title"><i class="bi bi-person-vcard me-2"></i>Extracted Information</x-slot>
            <x-slot name="tools">
                @switch($scan->status)
                    @case('approved')<x-admin.badge type="success" label="Approved"/>@break
                    @case('rejected')<x-admin.badge type="danger" label="Rejected"/>@break
                    @case('review')<x-admin.badge type="warning" label="Needs Review"/>@break
                    @default<x-admin.badge type="warning" label="Pending"/>@break
                @endswitch
            </x-slot>
            <form method="POST" action="{{ route('admin.id-scans.review', $scan->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Document Type</label>
                        <select name="documentType" class="form-select" disabled>
                            @foreach([
                                'national_id' => 'National ID (PhilSys)',
                                'drivers_license' => "Driver's License",
                                'passport' => 'Passport',
                                'umid' => 'UMID',
                                'philhealth_id' => 'PhilHealth ID',
                                'student_id' => 'Student ID',
                                'voters_id' => "Voter's ID",
                                'other' => 'Other Government ID',
                            ] as $value => $label)
                                <option value="{{ $value }}" {{ $scan->documentType === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Current Status</label>
                        <input type="text" class="form-control" value="{{ ucfirst($scan->status) }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="fullName" class="form-control" value="{{ $scan->fullName }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ID Number</label>
                        <input type="text" name="idNumber" class="form-control" value="{{ $scan->idNumber }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="text" name="dateOfBirth" class="form-control" value="{{ $scan->dateOfBirth }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiration Date</label>
                        <input type="text" name="expirationDate" class="form-control" value="{{ $scan->expirationDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Link to Renter</label>
                        <select name="userId" class="form-select">
                            <option value="">— Not linked —</option>
                            @foreach($scan->user ? collect([$scan->user]) : collect() as $linked)
                                <option value="{{ $linked->id }}" selected>{{ $linked->name }} ({{ $linked->email }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Optionally link this ID to a renter account.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ $scan->address }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Review Notes</label>
                        <textarea name="reviewNotes" rows="2" class="form-control" placeholder="Notes for the record (optional)">{{ $scan->reviewNotes }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rejectToggle">
                            <label class="form-check-label" for="rejectToggle">Reject this scan — show rejection reason</label>
                        </div>
                    </div>
                    <div class="col-12" id="rejectionBox" style="display:none;">
                        <label class="form-label">Rejection Reason</label>
                        <select name="rejectionReason" class="form-select">
                            <option value="">— Select a reason —</option>
                            <option value="illegible">Document is illegible or blurry</option>
                            <option value="expired">Document is expired</option>
                            <option value="forged">Document appears to be forged or tampered</option>
                            <option value="mismatch">Details do not match the renter</option>
                            <option value="duplicate">Duplicate ID already on file</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <input type="hidden" name="editFields" value="1">
                        <input type="hidden" name="status" id="reviewStatusInput">
                        <div class="d-flex gap-2">
                            <button type="submit" name="status" value="approved" class="btn-admin btn-admin--primary"
                                    data-confirm="Approve this ID scan?" data-confirm-title="Approve ID scan" data-confirm-ok="Approve" data-confirm-danger="false">
                                <i class="bi bi-check-lg me-1"></i>Approve &amp; Verify
                            </button>
                            <button type="submit" name="status" value="rejected" class="btn-admin btn-admin--secondary text-danger"
                                    data-confirm="Reject this ID scan?" data-confirm-title="Reject ID scan" data-confirm-ok="Reject">
                                <i class="bi bi-x-lg me-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </x-admin.card>

        <x-admin.card>
            <x-slot name="title"><i class="bi bi-clipboard-check me-2"></i>Duplicate &amp; Return Check</x-slot>
            @php
                $duplicate = $scan->idNumber
                    ? app(\App\Services\IdScanService::class)->findDuplicate($scan->idNumber, ['pending', 'review', 'approved'])
                    : null;
            @endphp
            @if($duplicate && $duplicate->id !== $scan->id)
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    An ID with the same number was previously scanned
                    <a href="{{ route('admin.id-scans.show', $duplicate->id) }}">#{{ $duplicate->id }}</a>
                    ({{ $duplicate->status }}). Verify this is not a duplicate registration.
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-check-circle text-success me-1"></i>
                    No conflicting scan found for this ID number.
                    @if($scan->userId)
                        Returning renter: <strong>{{ $scan->user->name }}</strong> can be recognized automatically on future scans.
                    @endif
                </p>
            @endif
        </x-admin.card>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const rejectToggle = document.getElementById('rejectToggle');
        const rejectionBox = document.getElementById('rejectionBox');

        if (rejectToggle) {
            rejectToggle.addEventListener('change', function() {
                rejectionBox.style.display = this.checked ? 'block' : 'none';
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('button[data-confirm][name="status"]');
            if (!btn) return;
            const statusInput = document.getElementById('reviewStatusInput');
            if (statusInput) statusInput.value = btn.value;
        });
    })();
</script>
@endsection
