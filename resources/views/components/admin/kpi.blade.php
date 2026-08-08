@props([
    'title' => '',
    'value' => '',
    'icon' => 'bi-info-circle',
    'color' => 'var(--brand)',
    'trend' => null,
    'trendLabel' => '',
    'foot' => '',
    'link' => null,
])

<div class="kpi {{ $link ? 'kpi--clickable' : '' }}" @if($link) onclick="window.location='{{ $link }}'" role="link" tabindex="0" onkeydown="if(event.key==='Enter')window.location='{{ $link }}'" @endif>
    <div class="kpi__top">
        <div class="kpi__icon" style="background: {{ $color }}1a; color: {{ $color }};">
            <i class="bi {{ $icon }}"></i>
        </div>
        @if($trend)
            <span class="kpi__trend {{ $trend }}">
                <i class="bi bi-arrow-{{ $trend === 'up' ? 'up-right' : 'down-right' }}"></i>{{ $trendLabel }}
            </span>
        @endif
    </div>
    <div class="kpi__value">{{ $value }}</div>
    <div class="kpi__label">{{ $title }}</div>
    @if($foot)
        <div class="kpi__foot"><i class="bi bi-clock-history"></i>{{ $foot }}</div>
    @endif
</div>
