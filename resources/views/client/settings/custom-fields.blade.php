@extends('layouts.client')
@php($title = 'Custom Fields')
@section('content')

<x-page-header title="Custom Fields" subtitle="Add your own fields to the lead form" />

<div class="card p-4 form-section fade-up mb-3">
    <div class="form-section-title"><i class="bi bi-plus-circle"></i>Add Field</div>
    <form method="POST" action="{{ route('client.custom-fields.store') }}" class="row g-2">
        @csrf
        <div class="col-md-6"><input class="form-control" name="label" placeholder="Field label (e.g. Budget, Preferred City)" required></div>
        <div class="col-md-4">
            <select class="form-select" name="type">
                <option value="text">Text</option>
                <option value="number">Number</option>
                <option value="date">Date</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
    </form>
</div>

<div class="card fade-up">
    @if($fields->isEmpty())
        <x-empty-state icon="bi-input-cursor-text" title="No custom fields yet" description="Fields you add here will show up on your lead form." />
    @else
        <div class="table-responsive d-none d-md-block">
            <table class="table table-modern mb-0">
                <thead><tr><th>Label</th><th>Type</th><th></th></tr></thead>
                <tbody>
                @foreach($fields as $f)
                    <tr>
                        <td class="row-title">{{ $f->label }}</td>
                        <td><span class="badge badge-neutral">{{ ucfirst($f->type) }}</span></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('client.custom-fields.destroy',$f) }}" data-confirm="Existing lead data for this field will remain but the field will no longer show on the form." data-confirm-title="Remove this field?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-md-none d-flex flex-column gap-2 p-3">
            @foreach($fields as $f)
                <div class="entity-card">
                    <div class="top">
                        <div class="flex-grow-1"><div class="row-title">{{ $f->label }}</div></div>
                        <span class="badge badge-neutral">{{ ucfirst($f->type) }}</span>
                    </div>
                    <div class="actions-row">
                        <form method="POST" action="{{ route('client.custom-fields.destroy',$f) }}" class="w-100" data-confirm="Existing lead data for this field will remain but the field will no longer show on the form." data-confirm-title="Remove this field?">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm w-100">Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
