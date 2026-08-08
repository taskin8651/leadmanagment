@props(['name' => '', 'size' => 'md'])
@php
$initials = collect(explode(' ', trim($name)))->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
$initials = $initials ?: '?';
@endphp
<span {{ $attributes->merge(['class' => 'avatar avatar-' . $size]) }}>{{ $initials }}</span>
