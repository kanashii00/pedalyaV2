@props(['type' => 'neutral', 'label' => '', 'plain' => false])

<span class="badge-admin badge-admin--{{ $type }} {{ $plain ? 'badge-admin--plain' : '' }}">{{ $label }}</span>
