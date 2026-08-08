@extends('layouts.client')
@php($title = 'Leads')
@section('content')

<x-page-header title="Leads" subtitle="Manage and convert your prospects">
    <x-slot:actions>
        <a href="{{ route('client.leads.export', request()->query()) }}" class="btn btn-light"><i class="bi bi-download me-1"></i>Export</a>
        @can('delete_leads')
        <a href="{{ route('client.leads.trash') }}" class="btn btn-light"><i class="bi bi-trash3 me-1"></i>Trash</a>
        @endcan
        @can('create_leads')
        <a href="{{ route('client.leads.import') }}" class="btn btn-light"><i class="bi bi-file-earmark-arrow-up me-1"></i>Import</a>
        <a href="{{ route('client.leads.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Lead</a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="kpi-strip mb-3 fade-up">
    @foreach(['total'=>['All','bi-collection'],'new'=>['New','bi-stars'],'contacted'=>['Contacted','bi-telephone'],'qualified'=>['Qualified','bi-patch-check'],'follow-up'=>['Follow-up','bi-calendar-event'],'won'=>['Won','bi-trophy'],'lost'=>['Lost','bi-x-circle']] as $key => [$label, $icon])
        <a href="{{ route('client.leads.index', array_merge(request()->except(['status','page']), $key !== 'total' ? ['status' => $key] : [])) }}" class="kpi-mini text-decoration-none {{ (request('status') === $key) || ($key === 'total' && !request('status')) ? 'active' : '' }}">
            <div class="n">{{ $kpis[$key] }}</div>
            <div class="l"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</div>
        </a>
    @endforeach
</div>

<div class="card p-3 mb-3 fade-up">
    <form class="toolbar" method="GET">
        <div class="search-input"><i class="bi bi-search"></i><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search leads by name, phone, email…"></div>
        @if($tags->isNotEmpty())
        <select class="form-select" style="max-width:170px" name="tag">
            <option value="">All Tags</option>
            @foreach($tags as $t)<option value="{{ $t->id }}" @selected(request('tag')==$t->id)>{{ $t->name }}</option>@endforeach
        </select>
        @endif
        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if(request()->anyFilled(['search','status']))<a href="{{ route('client.leads.index') }}" class="btn btn-ghost">Clear filters</a>@endif
    </form>
</div>

@if($leads->isEmpty())
    <div class="card">
        <x-empty-state icon="bi-people" title="No leads found" description="No leads match your current filters.">
            <x-slot:actions>
                <a href="{{ route('client.leads.index') }}" class="btn btn-light">Clear Filters</a>
                @can('create_leads')<a href="{{ route('client.leads.create') }}" class="btn btn-primary">+ Add Lead</a>@endcan
            </x-slot:actions>
        </x-empty-state>
    </div>
@else
    <form id="bulk-form" method="POST" action="{{ route('client.leads.bulk') }}" class="d-none"></form>

    @if(auth()->user()->can('edit_leads') || auth()->user()->can('delete_leads'))
        <div id="bulk-bar" class="bulk-bar d-none">
            <span><span data-role="bulk-count">0</span> selected</span>
            <button type="button" id="bulk-export" class="btn btn-light ms-auto" data-export-url="{{ route('client.leads.export') }}"><i class="bi bi-download me-1"></i>Export</button>
            @can('edit_leads')
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">Change Status</button>
                <ul class="dropdown-menu">
                    @foreach(['new','contacted','qualified','follow-up','won','lost'] as $s)
                        <li><button type="button" class="dropdown-item" data-bulk-status="{{ $s }}">{{ ucfirst($s) }}</button></li>
                    @endforeach
                </ul>
            </div>
            @endcan
            @if($assignees->isNotEmpty())
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">Reassign</button>
                <ul class="dropdown-menu">
                    @foreach($assignees as $a)
                        <li><button type="button" class="dropdown-item" data-bulk-reassign="{{ $a->id }}">{{ $a->name }}</button></li>
                    @endforeach
                </ul>
            </div>
            @endif
            @can('delete_leads')
            <button type="button" class="btn btn-light text-danger" data-bulk-delete><i class="bi bi-trash me-1"></i>Delete</button>
            @endcan
        </div>
    @endif

    <div class="card d-none d-md-block fade-up">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead><tr><th style="width:36px"><input type="checkbox" class="form-check-input" id="bulk-select-all" aria-label="Select all leads"></th><th>Lead</th><th>Contact</th><th>Source</th><th>Status</th><th>Priority</th><th>Score</th><th>Assigned</th><th>Follow-up</th><th></th></tr></thead>
                <tbody>
                @foreach($leads as $lead)
                    <tr>
                        <td><input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $lead->id }}" aria-label="Select {{ $lead->name }}"></td>
                        <td><div class="d-flex align-items-center gap-2"><x-avatar :name="$lead->name" size="sm" /><div><div class="row-title">{{ $lead->name }}</div><div class="row-sub">{{ $lead->lead_number }}@foreach($lead->tags as $tag) <span class="badge badge-soft">{{ $tag->name }}</span>@endforeach</div></div></div></td>
                        <td>{{ $lead->phone }}<div class="row-sub">{{ $lead->email }}</div></td>
                        <td>{{ $lead->source }}</td>
                        <td><x-status-badge :status="$lead->status" /></td>
                        <td><x-priority-badge :priority="$lead->priority" /></td>
                        <td><span class="badge {{ $lead->score >= 70 ? 'bg-danger' : ($lead->score >= 50 ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $lead->score }}</span></td>
                        <td>@if($lead->assignee)<span class="badge badge-neutral">{{ $lead->assignee->name }}</span>@else<span class="row-sub">Unassigned</span>@endif</td>
                        <td class="row-sub">{{ $lead->next_follow_up_at?->format('d M, h:i A') ?? '—' }}</td>
                        <td class="text-end"><a class="btn btn-sm btn-light" href="{{ route('client.leads.show',$lead) }}">View</a> @can('edit_leads')<a class="btn btn-sm btn-light" href="{{ route('client.leads.edit',$lead) }}">Edit</a>@endcan</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $leads->links() }}</div>
    </div>

    <div class="d-md-none d-flex flex-column gap-2 stagger">
        @foreach($leads as $lead)
            <div class="entity-card">
                <div class="top">
                    <x-avatar :name="$lead->name" size="md" />
                    <div class="flex-grow-1"><div class="row-title">{{ $lead->name }}</div><div class="row-sub">{{ $lead->lead_number }} · {{ $lead->phone }}</div></div>
                    <x-priority-badge :priority="$lead->priority" />
                </div>
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <x-status-badge :status="$lead->status" />
                    @if($lead->next_follow_up_at)
                        <span class="badge badge-neutral"><i class="bi bi-calendar-event me-1"></i>{{ $lead->next_follow_up_at->format('d M') }}</span>
                    @endif
                    @if($lead->assignee)
                        <span class="badge badge-neutral"><i class="bi bi-person me-1"></i>{{ $lead->assignee->name }}</span>
                    @endif
                </div>
                <div class="actions-row">
                    <a class="btn btn-light btn-sm" href="{{ route('client.leads.show',$lead) }}">View</a>
                    @can('edit_leads')<a class="btn btn-light btn-sm" href="{{ route('client.leads.edit',$lead) }}">Edit</a>@endcan
                    <a class="btn btn-light btn-sm" href="tel:{{ $lead->phone }}"><i class="bi bi-telephone"></i></a>
                    <a class="btn btn-light btn-sm" target="_blank" href="https://wa.me/{{ preg_replace('/\D/','',$lead->phone) }}"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
        @endforeach
        <div>{{ $leads->links() }}</div>
    </div>
@endif

@endsection
