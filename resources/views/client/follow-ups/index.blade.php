@extends('layouts.client')
@php($title = 'Follow-ups')
@section('content')

<x-page-header title="Follow-ups" subtitle="Track pending and completed follow-ups">
    <x-slot:actions><a href="{{ route('client.follow-ups.calendar') }}" class="btn btn-light"><i class="bi bi-calendar3 me-1"></i>Calendar View</a></x-slot:actions>
</x-page-header>

<div class="card p-3 mb-3 fade-up">
    <form class="d-flex gap-2">
        <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['pending','completed','cancelled','missed'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
    </form>
</div>

<div class="card fade-up">
    @if($followUps->isEmpty())
        <x-empty-state icon="bi-calendar-x" title="No follow-ups" description="Follow-ups scheduled for your leads will appear here." />
    @else
        <div class="table-responsive d-none d-md-block">
            <table class="table table-modern mb-0">
                <thead><tr><th>Lead</th><th>Date</th><th>Type</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($followUps as $f)
                    @php($overdue = $f->status === 'pending' && $f->follow_up_at->isPast())
                    <tr style="{{ $overdue ? 'background:var(--warning-soft)' : '' }}">
                        <td><a class="row-title text-decoration-none" href="{{ route('client.leads.show',$f->lead) }}">{{ $f->lead->name }}</a><div class="row-sub">{{ $f->lead->phone }}</div></td>
                        <td>{{ $f->follow_up_at->format('d M Y, h:i A') }}@if($overdue)<div class="row-sub" style="color:var(--warning)">Overdue</div>@endif</td>
                        <td>{{ $f->type }}</td>
                        <td><x-status-badge :status="$f->status" /></td>
                        <td class="text-end">
                            @can('complete_followups')
                            @if($f->status==='pending')
                                <form method="POST" action="{{ route('client.follow-ups.complete',$f) }}" data-role="complete-form">@csrf
                                    <button class="btn btn-sm btn-primary" data-role="complete-btn"><i class="bi bi-check2 me-1"></i>Complete</button>
                                </form>
                            @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 d-none d-md-block">{{ $followUps->links() }}</div>

        <div class="d-md-none d-flex flex-column gap-2 p-3">
            @foreach($followUps as $f)
                @php($overdue = $f->status === 'pending' && $f->follow_up_at->isPast())
                <div class="entity-card" @if($overdue) style="border-color:#f6e2b8;background:var(--warning-soft)" @endif>
                    <div class="top">
                        <div class="flex-grow-1">
                            <a class="row-title text-decoration-none d-block" href="{{ route('client.leads.show',$f->lead) }}">{{ $f->lead->name }}</a>
                            <div class="row-sub">{{ $f->lead->phone }}</div>
                        </div>
                        <x-status-badge :status="$f->status" />
                    </div>
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <span class="badge badge-neutral"><i class="bi bi-calendar-event me-1"></i>{{ $f->follow_up_at->format('d M, h:i A') }}</span>
                        <span class="badge badge-neutral">{{ $f->type }}</span>
                        @if($overdue)<span class="badge badge-warning">Overdue</span>@endif
                    </div>
                    @can('complete_followups')
                    @if($f->status==='pending')
                        <div class="actions-row">
                            <form method="POST" action="{{ route('client.follow-ups.complete',$f) }}" data-role="complete-form" class="w-100">@csrf
                                <button class="btn btn-primary btn-sm w-100" data-role="complete-btn"><i class="bi bi-check2 me-1"></i>Complete</button>
                            </form>
                        </div>
                    @endif
                    @endcan
                </div>
            @endforeach
            <div>{{ $followUps->links() }}</div>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-role="complete-form"]').forEach((form) => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = form.querySelector('[data-role="complete-btn"]');
        btn.disabled = true;
        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
        }).then((r) => {
            if (!r.ok) throw new Error();
            btn.outerHTML = '<span class="badge badge-success"><i class="bi bi-check-lg me-1"></i>Completed</span>';
            showToast('Follow-up completed.', 'success');
        }).catch(() => { btn.disabled = false; showToast('Could not complete follow-up.', 'error'); });
    });
});
</script>
@endpush
