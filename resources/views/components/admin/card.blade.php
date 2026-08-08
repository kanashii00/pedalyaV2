@props(['title' => null, 'sub' => null, 'flush' => false, 'bodyClass' => ''])

<div class="admin-card">
    @if($title || isset($tools))
        <div class="admin-card__head">
            <div>
                @if($title)<div class="admin-card__title">{{ $title }}</div>@endif
                @if($sub)<div class="admin-card__sub">{{ $sub }}</div>@endif
            </div>
            @isset($tools)
                <div class="admin-card__tools">{{ $tools }}</div>
            @endisset
        </div>
    @endif
    <div class="admin-card__body {{ $flush ? 'admin-card__body--flush' : '' }} {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
