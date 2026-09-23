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
                            @if($notification->userId === auth()->id() && !$notification->readAt)
                                <button type="button"
                                    class="btn-admin btn-admin--secondary btn-admin--sm ms-1"
                                    data-mark-read="{{ $notification->id }}"
                                    title="Mark as read">
                                    <i class="bi bi-envelope-open"></i> Mark read
                                </button>
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
            <span class="admin-table-foot__info">
                Showing
                @if($notifications->total() > 0)
                    {{ $notifications->firstItem() }}&ndash;{{ $notifications->lastItem() }} of {{ $notifications->total() }} results
                @else
                    0 results
                @endif
            </span>

            <nav class="admin-pagination" aria-label="Notification pages">
                @if($notifications->onFirstPage())
                    <span class="admin-pagination__item is-disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i> Previous</span>
                @else
                    <a class="admin-pagination__item" href="{{ $notifications->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i> Previous</a>
                @endif

                @for($page = 1; $page <= $notifications->lastPage(); $page++)
                    @if($page === $notifications->currentPage())
                        <span class="admin-pagination__item is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="admin-pagination__item" href="{{ $notifications->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if($notifications->hasMorePages())
                    <a class="admin-pagination__item" href="{{ $notifications->nextPageUrl() }}" rel="next">Next <i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="admin-pagination__item is-disabled" aria-disabled="true">Next <i class="bi bi-chevron-right"></i></span>
                @endif
            </nav>
        </div>
    @endif
</div>