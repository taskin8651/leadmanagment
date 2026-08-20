@extends('layouts.client')
@php
    $title = $lead->name;
@endphp
@section('content')

@if($duplicates->isNotEmpty())
    <div class="card fade-up mb-3 p-3 d-flex flex-row align-items-center gap-3" style="background:var(--warning-soft);border-color:#f6e2b8">
        <div class="icon-wrap" style="background:#fff;color:var(--warning);width:38px;height:38px;margin:0"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:13.5px">Possible duplicate</div>
            <div class="muted" style="font-size:12.3px">Same phone number as: @foreach($duplicates as $d)<a href="{{ route('client.leads.show',$d) }}">{{ $d->name }} ({{ $d->lead_number }})</a>@if(!$loop->last), @endif @endforeach</div>
        </div>
        @can('delete_leads')
        <form method="POST" action="{{ route('client.leads.merge',$lead) }}">
            @csrf
            <input type="hidden" name="duplicate_id" value="{{ $duplicates->first()->id }}">
            <button class="btn btn-light btn-sm" data-confirm="This moves all follow-ups and activity from {{ $duplicates->first()->name }} into this lead, then deletes the duplicate." data-confirm-title="Merge duplicate into this lead?">Merge into this lead</button>
        </form>
        @endcan
    </div>
@endif

<div class="card p-4 fade-up mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
            <x-avatar :name="$lead->name" size="lg" />
            <div>
                <div class="row-sub mb-1">{{ $lead->lead_number }}</div>
                <h1 class="fw-800 mb-1" style="font-size:20px">{{ $lead->name }}</h1>
                <div class="muted" style="font-size:13px">{{ $lead->company_name ?: 'No company' }}</div>
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <x-status-badge :status="$lead->status" />
                    <x-priority-badge :priority="$lead->priority" />
                    <span class="badge {{ $lead->score >= 70 ? 'bg-danger' : ($lead->score >= 50 ? 'bg-warning text-dark' : 'bg-secondary') }}" data-bs-toggle="tooltip" title="Lead score — suggested priority: {{ ucfirst($lead->suggestedPriority()) }}"><i class="bi bi-graph-up-arrow me-1"></i>Score {{ $lead->score }}</span>
                    @foreach($lead->tags as $tag)<span class="badge badge-soft">{{ $tag->name }}</span>@endforeach
                    @if($lead->first_response_at)
                        <span class="badge badge-success"><i class="bi bi-stopwatch me-1"></i>Responded in {{ $lead->created_at->diffForHumans($lead->first_response_at, true) }}</span>
                    @else
                        <span class="badge badge-warning"><i class="bi bi-hourglass-split me-1"></i>Awaiting first response — {{ $lead->created_at->diffForHumans(null, true) }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-none d-md-flex gap-2 flex-wrap">
            <a href="tel:{{ $lead->phone }}" class="btn btn-light" data-bs-toggle="tooltip" title="Call" data-log-interaction="call">Call</a>
            <a href="https://wa.me/{{ preg_replace('/\D/','',$lead->phone) }}" target="_blank" class="btn btn-light" data-bs-toggle="tooltip" title="Open in WhatsApp" data-log-interaction="whatsapp">WhatsApp</a>
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#whatsappModal"><i class="bi bi-send me-1"></i>Send WhatsApp</button>
            @if($lead->email)<button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#emailModal">Email</button>@endif
            @can('edit_leads')<button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#followUpModal"><i class="bi bi-calendar-plus me-1"></i>Schedule Follow-up</button>@endcan
            <a href="{{ route('client.invoices.create', ['lead_id' => $lead->id]) }}" class="btn btn-light"><i class="bi bi-receipt me-1"></i>Create Invoice</a>
            @can('edit_leads')<a href="{{ route('client.leads.edit',$lead) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>@endcan
            @can('delete_leads')
            <form method="POST" action="{{ route('client.leads.destroy',$lead) }}" data-confirm="This will permanently remove {{ $lead->name }} from your leads." data-confirm-title="Delete lead?">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            @endcan
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
        <span class="muted small">Log call outcome:</span>
        @foreach(\App\Http\Controllers\Client\LeadController::CALL_OUTCOMES as $outcome)
            <button type="button" class="btn btn-sm btn-light" data-log-outcome="{{ $outcome }}">{{ $outcome }}</button>
        @endforeach
    </div>
</div>

<ul class="nav tab-underline mb-3 fade-up" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button">Overview</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity" type="button">Activity <span class="badge badge-neutral ms-1">{{ $lead->activities->count() }}</span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-followups" type="button">Follow-ups <span class="badge badge-neutral ms-1">{{ $lead->followUps->count() }}</span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-files" type="button">Files <span class="badge badge-neutral ms-1">{{ $lead->attachments->count() }}</span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoices" type="button">Invoices <span class="badge badge-neutral ms-1">{{ $lead->invoices->count() }}</span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-notes" type="button">Notes</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card p-4 fade-up">
                    <h6 class="fw-bold mb-3">Overview</h6>
                    <div class="row g-4">
                        <div class="col-sm-4"><div class="muted small mb-1">Phone</div><div class="fw-semibold">{{ $lead->phone }}</div></div>
                        <div class="col-sm-4"><div class="muted small mb-1">Email</div><div class="fw-semibold">{{ $lead->email ?: '—' }}</div></div>
                        <div class="col-sm-4"><div class="muted small mb-1">Source</div><div class="fw-semibold">{{ $lead->source }}</div></div>
                        <div class="col-sm-4"><div class="muted small mb-1">Value</div><div class="fw-semibold">{{ $lead->estimated_value ? '₹'.number_format($lead->estimated_value,2) : '—' }}</div></div>
                        <div class="col-sm-4"><div class="muted small mb-1">Assigned To</div><div class="fw-semibold">{{ $lead->assignee->name ?? 'Unassigned' }}</div></div>
                        @foreach($fieldDefinitions as $def)
                            <div class="col-sm-4"><div class="muted small mb-1">{{ $def->label }}</div><div class="fw-semibold">{{ $lead->custom_fields[$def->key] ?? '—' }}</div></div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-4 fade-up h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Upcoming Follow-ups</h6>
                        @can('edit_leads')<button type="button" class="btn btn-ghost btn-sm p-0" data-bs-toggle="modal" data-bs-target="#followUpModal">+ Schedule</button>@endcan
                    </div>
                    @forelse($lead->followUps->where('status','pending')->sortBy('follow_up_at')->take(3) as $f)
                        <div class="border-bottom py-3">
                            <div class="fw-semibold" style="font-size:13.3px">{{ $f->subject ?: $f->type }}</div>
                            <div class="row-sub">{{ $f->follow_up_at->format('d M Y, h:i A') }}</div>
                        </div>
                    @empty
                        <p class="muted mb-0">No upcoming follow-ups.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-activity">
        <div class="card p-4 fade-up">
            <h6 class="fw-bold mb-4">Activity Timeline</h6>
            @if($lead->activities->isEmpty())
                <x-empty-state icon="bi-clock-history" title="No activity yet" description="Calls, notes, emails and status changes will show up here." />
            @else
                <div class="timeline">
                    @foreach($lead->activities->sortBy('created_at') as $i => $a)
                        <div class="timeline-item" style="animation-delay: {{ $i * 60 }}ms">
                            <div class="dot"><i class="bi {{ $a->icon }}"></i></div>
                            <div class="ttitle">{{ $a->description }}</div>
                            <div class="tmeta">{{ $a->user->name ?? 'System' }} · {{ $a->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="tab-followups">
        <div class="card fade-up">
            @if($lead->followUps->isEmpty())
                <x-empty-state icon="bi-calendar-x" title="No follow-ups" description="Follow-ups scheduled for this lead will appear here.">
                    @can('edit_leads')
                    <x-slot:actions><button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#followUpModal">+ Schedule Follow-up</button></x-slot:actions>
                    @endcan
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead><tr><th>Date</th><th>Type</th><th>Subject</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($lead->followUps->sortByDesc('follow_up_at') as $f)
                            <tr><td>{{ $f->follow_up_at->format('d M Y, h:i A') }}</td><td>{{ $f->type }}</td><td>{{ $f->subject ?: '—' }}</td><td><x-status-badge :status="$f->status" /></td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="tab-files">
        <div class="card p-4 fade-up mb-3">
            <form method="POST" action="{{ route('client.leads.attachments.store',$lead) }}" enctype="multipart/form-data" class="d-flex gap-2 flex-wrap align-items-center">
                @csrf
                <input class="form-control" type="file" name="file" required style="max-width:320px">
                <button class="btn btn-primary btn-sm">Upload</button>
                <span class="muted small">PDF, images, Office docs — max 5MB.</span>
            </form>
            @error('file')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </div>
        <div class="card fade-up">
            @if($lead->attachments->isEmpty())
                <x-empty-state icon="bi-paperclip" title="No files yet" description="Documents you upload for this lead will appear here." />
            @else
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead><tr><th>File</th><th>Uploaded By</th><th>Date</th><th></th></tr></thead>
                        <tbody>
                        @foreach($lead->attachments as $file)
                            <tr>
                                <td><i class="bi bi-file-earmark me-1"></i>{{ $file->original_name }} <span class="row-sub">({{ round($file->size / 1024, 1) }} KB)</span></td>
                                <td>{{ $file->uploader->name ?? 'System' }}</td>
                                <td class="row-sub">{{ $file->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-light" href="{{ route('client.leads.attachments.download',[$lead,$file]) }}"><i class="bi bi-download"></i></a>
                                    <form method="POST" action="{{ route('client.leads.attachments.destroy',[$lead,$file]) }}" class="d-inline" data-confirm="Remove {{ $file->original_name }}?" data-confirm-title="Delete file?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="tab-invoices">
        <div class="card fade-up">
            <div class="p-4 pb-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Invoices</h6>
                <a href="{{ route('client.invoices.create', ['lead_id' => $lead->id]) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Create Invoice</a>
            </div>
            @if($lead->invoices->isEmpty())
                <x-empty-state icon="bi-receipt" title="No invoices yet" description="Create an invoice when you're ready to bill this lead." />
            @else
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead><tr><th>Invoice</th><th>Date</th><th>Status</th><th class="text-end">Amount</th><th></th></tr></thead>
                        <tbody>
                        @foreach($lead->invoices as $inv)
                            <tr>
                                <td class="row-title">{{ $inv->invoice_number }}</td>
                                <td class="row-sub">{{ $inv->issue_date->format('d M Y') }}</td>
                                <td>
                                    @if($inv->status === 'paid')<span class="badge badge-success">Paid</span>
                                    @elseif($inv->status === 'cancelled')<span class="badge badge-neutral">Cancelled</span>
                                    @else<span class="badge badge-warning">Unpaid</span>@endif
                                </td>
                                <td class="text-end fw-bold">₹{{ number_format($inv->total, 2) }}</td>
                                <td class="text-end"><a class="btn btn-sm btn-light" href="{{ route('client.invoices.show',$inv) }}">View</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="tab-notes">
        <div class="card p-4 fade-up mb-3">
            <form method="POST" action="{{ route('client.leads.note',$lead) }}">
                @csrf
                <label class="form-label">Add a note</label>
                <textarea class="form-control mb-2" name="note" rows="3" placeholder="What happened?" required></textarea>
                <button class="btn btn-primary btn-sm">Add Note</button>
            </form>
        </div>
        <div class="card p-4 fade-up">
            <h6 class="fw-bold mb-2">Original Notes</h6>
            <p class="mb-0" style="white-space:pre-wrap">{{ $lead->notes ?: 'No notes added yet.' }}</p>
        </div>
    </div>
</div>

<div class="mobile-action-bar">
    <a href="tel:{{ $lead->phone }}" class="btn btn-light" data-log-interaction="call"><i class="bi bi-telephone d-block mb-1"></i>Call</a>
    <a href="https://wa.me/{{ preg_replace('/\D/','',$lead->phone) }}" target="_blank" class="btn btn-light" data-log-interaction="whatsapp"><i class="bi bi-whatsapp d-block mb-1"></i>WhatsApp</a>
    @can('edit_leads')<button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#followUpModal"><i class="bi bi-calendar-plus d-block mb-1"></i>Follow-up</button>@endcan
    @can('edit_leads')<a href="{{ route('client.leads.edit',$lead) }}" class="btn btn-primary"><i class="bi bi-pencil d-block mb-1"></i>Edit</a>@endcan
</div>

@can('edit_leads')
<div class="modal fade" id="followUpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Schedule Follow-up</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ route('client.leads.follow-ups.store',$lead) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Date & Time <span class="req">*</span></label>
                    <input class="form-control mb-3" type="datetime-local" name="follow_up_at" required>
                    <label class="form-label">Type <span class="req">*</span></label>
                    <select class="form-select mb-3" name="type">
                        <option>Call</option>
                        <option>WhatsApp</option>
                        <option>Email</option>
                        <option>Meeting</option>
                        <option>Other</option>
                    </select>
                    <label class="form-label">Subject</label>
                    <input class="form-control mb-3" name="subject" placeholder="e.g. Discuss pricing">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<div class="modal fade" id="whatsappModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Send WhatsApp to {{ $lead->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ route('client.leads.whatsapp',$lead) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="message" rows="5" maxlength="1000" required placeholder="Type your message…"></textarea>
                    <div class="row-sub mt-2">Sent via 11za to {{ $lead->phone }}.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($lead->email)
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Email {{ $lead->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ route('client.leads.email',$lead) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Subject</label>
                    <input class="form-control mb-3" name="subject" required>
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="body" rows="6" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-log-interaction]').forEach((el) => {
    el.addEventListener('click', () => {
        fetch(@json(route('client.leads.interaction',$lead)), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ type: el.dataset.logInteraction }),
        });
    });
});

document.querySelectorAll('[data-log-outcome]').forEach((btn) => {
    btn.addEventListener('click', () => {
        btn.disabled = true;
        fetch(@json(route('client.leads.interaction',$lead)), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ type: 'call', outcome: btn.dataset.logOutcome }),
        }).then((r) => {
            if (!r.ok) throw new Error();
            showToast('Call outcome logged: ' + btn.dataset.logOutcome, 'success');
        }).catch(() => showToast('Could not log call outcome.', 'error'))
          .finally(() => { btn.disabled = false; });
    });
});
</script>
@endpush
