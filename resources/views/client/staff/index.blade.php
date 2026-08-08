@extends('layouts.client')
@php($title = 'Staff')
@section('content')

<x-page-header title="Staff" subtitle="Team members who can access your CRM">
    <x-slot:actions><a href="{{ route('client.staff.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Staff</a></x-slot:actions>
</x-page-header>

@if($staff->isEmpty())
    <div class="card">
        <x-empty-state icon="bi-people" title="No staff members yet" description="Invite your team — staff and telecallers only see your account's own leads.">
            <x-slot:actions><a href="{{ route('client.staff.create') }}" class="btn btn-primary">+ Add Staff</a></x-slot:actions>
        </x-empty-state>
    </div>
@else
    <div class="card fade-up">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-modern mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Access</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($staff as $member)
                    <tr>
                        <td><div class="d-flex align-items-center gap-2"><x-avatar :name="$member->name" size="sm" /><span class="row-title">{{ $member->name }}</span></div></td>
                        <td>{{ $member->email }}</td>
                        <td><span class="badge badge-soft">{{ $member->roles->first()->name ?? '—' }}</span></td>
                        <td>
                            @forelse($member->permissions as $perm)
                                <span class="badge badge-neutral me-1 mb-1">{{ \App\Support\ClientPermissions::ALL[$perm->name] ?? $perm->name }}</span>
                            @empty
                                <span class="row-sub">No access granted</span>
                            @endforelse
                        </td>
                        <td>
                            @if($member->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-neutral">Suspended</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-light" href="{{ route('client.staff.edit',$member) }}">Edit</a>
                            <form method="POST" action="{{ route('client.staff.toggle-active',$member) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-light">{{ $member->is_active ? 'Suspend' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('client.staff.destroy',$member) }}" class="d-inline" data-confirm="This will remove {{ $member->name }}'s access to your CRM." data-confirm-title="Remove staff member?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-md-none d-flex flex-column gap-2 p-3">
            @foreach($staff as $member)
                <div class="entity-card">
                    <div class="top">
                        <x-avatar :name="$member->name" size="md" />
                        <div class="flex-grow-1"><div class="row-title">{{ $member->name }}</div><div class="row-sub">{{ $member->email }}</div></div>
                        @if($member->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-neutral">Suspended</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <span class="badge badge-soft">{{ $member->roles->first()->name ?? '—' }}</span>
                        @forelse($member->permissions as $perm)
                            <span class="badge badge-neutral">{{ \App\Support\ClientPermissions::ALL[$perm->name] ?? $perm->name }}</span>
                        @empty
                            <span class="row-sub">No access granted</span>
                        @endforelse
                    </div>
                    <div class="actions-row">
                        <a class="btn btn-light btn-sm" href="{{ route('client.staff.edit',$member) }}">Edit</a>
                        <form method="POST" action="{{ route('client.staff.toggle-active',$member) }}" class="w-100">
                            @csrf
                            <button class="btn btn-light btn-sm w-100">{{ $member->is_active ? 'Suspend' : 'Activate' }}</button>
                        </form>
                        <form method="POST" action="{{ route('client.staff.destroy',$member) }}" data-confirm="This will remove {{ $member->name }}'s access to your CRM." data-confirm-title="Remove staff member?">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@endsection
