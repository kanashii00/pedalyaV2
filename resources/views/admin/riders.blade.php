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
                <th class="sortable text-center">Total Rentals <span class="sort-ind"></span></th>
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
                    <td class="text-center" data-label="Total Rentals">{{ $rider->totalRentals ?? 0 }}</td>
                    <td data-label="Total Spent">₱{{ number_format($rider->totalSpent ?? 0, 2) }}</td>
                    <td data-label="Joined"><small class="text-muted">{{ $rider->created_at->format('M d, Y') }}</small></td>
                    <td data-label="Actions">
                        <div class="actions-row">
                            @if(!($rider->verified ?? false))
                                <button type="button"
                                        class="btn-admin btn-admin--warning btn-admin--sm"
                                        title="Review Verification"
                                        onclick="openVerifyReview(this)"
                                        data-id="{{ $rider->id }}"
                                        data-name="{{ $rider->name }}"
                                        data-email="{{ $rider->email }}"
                                        data-phone="{{ $rider->phoneNumber ?? '—' }}"
                                        data-address="{{ $rider->address ?? '—' }}"
                                        data-joined="{{ $rider->created_at->format('M d, Y') }}"
                                        data-id-url="{{ $rider->idVerification['id_url'] ?? '' }}"
                                        data-id-status="{{ $rider->idVerification['status'] ?? 'pending' }}"
                                        data-id-submitted="{{ $rider->idVerification['submitted_at'] ?? '' }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                            @endif

                            @if(($rider->status ?? 'active') === 'active')
                                <form action="{{ route('admin.riders.status', $rider->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="inactive">
                                    <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm" title="Disable" data-confirm="Disable this rider?">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.riders.status', $rider->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm" title="Enable">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            @endif

                            @if(($rider->status ?? 'active') !== 'suspended')
                                <form action="{{ route('admin.riders.status', $rider->id) }}" method="POST">
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

{{-- ── Review Verification Modal ────────────────────────────────── --}}
<div class="admin-modal" id="verifyReviewModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--xl">
        {{-- Header --}}
        <div class="admin-modal__head">
            <div class="d-flex align-items-center gap-2">
                <span class="vr-header-icon"><i class="bi bi-shield-check"></i></span>
                <div>
                    <h3 class="mb-0">Review Verification</h3>
                    <small class="text-muted">Verify customer identity before approval</small>
                </div>
            </div>
            <button type="button" class="btn-admin btn-admin--ghost btn-admin--sm" data-modal-close>
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="admin-modal__body">
            <div class="row g-4">

                {{-- ── Customer Details Card ──────────────────── --}}
                <div class="col-lg-5">
                    <div class="vr-card vr-card--details">
                        <div class="vr-card__head">
                            <span class="vr-card__icon"><i class="bi bi-person-circle"></i></span>
                            <span class="vr-card__title">Customer Details</span>
                        </div>
                        <div class="vr-card__body">
                            <div class="vr-field">
                                <span class="vr-field__label">Full Name</span>
                                <span class="vr-field__value" id="vrName">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Email Address</span>
                                <span class="vr-field__value" id="vrEmail">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Phone Number</span>
                                <span class="vr-field__value" id="vrPhone">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Address</span>
                                <span class="vr-field__value" id="vrAddress">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Date Joined</span>
                                <span class="vr-field__value" id="vrJoined">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Verification Status</span>
                                <span class="vr-field__value" id="vrIdStatus">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Submitted ID Card ──────────────────────── --}}
                <div class="col-lg-7">
                    <div class="vr-card vr-card--id">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-card-image"></i></span>
                            <span class="vr-card__title">Submitted ID</span>
                            <button type="button" class="btn-admin btn-admin--ghost btn-admin--xs ms-auto d-none" id="vrZoomBtn" title="View full size">
                                <i class="bi bi-arrows-fullscreen me-1"></i>Full Size
                            </button>
                        </div>
                        <div class="vr-card__body vr-card__body--id">
                            <div id="vrIdWrap" class="vr-id-preview">
                                <img id="vrIdImg" src="" alt="Submitted ID">
                                <p id="vrIdEmpty" class="vr-id-empty d-none">
                                    <i class="bi bi-file-earmark-image"></i>
                                    <span>No ID image submitted</span>
                                    <small>The customer has not uploaded an identification document.</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="admin-modal__foot">
            <form id="vrRejectForm" method="POST" action="{{ route('admin.riders.verify', '__ID__') }}" class="d-flex align-items-center gap-2 me-auto">
                @csrf
                @method('PUT')
                <input type="hidden" name="approved" value="0">
                <input type="text" name="reason" class="form-control form-control-sm vr-reason-input" placeholder="Rejection reason (optional)">
                <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm vr-action-btn">
                    <i class="bi bi-x-lg me-1"></i>Reject Verification
                </button>
            </form>
            <form id="vrApproveForm" method="POST" action="{{ route('admin.riders.verify', '__ID__') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="approved" value="1">
                <button type="submit" class="btn-admin btn-admin--success btn-admin--sm vr-action-btn vr-action-btn--approve">
                    <i class="bi bi-check-lg me-1"></i>Approve Verification
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Full-size ID Lightbox --}}
<div class="vr-lightbox" id="vrLightbox">
    <div class="vr-lightbox__backdrop" onclick="closeLightbox()"></div>
    <div class="vr-lightbox__content">
        <button type="button" class="vr-lightbox__close" onclick="closeLightbox()"><i class="bi bi-x-lg"></i></button>
        <img id="vrLightboxImg" src="" alt="ID Full Size">
    </div>
</div>

<script>
function openVerifyReview(btn) {
    const d = btn.dataset;
    document.getElementById('vrName').textContent = d.name || '—';
    document.getElementById('vrEmail').textContent = d.email || '—';
    document.getElementById('vrPhone').textContent = d.phone || '—';
    document.getElementById('vrAddress').textContent = d.address || '—';
    document.getElementById('vrJoined').textContent = d.joined || '—';

    const statusEl = document.getElementById('vrIdStatus');
    const s = d.idStatus || 'pending';
    const badgeClass = s === 'approved' ? 'vr-badge--success' : s === 'rejected' ? 'vr-badge--danger' : 'vr-badge--warning';
    const badgeLabel = s === 'approved' ? 'Approved' : s === 'rejected' ? 'Rejected' : 'Pending';
    statusEl.innerHTML = '<span class="vr-badge ' + badgeClass + '">' + badgeLabel + '</span>';

    const img = document.getElementById('vrIdImg');
    const empty = document.getElementById('vrIdEmpty');
    const zoomBtn = document.getElementById('vrZoomBtn');
    if (d.idUrl) {
        img.src = d.idUrl;
        img.classList.remove('d-none');
        empty.classList.add('d-none');
        zoomBtn.classList.remove('d-none');
    } else {
        img.classList.add('d-none');
        empty.classList.remove('d-none');
        zoomBtn.classList.add('d-none');
    }

    const id = d.id;
    document.getElementById('vrApproveForm').action = '{{ url("admin/riders") }}/' + id + '/verify';
    document.getElementById('vrRejectForm').action = '{{ url("admin/riders") }}/' + id + '/verify';

    PedalyaModal.open('verifyReviewModal');
}

document.getElementById('vrZoomBtn')?.addEventListener('click', function() {
    const img = document.getElementById('vrIdImg');
    if (img && img.src && !img.classList.contains('d-none')) {
        document.getElementById('vrLightboxImg').src = img.src;
        document.getElementById('vrLightbox').classList.add('open');
    }
});
function closeLightbox() {
    document.getElementById('vrLightbox').classList.remove('open');
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeLightbox(); });

document.getElementById('vrApproveForm')?.addEventListener('submit', function(e) {
    if (!confirm('Approve this customer\'s verification? This will mark them as Verified.')) e.preventDefault();
});
document.getElementById('vrRejectForm')?.addEventListener('submit', function(e) {
    if (!confirm('Reject this customer\'s verification? This will deny their ID submission.')) e.preventDefault();
});
</script>
@endsection