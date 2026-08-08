@extends('layouts.client')
@php($title = 'Attendance — ' . $attendance->user->name)
@section('content')

<x-page-header title="{{ $attendance->user->name }}" subtitle="{{ $attendance->date->format('d M Y') }} · {{ $attendance->user->roles->first()->name ?? '' }}">
    <x-slot:actions><a href="{{ route('client.attendances.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back to Attendance</a></x-slot:actions>
</x-page-header>

@php
$statCards = [
    ['icon' => 'bi-box-arrow-in-right', 'bg' => 'var(--primary-soft)', 'fg' => 'var(--primary)', 'label' => 'Check-in', 'value' => $attendance->check_in_at?->format('h:i A') ?? '—'],
    ['icon' => 'bi-box-arrow-right', 'bg' => 'var(--info-soft)', 'fg' => 'var(--info)', 'label' => 'Check-out', 'value' => $attendance->check_out_at?->format('h:i A') ?? '—'],
    ['icon' => 'bi-hourglass-split', 'bg' => 'var(--success-soft)', 'fg' => 'var(--success)', 'label' => 'Worked', 'value' => $attendance->worked_minutes ? floor($attendance->worked_minutes / 60) . 'h ' . ($attendance->worked_minutes % 60) . 'm' : '—'],
];
@endphp
<div class="row g-3 mb-3">
    @foreach($statCards as $c)
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="icon-wrap" style="background:{{ $c['bg'] }};color:{{ $c['fg'] }}"><i class="bi {{ $c['icon'] }}"></i></div>
                <div class="label">{{ $c['label'] }}</div>
                <div class="num" style="font-size:20px">{{ $c['value'] }}</div>
            </div>
        </div>
    @endforeach
    <div class="col-md-3">
        <div class="card stat-card h-100">
            <div class="icon-wrap" style="background:var(--warning-soft);color:var(--warning)"><i class="bi bi-flag"></i></div>
            <div class="label">Status</div>
            <div class="num" style="font-size:16px">
                @if($attendance->status === 'present')<span class="badge badge-success">Present</span>
                @elseif($attendance->status === 'late')<span class="badge badge-warning">Late</span>
                @else<span class="badge badge-neutral">Half Day</span>@endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card p-4 fade-up h-100">
            <h6 class="fw-bold mb-3">Check-in Details</h6>
            <div class="muted small mb-1">Time</div>
            <div class="fw-semibold mb-3">{{ $attendance->check_in_at?->format('d M Y, h:i A') ?? 'Not checked in' }}</div>
            <div class="muted small mb-1">Address</div>
            <div class="fw-semibold mb-3">{{ $attendance->check_in_address ?? '—' }}</div>
            @if($attendance->check_in_latitude)
                <x-attendance-map :points="[['lat' => $attendance->check_in_latitude, 'lng' => $attendance->check_in_longitude, 'label' => 'Check-in', 'color' => '#4f46e5']]" />
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4 fade-up h-100">
            <h6 class="fw-bold mb-3">Check-out Details</h6>
            <div class="muted small mb-1">Time</div>
            <div class="fw-semibold mb-3">{{ $attendance->check_out_at?->format('d M Y, h:i A') ?? 'Not checked out yet' }}</div>
            <div class="muted small mb-1">Address</div>
            <div class="fw-semibold mb-3">{{ $attendance->check_out_address ?? '—' }}</div>
            @if($attendance->check_out_latitude)
                <x-attendance-map :points="[['lat' => $attendance->check_out_latitude, 'lng' => $attendance->check_out_longitude, 'label' => 'Check-out', 'color' => '#16a34a']]" />
            @endif
        </div>
    </div>
</div>

@if($attendance->notes)
    <div class="card p-4 fade-up mt-3">
        <h6 class="fw-bold mb-2">Notes</h6>
        <p class="mb-0" style="white-space:pre-wrap">{{ $attendance->notes }}</p>
    </div>
@endif

@endsection
