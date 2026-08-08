@extends('layouts.rider')

@section('title', 'Notifications')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-pedalya">
            <div class="card-pedalya-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span><strong>Notifications</strong></span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" onclick="filterNotif('all', this)">All</button>
                        <button class="btn btn-outline-secondary" onclick="filterNotif('unread', this)">Unread</button>
                        <button class="btn btn-outline-secondary" onclick="filterNotif('rental', this)">Rentals</button>
                        <button class="btn btn-outline-secondary" onclick="filterNotif('system', this)">System</button>
                    </div>
                </div>
                <form action="{{ route('rider.notifications.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-check-all"></i> Mark All Read</button>
                </form>
            </div>
            <div class="notification-list" id="riderNotifList">
                @forelse($notifications as $notification)
                    <div class="notification-item {{ $notification->readAt ? '' : 'unread' }}" data-type="{{ $notification->type ?? 'system' }}">
                        <div class="notification-icon" style="background: {{ $notification->type === 'rental' ? '#E8F5E9; color: #2E7D32' : '#E3F2FD; color: #1976D2' }};">
                            @if($notification->type === 'rental')
                                <i class="bi bi-key-fill"></i>
                            @else
                                <i class="bi bi-info-circle"></i>
                            @endif
                        </div>
                        <div class="notification-content">
                            <div class="notification-text">{!! $notification->message !!}</div>
                            <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="notification-actions">
                            @if(!$notification->readAt)
                                <form action="{{ route('rider.notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link" title="Mark as read"><i class="bi bi-envelope-open"></i></button>
                                </form>
                            @else
                                <span class="btn btn-sm btn-link text-muted" title="Read"><i class="bi bi-envelope"></i></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-bell-slash"></i>
                            <p>No notifications yet</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function filterNotif(type, btn) {
        document.querySelectorAll('.btn-group .btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        document.querySelectorAll('#riderNotifList .notification-item').forEach(function(item) {
            if (type === 'all') {
                item.style.display = '';
            } else if (type === 'unread') {
                item.style.display = item.classList.contains('unread') ? '' : 'none';
            } else {
                item.style.display = item.dataset.type === type ? '' : 'none';
            }
        });
    }
</script>
@endsection
