@props([
    'icon' => 'bi-graph-up',
    'label' => '',
    'value' => 0,
    'prefix' => '',
    'suffix' => '',
    'decimals' => 0,
    'delta' => null,
    'deltaUp' => true,
    'deltaLabel' => 'vs last month',
    'color' => 'primary',
])
@php
$colors = [
    'primary' => ['bg' => 'var(--primary-soft)', 'fg' => 'var(--primary)'],
    'success' => ['bg' => 'var(--success-soft)', 'fg' => 'var(--success)'],
    'warning' => ['bg' => 'var(--warning-soft)', 'fg' => 'var(--warning)'],
    'danger' => ['bg' => 'var(--danger-soft)', 'fg' => 'var(--danger)'],
    'info' => ['bg' => 'var(--info-soft)', 'fg' => 'var(--info)'],
][$color] ?? ['bg' => 'var(--primary-soft)', 'fg' => 'var(--primary)'];
@endphp
<div class="card stat-card card-hover h-100">
    <div class="icon-wrap" style="background:{{ $colors['bg'] }};color:{{ $colors['fg'] }}"><i class="bi {{ $icon }}"></i></div>
    <div class="label">{{ $label }}</div>
    <div class="num" data-counter="{{ $value }}" data-prefix="{{ $prefix }}" data-suffix="{{ $suffix }}" data-decimals="{{ $decimals }}">{{ $prefix }}0{{ $suffix }}</div>
    @if(!is_null($delta))
        <div class="delta {{ $deltaUp ? 'up' : 'down' }}">
            <i class="bi {{ $deltaUp ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>{{ $delta }}
            <span class="vs">{{ $deltaLabel }}</span>
        </div>
    @endif
</div>
