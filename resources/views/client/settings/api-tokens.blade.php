@extends('layouts.client')
@php($title = 'API Tokens')
@section('content')

<x-page-header title="API Tokens" subtitle="Connect your own apps or scripts to your leads" />

@if(session('plainTextToken'))
    <div class="card p-4 fade-up mb-3" style="background:var(--success-soft);border-color:#bfead0">
        <div class="fw-bold mb-2"><i class="bi bi-key me-1"></i>Your new token (copy it now — it won't be shown again)</div>
        <div class="input-icon-group"><i class="bi bi-clipboard"></i><input type="text" class="form-control" readonly value="{{ session('plainTextToken') }}" id="newToken"></div>
        <button type="button" class="btn btn-light btn-sm mt-2" onclick="navigator.clipboard.writeText(document.getElementById('newToken').value).then(() => showToast('Copied.', 'success'))">Copy</button>
    </div>
@endif

<div class="card p-4 form-section fade-up mb-3">
    <div class="form-section-title"><i class="bi bi-plus-circle"></i>Create New Token</div>
    <div class="form-section-sub">Give it a name so you remember what it's for (e.g. "Zapier", "My Script")</div>
    <form method="POST" action="{{ route('client.api-tokens.store') }}" class="d-flex gap-2">
        @csrf
        <input class="form-control" name="name" placeholder="Token name" required>
        <button class="btn btn-primary">Generate Token</button>
    </form>
</div>

<div class="card fade-up">
    @if($tokens->isEmpty())
        <x-empty-state icon="bi-key" title="No API tokens yet" description="Create one above to access your leads via the API." />
    @else
        <div class="table-responsive d-none d-md-block">
            <table class="table table-modern mb-0">
                <thead><tr><th>Name</th><th>Last Used</th><th>Created</th><th></th></tr></thead>
                <tbody>
                @foreach($tokens as $t)
                    <tr>
                        <td class="row-title">{{ $t->name }}</td>
                        <td class="row-sub">{{ $t->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="row-sub">{{ $t->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('client.api-tokens.destroy',$t->id) }}" data-confirm="Any app using this token will stop working immediately." data-confirm-title="Revoke this token?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Revoke</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-md-none d-flex flex-column gap-2 p-3">
            @foreach($tokens as $t)
                <div class="entity-card">
                    <div class="top">
                        <div class="flex-grow-1"><div class="row-title">{{ $t->name }}</div><div class="row-sub">Created {{ $t->created_at->format('d M Y') }} · Used {{ $t->last_used_at?->diffForHumans() ?? 'Never' }}</div></div>
                    </div>
                    <div class="actions-row">
                        <form method="POST" action="{{ route('client.api-tokens.destroy',$t->id) }}" class="w-100" data-confirm="Any app using this token will stop working immediately." data-confirm-title="Revoke this token?">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm w-100">Revoke</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
