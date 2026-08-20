@extends('layouts.client')
@php($title = 'Reports')
@section('content')

<x-page-header title="Telecaller Reports" subtitle="Per-day breakdown of leads, calls, reschedules and follow-ups by team member">
    <x-slot:actions>
        <a href="{{ route('client.reports.export', request()->query()) }}" class="btn btn-primary"><i class="bi bi-download me-1"></i>Download CSV</a>
    </x-slot:actions>
</x-page-header>

<div class="card p-3 mb-3 fade-up">
    <form class="toolbar" method="GET">
        <div class="d-flex flex-column">
            <label class="muted small mb-1">From</label>
            <input type="date" class="form-control" name="from" value="{{ $from->toDateString() }}">
        </div>
        <div class="d-flex flex-column">
            <label class="muted small mb-1">To</label>
            <input type="date" class="form-control" name="to" value="{{ $to->toDateString() }}">
        </div>
        <div class="d-flex flex-column" style="min-width:200px">
            <label class="muted small mb-1">Telecaller</label>
            <select class="form-select" name="telecaller_id">
                <option value="">All team members</option>
                @foreach($users as $id => $user)
                    <option value="{{ $id }}" @selected(request('telecaller_id') == $id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary align-self-end"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if(request()->anyFilled(['from','to','telecaller_id']))<a href="{{ route('client.reports.index') }}" class="btn btn-ghost align-self-end">Clear</a>@endif
    </form>
</div>

<div class="row g-3 mb-4 stagger">
    <div class="col-6 col-xl-3"><x-stat-card icon="bi-person-plus" label="Leads Assigned" :value="$totals['leads_assigned']" color="primary" /></div>
    <div class="col-6 col-xl-3"><x-stat-card icon="bi-telephone" label="Calls / Interactions" :value="$totals['interactions']" color="info" /></div>
    <div class="col-6 col-xl-3"><x-stat-card icon="bi-arrow-repeat" label="Rescheduled" :value="$totals['rescheduled']" color="warning" /></div>
    <div class="col-6 col-xl-3"><x-stat-card icon="bi-check2-circle" label="Follow-ups Completed" :value="$totals['completed']" color="success" /></div>
</div>

<div class="card fade-up">
    @if($rows->isEmpty())
        <x-empty-state icon="bi-bar-chart" title="No activity in this range" description="Try widening the date range or clearing filters." />
    @else
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                <tr>
                    <th>Date</th><th>Telecaller</th><th class="text-center">Leads Assigned</th><th class="text-center">Calls</th>
                    <th class="text-center">Rescheduled</th><th class="text-center">Completed</th><th class="text-center">Pending</th>
                    <th class="text-center">Missed</th><th class="text-center">Won</th><th class="text-center">Lost</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td class="row-sub">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                        <td class="row-title">{{ $row['telecaller'] }}</td>
                        <td class="text-center">{{ $row['leads_assigned'] }}</td>
                        <td class="text-center">{{ $row['interactions'] }}</td>
                        <td class="text-center">@if($row['rescheduled'] > 0)<span class="badge badge-warning">{{ $row['rescheduled'] }}</span>@else 0 @endif</td>
                        <td class="text-center">{{ $row['completed'] }}</td>
                        <td class="text-center">{{ $row['pending'] }}</td>
                        <td class="text-center">{{ $row['missed'] }}</td>
                        <td class="text-center">{{ $row['won'] }}</td>
                        <td class="text-center">{{ $row['lost'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
