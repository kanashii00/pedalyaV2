@extends('layouts.admin')

@section('title', 'Notifications')

@section('page-header')
    <h1>Notifications</h1>
    <p>Send and manage system notifications</p>
@endsection

@section('actions')
<button type="button" class="btn-admin btn-admin--primary" onclick="PedalyaModal.open('sendNotificationModal')">
    <i class="bi bi-send me-1"></i>Send Notification
</button>
@endsection

@section('content')
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search this list..."></div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="sortable">Title <span class="sort-ind"></span></th>
                    <th class="sortable">Message <span class="sort-ind"></span></th>
                    <th class="sortable">Type <span class="sort-ind"></span></th>
                    <th class="sortable">Sent To <span class="sort-ind"></span></th>
                    <th class="sortable">Date <span class="sort-ind"></span></th>
                    <th>Read <span class="sort-ind"></span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications ?? [] as $notification)
                    <tr>
                        <td data-label="Title" class="cell-title">{{ $notification->title }}</td>
                        <td data-label="Message" class="text-truncate" style="max-width: 250px;" title="{{ $notification->message }}">
                            {{ $notification->message }}
                        </td>
                        <td data-label="Type">
                            @switch($notification->type)
                                @case('info')<x-admin.badge type="info" label="Info"/>@break
                                @case('warning')<x-admin.badge type="warning" label="Warning"/>@break
                                @case('error')<x-admin.badge type="danger" label="Error"/>@break
                                @case('success')<x-admin.badge type="success" label="Success"/>@break
                                @default<x-admin.badge type="neutral" label="{{ ucfirst($notification->type) }}"/>@break
                            @endswitch
                        </td>
                        <td data-label="Sent To">
                            @if($notification->userId)
                                {{ $notification->user->name ?? $notification->userId }}
                            @else
                                <x-admin.badge type="neutral" label="All Users"/>
                            @endif
                        </td>
                        <td data-label="Date"><small>{{ $notification->created_at->format('M d, Y H:i') }}</small></td>
                        <td data-label="Read">
                            @if($notification->readAt)
                                <x-admin.badge type="success" label="Read"/>
                            @else
                                <x-admin.badge type="info" label="Unread"/>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-admin.empty-state icon="bi-bell-slash" title="No notifications found" message="Send your first notification to reach all registered users."/>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($notifications, 'links'))
        <div class="admin-table-foot">
            <span>Showing {{ $notifications->total() }} records</span>
            {{ $notifications->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Send Notification Modal --}}
<div class="admin-modal" id="sendNotificationModal">
    <div class="admin-modal__backdrop"></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <h3><i class="bi bi-send me-2"></i>Send Notification</h3>
            <button type="button" class="admin-icon-btn" data-modal-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('admin.notifications.store') }}" method="POST">
            @csrf
            <div class="admin-modal__body">
                <div class="admin-form">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                            name="title" value="{{ old('title') }}" required
                            placeholder="Notification title">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('message') is-invalid @enderror"
                            name="message" rows="4" required
                            placeholder="Write your notification message...">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror"
                            name="type" required>
                            <option value="">Select Type</option>
                            <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>Error</option>
                            <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr style="border-color: var(--border-strong);">

                    <div class="mb-3">
                        <label class="form-label">Recipients</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input type="radio" id="recipientBroadcast" name="recipientType"
                                    class="form-check-input" value="broadcast"
                                    {{ old('recipientType', 'broadcast') == 'broadcast' ? 'checked' : '' }}
                                    onchange="toggleRecipientFields()">
                                <label class="form-check-label" for="recipientBroadcast">Broadcast to All</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="recipientSingle" name="recipientType"
                                    class="form-check-input" value="single"
                                    {{ old('recipientType') == 'single' ? 'checked' : '' }}
                                    onchange="toggleRecipientFields()">
                                <label class="form-check-label" for="recipientSingle">Single User</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="recipientMulti" name="recipientType"
                                    class="form-check-input" value="multi"
                                    {{ old('recipientType') == 'multi' ? 'checked' : '' }}
                                    onchange="toggleRecipientFields()">
                                <label class="form-check-label" for="recipientMulti">Multiple Users</label>
                            </div>
                        </div>
                    </div>

                    <div id="singleUserSection" style="{{ old('recipientType') == 'single' ? '' : 'display:none;' }}">
                        <div class="mb-3">
                            <label class="form-label">Select User</label>
                            <select class="form-select @error('user_id') is-invalid @enderror"
                                name="user_id">
                                <option value="">Select a user</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="multiUsersSection" style="{{ old('recipientType') == 'multi' ? '' : 'display:none;' }}">
                        <div class="mb-3">
                            <label class="form-label">Select Users</label>
                            <select class="form-select @error('user_ids') is-invalid @enderror"
                                name="user_ids[]" multiple size="5">
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple users.</small>
                            @error('user_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="broadcastLabel" style="{{ old('recipientType', 'broadcast') == 'broadcast' ? '' : 'display:none;' }}">
                        <x-admin.badge type="neutral" label="This notification will be sent to all registered users."/>
                    </div>
                </div>
            </div>
            <div class="admin-modal__foot">
                <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-admin btn-admin--primary">
                    <i class="bi bi-send me-1"></i>Send Notification
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleRecipientFields() {
    const type = document.querySelector('[name="recipientType"]:checked')?.value ?? 'broadcast';
    const singleSection = document.getElementById('singleUserSection');
    const multiSection = document.getElementById('multiUsersSection');
    const broadcastLabel = document.getElementById('broadcastLabel');

    singleSection.style.display = type === 'single' ? 'block' : 'none';
    multiSection.style.display = type === 'multi' ? 'block' : 'none';
    broadcastLabel.style.display = type === 'broadcast' ? '' : 'none';
}
</script>
@endsection
