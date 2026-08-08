@extends('layouts.client')
@php($title = 'Leads Trash')
@section('content')

<x-page-header title="Leads Trash" subtitle="Deleted leads — restore or permanently remove">
    <x-slot:actions><a href="{{ route('client.leads.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back to Leads</a></x-slot:actions>
</x-page-header>

@if($leads->isEmpty())
    <div class="card">
        <x-empty-state icon="bi-trash" title="Trash is empty" description="Deleted leads will appear here." />
    </div>
@else
    <div class="card fade-up">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-modern mb-0">
                <thead><tr><th>Lead</th><th>Deleted</th><th></th></tr></thead>
                <tbody>
                @foreach($leads as $lead)
                    <tr>
                        <td><div class="row-title">{{ $lead->name }}</div><div class="row-sub">{{ $lead->lead_number }}</div></td>
                        <td class="row-sub">{{ $lead->deleted_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('client.leads.restore',$lead->id) }}" class="d-inline">@csrf<button class="btn btn-sm btn-light"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button></form>
                            @role('Admin')
                            <form method="POST" action="{{ route('client.leads.force-delete',$lead->id) }}" class="d-inline" data-confirm="This permanently deletes {{ $lead->name }}. This cannot be undone." data-confirm-title="Permanently delete?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete Forever</button></form>
                            @endrole
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 d-none d-md-block">{{ $leads->links() }}</div>

        <div class="d-md-none d-flex flex-column gap-2 p-3">
            @foreach($leads as $lead)
                <div class="entity-card">
                    <div class="top">
                        <div class="flex-grow-1"><div class="row-title">{{ $lead->name }}</div><div class="row-sub">{{ $lead->lead_number }} · Deleted {{ $lead->deleted_at->diffForHumans() }}</div></div>
                    </div>
                    <div class="actions-row">
                        <form method="POST" action="{{ route('client.leads.restore',$lead->id) }}" class="w-100">@csrf<button class="btn btn-light btn-sm w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button></form>
                        @role('Admin')
                        <form method="POST" action="{{ route('client.leads.force-delete',$lead->id) }}" class="w-100" data-confirm="This permanently deletes {{ $lead->name }}. This cannot be undone." data-confirm-title="Permanently delete?">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm w-100">Delete Forever</button></form>
                        @endrole
                    </div>
                </div>
            @endforeach
            <div>{{ $leads->links() }}</div>
        </div>
    </div>
@endif

@endsection
