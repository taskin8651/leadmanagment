@props(['status'])
@php
$map = [
    'new' => 'badge-info',
    'contacted' => 'badge-soft',
    'qualified' => 'badge-warning',
    'follow-up' => 'badge-warning',
    'won' => 'badge-success',
    'lost' => 'badge-danger',
    'pending' => 'badge-warning',
    'completed' => 'badge-success',
    'cancelled' => 'badge-neutral',
    'missed' => 'badge-danger',
    'present' => 'badge-success',
    'late' => 'badge-warning',
    'half_day' => 'badge-neutral',
];
$cls = $map[$status] ?? 'badge-neutral';
@endphp
<span class="badge {{ $cls }}">{{ ucfirst(str_replace('-', ' ', $status)) }}</span>
