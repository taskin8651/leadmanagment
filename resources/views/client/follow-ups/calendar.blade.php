@extends('layouts.client')
@php($title = 'Follow-up Calendar')
@section('content')

@php
    $gridStart = $month->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $gridEnd = $month->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
    $prevMonth = $month->copy()->subMonthNoOverflow()->format('Y-m');
    $nextMonth = $month->copy()->addMonthNoOverflow()->format('Y-m');
@endphp

<x-page-header title="Follow-up Calendar" subtitle="{{ $month->format('F Y') }}">
    <x-slot:actions>
        <a href="{{ route('client.follow-ups.index') }}" class="btn btn-light"><i class="bi bi-list-ul me-1"></i>List View</a>
        <a href="{{ route('client.follow-ups.calendar', ['month' => $prevMonth]) }}" class="btn btn-light"><i class="bi bi-chevron-left"></i></a>
        <a href="{{ route('client.follow-ups.calendar') }}" class="btn btn-light">Today</a>
        <a href="{{ route('client.follow-ups.calendar', ['month' => $nextMonth]) }}" class="btn btn-light"><i class="bi bi-chevron-right"></i></a>
    </x-slot:actions>
</x-page-header>

<div class="card fade-up p-2 p-md-3">
    <div class="calendar-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
            <div class="text-center fw-bold muted small py-1">{{ $d }}</div>
        @endforeach

        @php($cursor = $gridStart->copy())
        @while($cursor->lte($gridEnd))
            @php($dayFollowUps = $followUps->get($cursor->format('Y-m-d'), collect()))
            <div class="border rounded p-2" style="min-height:110px;{{ $cursor->month !== $month->month ? 'opacity:.4' : '' }}{{ $cursor->isToday() ? 'background:var(--primary-soft,#eef0ff);border-color:#c7cbff' : '' }}">
                <div class="small fw-bold mb-1">{{ $cursor->day }}</div>
                @foreach($dayFollowUps->take(3) as $f)
                    <a href="{{ route('client.leads.show',$f->lead) }}" class="d-block text-decoration-none mb-1" data-bs-toggle="tooltip" title="{{ $f->lead->name }} — {{ $f->subject ?: $f->type }}">
                        <span class="badge {{ $f->status==='completed' ? 'badge-success' : ($f->status==='pending' && $f->follow_up_at->isPast() ? 'badge-warning' : 'badge-neutral') }} d-block text-truncate" style="text-align:left;font-weight:500">
                            {{ $f->follow_up_at->format('h:i A') }} {{ $f->lead->name }}
                        </span>
                    </a>
                @endforeach
                @if($dayFollowUps->count() > 3)
                    <div class="row-sub small">+{{ $dayFollowUps->count() - 3 }} more</div>
                @endif
            </div>
            @php($cursor->addDay())
        @endwhile
    </div>
</div>

@endsection
