@extends('layouts.client')
@php($title = 'Follow-ups')
@section('content')

<x-page-header title="Follow-ups" subtitle="Track pending and completed follow-ups">
    <x-slot:actions><a href="{{ route('client.follow-ups.calendar') }}" class="btn btn-light"><i class="bi bi-calendar3 me-1"></i>Calendar View</a></x-slot:actions>
</x-page-header>

<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('client.follow-ups.index', array_merge(request()->except('when'), ['when' => 'today'])) }}" class="btn btn-sm {{ request('when')=='today' ? 'btn-primary' : 'btn-light' }}"><i class="bi bi-calendar-day me-1"></i>Call Today</a>
    <a href="{{ route('client.follow-ups.index', array_merge(request()->except('when'), ['when' => 'overdue'])) }}" class="btn btn-sm {{ request('when')=='overdue' ? 'btn-primary' : 'btn-light' }}"><i class="bi bi-alarm me-1"></i>Overdue</a>
    <a href="{{ route('client.follow-ups.index', array_merge(request()->except('when'), ['when' => 'upcoming'])) }}" class="btn btn-sm {{ request('when')=='upcoming' ? 'btn-primary' : 'btn-light' }}"><i class="bi bi-calendar-week me-1"></i>Upcoming</a>
    <a href="{{ route('client.follow-ups.index') }}" class="btn btn-sm {{ request('when') ? 'btn-light' : 'btn-primary' }}">All</a>
</div>

<div class="card p-3 mb-3 fade-up">
    <form class="d-flex gap-2">
        @if(request('when'))<input type="hidden" name="when" value="{{ request('when') }}">@endif
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
                    @php($isToday = $f->follow_up_at->isToday())
                    <tr style="{{ $overdue ? 'background:var(--warning-soft)' : '' }}">
                        <td><a class="row-title text-decoration-none" href="{{ route('client.leads.show',$f->lead) }}">{{ $f->lead->name }}</a><div class="row-sub">{{ $f->lead->phone }}</div></td>
                        <td>
                            {{ $f->follow_up_at->format('d M Y, h:i A') }}
                            @if($overdue)<div class="row-sub" style="color:var(--warning)">Overdue</div>@endif
                            @if($isToday && $f->status==='pending')<div class="row-sub" style="color:var(--primary)"><i class="bi bi-telephone me-1"></i>Call today</div>@endif
                            @if($f->reschedule_count > 0)<div class="mt-1"><span class="badge badge-warning">Rescheduled ×{{ $f->reschedule_count }}</span></div>@endif
                        </td>
                        <td>{{ $f->type }}</td>
                        <td><x-status-badge :status="$f->status" /></td>
                        <td class="text-end">
                            @if($f->status==='pending')
                            <div class="d-flex gap-1 justify-content-end">
                                @can('edit_leads')
                                <button type="button" class="btn btn-sm btn-light" data-role="reschedule-btn" data-action="{{ route('client.follow-ups.reschedule',$f) }}" data-current="{{ $f->follow_up_at->format('Y-m-d\TH:i') }}" data-lead="{{ $f->lead->name }}"><i class="bi bi-arrow-repeat me-1"></i>Reschedule</button>
                                @endcan
                                @can('complete_followups')
                                <form method="POST" action="{{ route('client.follow-ups.complete',$f) }}" data-role="complete-form">@csrf
                                    <button class="btn btn-sm btn-primary" data-role="complete-btn"><i class="bi bi-check2 me-1"></i>Complete</button>
                                </form>
                                @endcan
                            </div>
                            @endif
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
                @php($isToday = $f->follow_up_at->isToday())
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
                        @if($isToday && $f->status==='pending')<span class="badge badge-primary"><i class="bi bi-telephone me-1"></i>Call today</span>@endif
                        @if($f->reschedule_count > 0)<span class="badge badge-warning">Rescheduled ×{{ $f->reschedule_count }}</span>@endif
                    </div>
                    @if($f->status==='pending')
                        <div class="actions-row d-flex gap-2">
                            @can('edit_leads')
                            <button type="button" class="btn btn-light btn-sm flex-grow-1" data-role="reschedule-btn" data-action="{{ route('client.follow-ups.reschedule',$f) }}" data-current="{{ $f->follow_up_at->format('Y-m-d\TH:i') }}" data-lead="{{ $f->lead->name }}"><i class="bi bi-arrow-repeat me-1"></i>Reschedule</button>
                            @endcan
                            @can('complete_followups')
                            <form method="POST" action="{{ route('client.follow-ups.complete',$f) }}" data-role="complete-form" class="flex-grow-1">@csrf
                                <button class="btn btn-primary btn-sm w-100" data-role="complete-btn"><i class="bi bi-check2 me-1"></i>Complete</button>
                            </form>
                            @endcan
                        </div>
                    @endif
                </div>
            @endforeach
            <div>{{ $followUps->links() }}</div>
        </div>
    @endif
</div>

<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px">
            <form method="POST" id="rescheduleForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h6 class="fw-bold mb-0">Reschedule follow-up — <span id="rescheduleLeadName"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="muted small mb-1">New date &amp; time</label>
                    <input type="datetime-local" class="form-control mb-3" name="follow_up_at" id="rescheduleDate" required>
                    <label class="muted small mb-1">Reason (optional)</label>
                    <textarea class="form-control" name="reason" rows="2" placeholder="e.g. Customer asked to call tomorrow"></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i>Reschedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const modalEl = document.getElementById('rescheduleModal');
    const modal = new bootstrap.Modal(modalEl);
    document.querySelectorAll('[data-role="reschedule-btn"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('rescheduleForm').action = btn.dataset.action;
            document.getElementById('rescheduleDate').value = btn.dataset.current;
            document.getElementById('rescheduleLeadName').textContent = btn.dataset.lead;
            modal.show();
        });
    });
})();

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
