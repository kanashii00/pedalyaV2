@extends('layouts.admin')

@section('title', 'Notifications')

@section('page-header')
    <h1>Notifications</h1>
    <p>Send and manage system notifications</p>
@endsection

@section('actions')
<button type="button" class="btn-admin btn-admin--secondary" id="markAllReadBtn" data-mark-all-read>
    <i class="bi bi-check-all me-1"></i>Mark All Read
</button>
<button type="button" class="btn-admin btn-admin--primary" onclick="PedalyaModal.open('sendNotificationModal')">
    <i class="bi bi-send me-1"></i>Send Notification
</button>
@endsection

@section('content')
<div id="adminNotificationWrap">
    @include('admin.notifications-table', ['notifications' => $notifications])
</div>

{{-- Send Notification Modal --}}
<div class="admin-modal" id="sendNotificationModal">
    <div class="admin-modal__backdrop"></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <h3><i class="bi bi-send me-2"></i>Send Notification</h3>
            <button type="button" class="admin-icon-btn" data-modal-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('admin.notifications.store') }}" method="POST" id="sendNotificationForm" novalidate>
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

                    <div class="mb-3" id="recipientGroup">
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
                        @error('recipientType')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
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
                <button type="submit" class="btn-admin btn-admin--primary" id="sendNotificationSubmit">
                    <i class="bi bi-send me-1"></i>Send Notification
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    var form = document.getElementById('sendNotificationForm');
    if (!form) return;

    var submitBtn = document.getElementById('sendNotificationSubmit');
    var storeUrl = @json(route('admin.notifications.store'));
    var tableUrl = @json(route('admin.notifications.table'));

    function toggleRecipientFields() {
        var type = document.querySelector('[name="recipientType"]:checked')?.value ?? 'broadcast';
        var singleSection = document.getElementById('singleUserSection');
        var multiSection = document.getElementById('multiUsersSection');
        var broadcastLabel = document.getElementById('broadcastLabel');

        singleSection.style.display = type === 'single' ? 'block' : 'none';
        multiSection.style.display = type === 'multi' ? 'block' : 'none';
        broadcastLabel.style.display = type === 'broadcast' ? '' : 'none';
    }

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('.invalid-feedback').forEach(function (el) { el.style.display = ''; });
    }

    function fieldFor(key) {
        var name = key === 'user_ids' ? 'user_ids[]' : key;
        return form.querySelector('[name="' + name + '"]');
    }

    function showFieldErrors(errors) {
        if (!errors) return;
        Object.keys(errors).forEach(function (key) {
            var el = fieldFor(key);
            var target = el;
            if (!el && key === 'recipientType') {
                target = form.querySelector('[name="recipientType"]');
                el = target; // radios share one group
            }
            if (!target) return;
            target.classList.add('is-invalid');
            var group = target.closest('.mb-3');
            if (!group) return;
            var fb = group.querySelector('.invalid-feedback');
            if (!fb) {
                fb = document.createElement('div');
                fb.className = 'invalid-feedback';
                group.appendChild(fb);
            }
            fb.textContent = Array.isArray(errors[key]) ? errors[key][0] : String(errors[key]);
            fb.style.display = 'block';
        });
    }

    function firstError(errors) {
        var keys = Object.keys(errors || {});
        if (!keys.length) return null;
        var list = errors[keys[0]];
        return Array.isArray(list) && list.length ? list[0] : null;
    }

    function refreshNotificationTable() {
        var wrap = document.getElementById('adminNotificationWrap');
        if (!wrap) return;
        fetch(tableUrl, { headers: { 'Accept': 'text/html' } })
            .then(function (res) { if (!res.ok) throw new Error(); return res.text(); })
            .then(function (html) {
                wrap.innerHTML = html;
                if (window.PedalyaBadges) window.PedalyaBadges.refresh();
                if (window.PedalyaTableInit) window.PedalyaTableInit(wrap);
            })
            .catch(function () {});
    }

    // The header dropdown's "Mark all read" (admin.js) also needs to refresh
    // this table, so listen for the shared event.
    window.addEventListener('pedalya:notifications-changed', refreshNotificationTable);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearFieldErrors();

        var original = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending…';

        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.Pedalya.csrfToken,
                'Accept': 'application/json',
            },
            body: new FormData(form),
        })
        .then(function (res) {
            return res.json().then(function (data) {
                data._httpStatus = res.status;
                return data;
            });
        })
        .then(function (data) {
            if (data._httpStatus >= 200 && data._httpStatus < 300) {
                PedalyaModal.close('sendNotificationModal');
                PedalyaToast.success(data.message || 'Notification sent successfully.');
                form.reset();
                toggleRecipientFields();
                window.dispatchEvent(new Event('pedalya:notifications-changed'));
                if (window.PedalyaBadges) window.PedalyaBadges.refresh();
                return;
            }

            if (data._httpStatus === 422) {
                showFieldErrors(data.errors || {});
                var msg = firstError(data.errors) || data.message || 'Please fix the highlighted fields.';
                PedalyaToast.error(msg, 'Cannot send notification');
                return;
            }

            if (data._httpStatus === 409) {
                PedalyaToast.warning(data.message || 'This notification was already sent.', 'Duplicate');
                return;
            }

            PedalyaToast.error(data.message || 'Unable to send the notification. Please try again.', 'Send failed');
        })
        .catch(function () {
            PedalyaToast.error('Unable to send the notification. Please check your connection and try again.', 'Send failed');
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        });
    });

    // "Mark all read" is handled globally in admin.js via [data-mark-all-read];
    // it toasts, resets badges and dispatches pedalya:notifications-changed so
    // the table refresh listener above runs.

    // Per-row mark-as-read (admin's own notifications)
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-mark-read]');
        if (!btn) return;

        var id = btn.dataset.markRead;
        if (!id) return;

        fetch(@json(route('admin.notifications.mark-read', ['id' => 'ID_PLACEHOLDER'])).replace('ID_PLACEHOLDER', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.Pedalya.csrfToken,
                'Accept': 'application/json',
            },
        })
        .then(function (res) {
            if (!res.ok) throw new Error();
            return res.json();
        })
        .then(function () {
            PedalyaToast.success('Notification marked as read.');
            if (window.PedalyaBadges) window.PedalyaBadges.refresh();
            window.dispatchEvent(new Event('pedalya:notifications-changed'));
        })
        .catch(function () {
            PedalyaToast.error('Unable to mark the notification as read.', 'Failed');
        });
    });

    window.toggleRecipientFields = toggleRecipientFields;
    toggleRecipientFields();
})();
</script>
@endsection