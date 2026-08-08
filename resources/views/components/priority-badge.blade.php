@props(['priority'])
@php
$map = [
    'low' => 'badge-neutral',
    'medium' => 'badge-soft',
    'high' => 'badge-warning',
    'hot' => 'badge-hot',
];
$cls = $map[$priority] ?? 'badge-neutral';
@endphp
<span class="badge {{ $cls }}">@if($priority === 'hot')<i class="bi bi-fire" style="font-size:10px;margin-right:3px"></i>@endif{{ ucfirst($priority) }}</span>
