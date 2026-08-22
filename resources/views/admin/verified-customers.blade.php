@extends('layouts.admin')

@section('title', 'Verified Customers')

@section('page-header')
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Verified Customers</h1>
            <p>Active customers with confirmed identity verification</p>
        </div>
        <div class="admin-pagehead__actions">
            <a href="{{ route('admin.riders.index') }}" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Customer List
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <form method="GET" action="{{ route('admin.riders.verified') }}" class="d-flex gap-2 align-items-center">
            <div class="grow">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Search by name, email, phone, or student ID..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">ID <span class="sort-ind"></span></th>
                <th class="sortable">Full Name <span class="sort-ind"></span></th>
                <th class="sortable">Student ID <span class="sort-ind"></span></th>
                <th class="sortable">Contact <span class="sort-ind"></span></th>
                <th class="sortable">Email <span class="sort-ind"></span></th>
                <th class="sortable">Verified <span class="sort-ind"></span></th>
                <th class="sortable text-center">Rentals <span class="sort-ind"></span></th>
                <th class="sortable text-center">Spent <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riders as $rider)
                <tr>
                    <td data-label="ID"><code>{{ $rider->id }}</code></td>
                    <td data-label="Full Name">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2" style="width:32px;height:32px;font-size:12px;">
                                {{ strtoupper(substr($rider->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold" style="color:var(--text-1)">{{ $rider->name }}</div>
                                @if($rider->address)
                                    <small class="text-muted">{{ Str::limit($rider->address, 30) }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td data-label="Student ID"><small>{{ $rider->studentId ?? '—' }}</small></td>
                    <td data-label="Contact">{{ $rider->phoneNumber ?? '—' }}</td>
                    <td data-label="Email"><small>{{ $rider->email }}</small></td>
                    <td data-label="Verified">
                        @php
                            $verifiedAt = null;
                            if (is_array($rider->idVerification)) {
                                $verifiedAt = $rider->idVerification['verified_at'] ?? null;
                            }
                        @endphp
                        @if($verifiedAt)
                            <small class="text-muted">{{ \Carbon\Carbon::parse($verifiedAt)->format('M d, Y') }}</small>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                    <td class="text-center" data-label="Rentals">{{ $rider->totalRentals ?? 0 }}</td>
                    <td class="text-center" data-label="Spent">₱{{ number_format($rider->totalSpent ?? 0, 2) }}</td>
                    <td data-label="Actions">
                        <div class="actions-row">
                            <button type="button" class="btn-admin btn-admin--secondary btn-admin--sm"
                                    title="View Customer"
                                    onclick="openVerifiedView(this)"
                                    data-id="{{ $rider->id }}"
                                    data-name="{{ $rider->name }}"
                                    data-student-id="{{ $rider->studentId ?? '—' }}"
                                    data-email="{{ $rider->email }}"
                                    data-phone="{{ $rider->phoneNumber ?? '—' }}"
                                    data-address="{{ $rider->address ?? '—' }}"
                                    data-joined="{{ $rider->created_at->format('M d, Y') }}"
                                    data-rentals="{{ $rider->totalRentals ?? 0 }}"
                                    data-spent="{{ number_format($rider->totalSpent ?? 0, 2) }}"
                                    data-status="{{ $rider->status ?? 'active' }}"
                                    data-verified-at="{{ $verifiedAt ? \Carbon\Carbon::parse($verifiedAt)->format('M d, Y \a\t g:i A') : '—' }}"
                                    data-id-url="{{ ($rider->idVerification['id_url'] ?? '') }}">
                                <i class="bi bi-eye"></i>
                            </button>

                            <form action="{{ route('admin.riders.status', $rider->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="inactive">
                                <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm" title="Blacklist" data-confirm="Blacklist this customer? They will be moved to Blacklisted Customers.">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <x-admin.empty-state icon="bi-patch-check" title="No verified customers found" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="admin-table-foot">
        <span>Showing {{ method_exists($riders, 'total') ? $riders->total() : $riders->count() }} verified customers</span>
        @if(method_exists($riders, 'links'))
            {{ $riders->withQueryString()->links() }}
        @endif
    </div>
</div>

{{-- ── View Customer Modal ──────────────────────────────────── --}}
<div class="admin-modal" id="verifiedViewModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <div class="d-flex align-items-center gap-2">
                <span class="vr-header-icon"><i class="bi bi-person-badge"></i></span>
                <div>
                    <h3 class="mb-0">Customer Details</h3>
                    <small class="text-muted">Verified &amp; active customer profile</small>
                </div>
            </div>
            <button type="button" class="btn-admin btn-admin--ghost btn-admin--sm" data-modal-close>
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="admin-modal__body">
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="vr-card vr-card--details">
                        <div class="vr-card__head">
                            <span class="vr-card__icon"><i class="bi bi-person-circle"></i></span>
                            <span class="vr-card__title">Profile</span>
                        </div>
                        <div class="vr-card__body">
                            <div class="vr-field">
                                <span class="vr-field__label">Customer ID</span>
                                <span class="vr-field__value" id="vvId">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Full Name</span>
                                <span class="vr-field__value" id="vvName">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Student ID</span>
                                <span class="vr-field__value" id="vvStudentId">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Email Address</span>
                                <span class="vr-field__value" id="vvEmail">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Phone Number</span>
                                <span class="vr-field__value" id="vvPhone">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Address</span>
                                <span class="vr-field__value" id="vvAddress">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="vr-card vr-card--id">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-shield-check"></i></span>
                            <span class="vr-card__title">Account Information</span>
                        </div>
                        <div class="vr-card__body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Verification Date</span>
                                        <span class="vr-field__value" id="vvVerifiedAt">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Account Status</span>
                                        <span class="vr-field__value" id="vvStatus">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Date Joined</span>
                                        <span class="vr-field__value" id="vvJoined">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Total Rentals</span>
                                        <span class="vr-field__value" id="vvRentals">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Total Spent</span>
                                        <span class="vr-field__value" id="vvSpent">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vr-card vr-card--id mt-3">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-card-image"></i></span>
                            <span class="vr-card__title">Submitted ID</span>
                            <button type="button" class="btn-admin btn-admin--ghost btn-admin--xs ms-auto d-none" id="vvZoomBtn" title="View full size">
                                <i class="bi bi-arrows-fullscreen me-1"></i>Full Size
                            </button>
                        </div>
                        <div class="vr-card__body vr-card__body--id">
                            <div class="vr-id-preview">
                                <img id="vvIdImg" src="" alt="Submitted ID">
                                <p id="vvIdEmpty" class="vr-id-empty d-none">
                                    <i class="bi bi-file-earmark-image"></i>
                                    <span>No ID image submitted</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admin-modal__foot">
            <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Close</button>
        </div>
    </div>
</div>

<div class="vr-lightbox" id="vvLightbox">
    <div class="vr-lightbox__backdrop" onclick="closeVvLightbox()"></div>
    <div class="vr-lightbox__content">
        <button type="button" class="vr-lightbox__close" onclick="closeVvLightbox()"><i class="bi bi-x-lg"></i></button>
        <img id="vvLightboxImg" src="" alt="ID Full Size">
    </div>
</div>

<script>
function openVerifiedView(btn) {
    const d = btn.dataset;
    document.getElementById('vvId').textContent = '#' + (d.id || '—');
    document.getElementById('vvName').textContent = d.name || '—';
    document.getElementById('vvStudentId').textContent = d.studentId || '—';
    document.getElementById('vvEmail').textContent = d.email || '—';
    document.getElementById('vvPhone').textContent = d.phone || '—';
    document.getElementById('vvAddress').textContent = d.address || '—';
    document.getElementById('vvJoined').textContent = d.joined || '—';
    document.getElementById('vvVerifiedAt').textContent = d.verifiedAt || '—';
    document.getElementById('vvRentals').textContent = d.rentals || '0';
    document.getElementById('vvSpent').textContent = '₱' + (d.spent || '0.00');

    const statusEl = document.getElementById('vvStatus');
    const s = d.status || 'active';
    const badgeClass = s === 'active' ? 'vr-badge--success' : s === 'suspended' ? 'vr-badge--danger' : 'vr-badge--warning';
    statusEl.innerHTML = '<span class="vr-badge ' + badgeClass + '">' + s.charAt(0).toUpperCase() + s.slice(1) + '</span>';

    const img = document.getElementById('vvIdImg');
    const empty = document.getElementById('vvIdEmpty');
    const zoomBtn = document.getElementById('vvZoomBtn');
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

    PedalyaModal.open('verifiedViewModal');
}

document.getElementById('vvZoomBtn')?.addEventListener('click', function() {
    const img = document.getElementById('vvIdImg');
    if (img && img.src && !img.classList.contains('d-none')) {
        document.getElementById('vvLightboxImg').src = img.src;
        document.getElementById('vvLightbox').classList.add('open');
    }
});
function closeVvLightbox() {
    document.getElementById('vvLightbox').classList.remove('open');
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeVvLightbox(); });
</script>
@endsection
