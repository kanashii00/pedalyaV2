@extends('layouts.admin')

@section('title', 'Blacklisted Customers')

@section('page-header')
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Blacklisted Customers</h1>
            <p>Suspended, disabled, or blacklisted customers</p>
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
        <form method="GET" action="{{ route('admin.riders.blacklisted') }}" class="d-flex gap-2 align-items-center">
            <div class="grow">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Search by name, email, phone, or student ID..." value="{{ request('search') }}">
            </div>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="blacklisted" {{ request('status') === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
            </select>
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
                <th class="sortable">Reason <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Date Added <span class="sort-ind"></span></th>
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
                    <td data-label="Reason">
                        @if($rider->blacklistReason)
                            <small class="text-muted" title="{{ $rider->blacklistReason }}">{{ Str::limit($rider->blacklistReason, 40) }}</small>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                    <td data-label="Status">
                        @if($rider->status === 'inactive')
                            <x-admin.badge type="neutral" label="Disabled" />
                        @elseif($rider->status === 'suspended')
                            <x-admin.badge type="warning" label="Suspended" />
                        @elseif($rider->status === 'blacklisted')
                            <x-admin.badge type="danger" label="Blacklisted" />
                        @else
                            <x-admin.badge type="neutral" :label="ucfirst($rider->status)" />
                        @endif
                    </td>
                    <td data-label="Date Added"><small class="text-muted">{{ $rider->created_at->format('M d, Y') }}</small></td>
                    <td data-label="Actions">
                        <div class="actions-row">
                            <button type="button" class="btn-admin btn-admin--secondary btn-admin--sm"
                                    title="View Customer"
                                    onclick="openBlacklistedView(this)"
                                    data-id="{{ $rider->id }}"
                                    data-name="{{ $rider->name }}"
                                    data-student-id="{{ $rider->studentId ?? '—' }}"
                                    data-email="{{ $rider->email }}"
                                    data-phone="{{ $rider->phoneNumber ?? '—' }}"
                                    data-address="{{ $rider->address ?? '—' }}"
                                    data-joined="{{ $rider->created_at->format('M d, Y') }}"
                                    data-status="{{ $rider->status }}"
                                    data-blacklist-reason="{{ $rider->blacklistReason ?? '' }}"
                                    data-id-url="{{ ($rider->idVerification['id_url'] ?? '') }}">
                                <i class="bi bi-eye"></i>
                            </button>

                            <button type="button" class="btn-admin btn-admin--warning btn-admin--sm"
                                    title="Edit / Manage"
                                    onclick="openBlacklistManage(this)"
                                    data-id="{{ $rider->id }}"
                                    data-name="{{ $rider->name }}"
                                    data-student-id="{{ $rider->studentId ?? '' }}"
                                    data-email="{{ $rider->email }}"
                                    data-phone="{{ $rider->phoneNumber ?? '' }}"
                                    data-address="{{ $rider->address ?? '' }}"
                                    data-status="{{ $rider->status }}"
                                    data-blacklist-reason="{{ $rider->blacklistReason ?? '' }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <form action="{{ route('admin.riders.status', $rider->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn-admin btn-admin--success btn-admin--sm" title="Restore to Active" data-confirm="Restore this customer to Active? They will appear in Verified Customers if verified.">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <x-admin.empty-state icon="bi-x-octagon" title="No blacklisted customers" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="admin-table-foot">
        <span>Showing {{ method_exists($riders, 'total') ? $riders->total() : $riders->count() }} blacklisted customers</span>
        @if(method_exists($riders, 'links'))
            {{ $riders->withQueryString()->links() }}
        @endif
    </div>
</div>

{{-- ── View Customer Modal ──────────────────────────────────── --}}
<div class="admin-modal" id="blacklistedViewModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <div class="d-flex align-items-center gap-2">
                <span class="vr-header-icon"><i class="bi bi-x-octagon"></i></span>
                <div>
                    <h3 class="mb-0">Customer Details</h3>
                    <small class="text-muted">Blacklisted customer profile</small>
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
                                <span class="vr-field__value" id="bvId">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Full Name</span>
                                <span class="vr-field__value" id="bvName">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Student ID</span>
                                <span class="vr-field__value" id="bvStudentId">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Email Address</span>
                                <span class="vr-field__value" id="bvEmail">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Phone Number</span>
                                <span class="vr-field__value" id="bvPhone">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Address</span>
                                <span class="vr-field__value" id="bvAddress">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="vr-card vr-card--id">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-shield-x"></i></span>
                            <span class="vr-card__title">Account Information</span>
                        </div>
                        <div class="vr-card__body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Date Joined</span>
                                        <span class="vr-field__value" id="bvJoined">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Account Status</span>
                                        <span class="vr-field__value" id="bvStatus">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Blacklist Reason</span>
                                        <span class="vr-field__value" id="bvReason">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vr-card vr-card--id mt-3">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-card-image"></i></span>
                            <span class="vr-card__title">Submitted ID</span>
                            <button type="button" class="btn-admin btn-admin--ghost btn-admin--xs ms-auto d-none" id="bvZoomBtn" title="View full size">
                                <i class="bi bi-arrows-fullscreen me-1"></i>Full Size
                            </button>
                        </div>
                        <div class="vr-card__body vr-card__body--id">
                            <div class="vr-id-preview">
                                <img id="bvIdImg" src="" alt="Submitted ID">
                                <p id="bvIdEmpty" class="vr-id-empty d-none">
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

{{-- ── Edit / Manage Modal ─────────────────────────────────── --}}
<div class="admin-modal" id="blacklistManageModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <div class="d-flex align-items-center gap-2">
                <span class="vr-header-icon"><i class="bi bi-pencil-square"></i></span>
                <div>
                    <h3 class="mb-0">Edit / Manage Customer</h3>
                    <small class="text-muted">Update blacklist status, reason, and profile details</small>
                </div>
            </div>
            <button type="button" class="btn-admin btn-admin--ghost btn-admin--sm" data-modal-close>
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="blacklistManageForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="admin-modal__body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="vr-card vr-card--details">
                            <div class="vr-card__head">
                                <span class="vr-card__icon"><i class="bi bi-person-circle"></i></span>
                                <span class="vr-card__title">Profile</span>
                            </div>
                            <div class="vr-card__body">
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Full Name</label>
                                    <input type="text" name="name" class="form-control form-control-sm" id="bmName" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Student ID</label>
                                    <input type="text" name="studentId" class="form-control form-control-sm" id="bmStudentId">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Email</label>
                                    <input type="email" name="email" class="form-control form-control-sm" id="bmEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Phone Number</label>
                                    <input type="text" name="phoneNumber" class="form-control form-control-sm" id="bmPhone">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Address</label>
                                    <input type="text" name="address" class="form-control form-control-sm" id="bmAddress">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vr-card vr-card--id">
                            <div class="vr-card__head">
                                <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-shield-x"></i></span>
                                <span class="vr-card__title">Blacklist Details</span>
                            </div>
                            <div class="vr-card__body">
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Status</label>
                                    <select name="status" class="form-select form-select-sm" id="bmStatus">
                                        <option value="inactive">Disabled</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="blacklisted">Blacklisted</option>
                                        <option value="active">Restore to Active</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Blacklist Reason</label>
                                    <textarea name="blacklistReason" class="form-control form-control-sm" id="bmReason" rows="4" placeholder="Enter reason for blacklisting..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin-modal__foot">
                <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-admin btn-admin--success btn-admin--sm">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<div class="vr-lightbox" id="bvLightbox">
    <div class="vr-lightbox__backdrop" onclick="closeBvLightbox()"></div>
    <div class="vr-lightbox__content">
        <button type="button" class="vr-lightbox__close" onclick="closeBvLightbox()"><i class="bi bi-x-lg"></i></button>
        <img id="bvLightboxImg" src="" alt="ID Full Size">
    </div>
</div>

<script>
function openBlacklistedView(btn) {
    const d = btn.dataset;
    document.getElementById('bvId').textContent = '#' + (d.id || '—');
    document.getElementById('bvName').textContent = d.name || '—';
    document.getElementById('bvStudentId').textContent = d.studentId || '—';
    document.getElementById('bvEmail').textContent = d.email || '—';
    document.getElementById('bvPhone').textContent = d.phone || '—';
    document.getElementById('bvAddress').textContent = d.address || '—';
    document.getElementById('bvJoined').textContent = d.joined || '—';

    const statusEl = document.getElementById('bvStatus');
    const s = d.status || 'inactive';
    const badgeMap = { active: 'vr-badge--success', inactive: 'vr-badge--neutral', suspended: 'vr-badge--warning', blacklisted: 'vr-badge--danger' };
    const labelMap = { active: 'Active', inactive: 'Disabled', suspended: 'Suspended', blacklisted: 'Blacklisted' };
    statusEl.innerHTML = '<span class="vr-badge ' + (badgeMap[s] || 'vr-badge--neutral') + '">' + (labelMap[s] || s) + '</span>';

    document.getElementById('bvReason').textContent = d.blacklistReason || 'No reason provided';

    const img = document.getElementById('bvIdImg');
    const empty = document.getElementById('bvIdEmpty');
    const zoomBtn = document.getElementById('bvZoomBtn');
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

    PedalyaModal.open('blacklistedViewModal');
}

function openBlacklistManage(btn) {
    const d = btn.dataset;
    document.getElementById('bmName').value = d.name || '';
    document.getElementById('bmStudentId').value = d.studentId || '';
    document.getElementById('bmEmail').value = d.email || '';
    document.getElementById('bmPhone').value = d.phone || '';
    document.getElementById('bmAddress').value = d.address || '';
    document.getElementById('bmStatus').value = d.status || 'inactive';
    document.getElementById('bmReason').value = d.blacklistReason || '';

    document.getElementById('blacklistManageForm').action =
        '{{ url("admin/blacklisted-customers") }}/' + d.id;

    PedalyaModal.open('blacklistManageModal');
}

document.getElementById('bvZoomBtn')?.addEventListener('click', function() {
    const img = document.getElementById('bvIdImg');
    if (img && img.src && !img.classList.contains('d-none')) {
        document.getElementById('bvLightboxImg').src = img.src;
        document.getElementById('bvLightbox').classList.add('open');
    }
});
function closeBvLightbox() {
    document.getElementById('bvLightbox').classList.remove('open');
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeBvLightbox(); });
</script>
@endsection
