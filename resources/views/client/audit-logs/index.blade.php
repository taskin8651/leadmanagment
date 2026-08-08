@extends('layouts.client')
@php($title = 'Audit Log')
@section('content')

<x-page-header title="Audit Log" subtitle="Security-relevant actions across your account — logins, staff, leads, invoices" />

<div class="card p-3 mb-3 fade-up">
    <form class="toolbar" method="GET">
        <div class="search-input"><i class="bi bi-search"></i><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search description…"></div>
        <select class="form-select" style="max-width:220px" name="action">
            <option value="">All actions</option>
            @foreach($actions as $a)<option value="{{ $a }}" @selected(request('action')==$a)>{{ ucwords(str_replace(['_','.'],[' ',' — '],$a)) }}</option>@endforeach
        </select>
        <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if(request()->anyFilled(['search','action']))<a href="{{ route('client.audit-logs.index') }}" class="btn btn-ghost">Clear filters</a>@endif
    </form>
</div>

@if($logs->isEmpty())
    <div class="card">
        <x-empty-state icon="bi-shield-lock" title="No audit entries yet" description="Logins, staff changes, and destructive actions will show up here." />
    </div>
@else
    <div class="card fade-up">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead><tr><th>When</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="row-sub">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td><span class="badge badge-neutral">{{ $log->action }}</span></td>
                        <td>{{ $log->description }}</td>
                        <td class="row-sub">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $logs->links() }}</div>
    </div>
@endif

@endsection
