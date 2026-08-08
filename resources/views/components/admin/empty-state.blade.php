@props(['icon' => 'bi-inbox', 'title' => 'Nothing here yet', 'message' => ''])

<div class="admin-empty">
    <i class="bi {{ $icon }}"></i>
    <h4>{{ $title }}</h4>
    @if($message)<p>{{ $message }}</p>@endif
    {{ $slot }}
</div>
