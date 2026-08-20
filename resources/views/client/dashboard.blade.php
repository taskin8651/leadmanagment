@extends('layouts.client')
@php($title = 'Dashboard')
@section('content')

<div class="card fade-up mb-4 p-4" style="background:linear-gradient(135deg,#0f172a,#302b63 130%);border:none;color:#fff">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            @php($hour = now()->hour)
            <h1 class="fw-800 mb-1" style="font-size:22px">{{ $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening') }}, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <p class="mb-0" style="color:#c7cbe8;font-size:13.5px">Here's what's happening with your leads today.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @can('create_leads')<a href="{{ route('client.leads.create') }}" class="btn btn-light btn-sm fw-bold"><i class="bi bi-plus-lg me-1"></i>Add Lead</a>@endcan
            <a href="{{ route('client.follow-ups.index') }}" class="btn btn-sm fw-bold" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="bi bi-calendar-check me-1"></i>Follow-ups</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4 stagger">
    <div class="col-6 col-xl-3"><x-stat-card icon="bi-people" label="Total Leads" :value="$leads" color="primary" :delta="$leadsDelta['value'] ?? null" :deltaUp="$leadsDelta['up'] ?? true" /></div>
    <div class="col-6 col-xl-3"><x-stat-card icon="bi-check2-circle" label="Won Leads" :value="$wonLeads" color="success" :delta="$wonDelta['value'] ?? null" :deltaUp="$wonDelta['up'] ?? true" /></div>
    <div class="col-6 col-xl-3"><x-stat-card icon="bi-calendar-check" label="Today's Follow-ups" :value="$todayFollowUps" color="info" /></div>
    <div class="col-6 col-xl-3"><x-stat-card icon="bi-alarm" label="Overdue Follow-ups" :value="$overdueFollowUps" color="{{ $overdueFollowUps > 0 ? 'warning' : 'success' }}" /></div>
</div>

<div class="card fade-up mb-4">
    <div class="p-4 pb-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold mb-1"><i class="bi bi-telephone-outbound me-1"></i>Today's Calls</h6>
            <div class="muted small">Leads scheduled — including rescheduled ones — for you to call today</div>
        </div>
        <a href="{{ route('client.follow-ups.index', ['when' => 'today']) }}" class="btn btn-ghost btn-sm">View all</a>
    </div>
    @if($todaysCalls->isEmpty())
        <x-empty-state icon="bi-calendar2-check" title="No calls scheduled for today" description="Follow-ups due today will show up here so you never miss a call." />
    @else
        <div class="table-responsive d-none d-md-block">
            <table class="table table-modern mb-0">
                <thead><tr><th>Lead</th><th>Time</th><th>Type</th><th></th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @foreach($todaysCalls as $f)
                    @php($overdueToday = $f->follow_up_at->isPast())
                    <tr style="{{ $overdueToday ? 'background:var(--warning-soft)' : '' }}">
                        <td><a class="row-title text-decoration-none" href="{{ route('client.leads.show',$f->lead) }}">{{ $f->lead->name }}</a><div class="row-sub">{{ $f->lead->phone }}</div></td>
                        <td>{{ $f->follow_up_at->format('h:i A') }}</td>
                        <td>{{ $f->type }}</td>
                        <td>@if($f->reschedule_count > 0)<span class="badge badge-warning">Rescheduled ×{{ $f->reschedule_count }}</span>@endif</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="tel:{{ $f->lead->phone }}" class="btn btn-sm btn-light"><i class="bi bi-telephone"></i></a>
                                @can('edit_leads')
                                <button type="button" class="btn btn-sm btn-light" data-role="reschedule-btn" data-action="{{ route('client.follow-ups.reschedule',$f) }}" data-current="{{ $f->follow_up_at->format('Y-m-d\TH:i') }}" data-lead="{{ $f->lead->name }}"><i class="bi bi-arrow-repeat"></i></button>
                                @endcan
                                @can('complete_followups')
                                <form method="POST" action="{{ route('client.follow-ups.complete',$f) }}" data-role="complete-form">@csrf
                                    <button class="btn btn-sm btn-primary" data-role="complete-btn"><i class="bi bi-check2"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-md-none d-flex flex-column gap-2 p-3">
            @foreach($todaysCalls as $f)
                <div class="entity-card">
                    <div class="top">
                        <div class="flex-grow-1">
                            <a class="row-title text-decoration-none d-block" href="{{ route('client.leads.show',$f->lead) }}">{{ $f->lead->name }}</a>
                            <div class="row-sub">{{ $f->lead->phone }} · {{ $f->follow_up_at->format('h:i A') }}</div>
                        </div>
                        @if($f->reschedule_count > 0)<span class="badge badge-warning">×{{ $f->reschedule_count }}</span>@endif
                    </div>
                    <div class="actions-row d-flex gap-2">
                        <a href="tel:{{ $f->lead->phone }}" class="btn btn-light btn-sm flex-grow-1"><i class="bi bi-telephone me-1"></i>Call</a>
                        @can('edit_leads')
                        <button type="button" class="btn btn-light btn-sm flex-grow-1" data-role="reschedule-btn" data-action="{{ route('client.follow-ups.reschedule',$f) }}" data-current="{{ $f->follow_up_at->format('Y-m-d\TH:i') }}" data-lead="{{ $f->lead->name }}"><i class="bi bi-arrow-repeat me-1"></i>Reschedule</button>
                        @endcan
                        @can('complete_followups')
                        <form method="POST" action="{{ route('client.follow-ups.complete',$f) }}" data-role="complete-form" class="flex-grow-1">@csrf
                            <button class="btn btn-primary btn-sm w-100" data-role="complete-btn"><i class="bi bi-check2 me-1"></i>Done</button>
                        </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card p-4 fade-up h-100">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold mb-1">Lead Growth</h6>
                    <div class="muted small">New leads captured over time</div>
                </div>
                <div class="btn-group btn-group-sm" role="group" data-role="range-switch">
                    <button type="button" class="btn btn-light" data-range="7">7D</button>
                    <button type="button" class="btn btn-light active" data-range="30">30D</button>
                    <button type="button" class="btn btn-light" data-range="90">90D</button>
                    <button type="button" class="btn btn-light" data-range="365">Year</button>
                </div>
            </div>
            <canvas id="leadGrowthChart" height="110"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 fade-up h-100 d-flex flex-column">
            <h6 class="fw-bold mb-1">Conversion Rate</h6>
            <div class="muted small mb-3">Won vs. total leads</div>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center position-relative" style="max-height:180px">
                <canvas id="conversionChart"></canvas>
                <div class="position-absolute text-center">
                    <div class="fw-800" style="font-size:26px">{{ $chart['conversionRate'] }}%</div>
                    <div class="muted small">Converted</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card p-4 fade-up h-100">
            <h6 class="fw-bold mb-1">Lead Sources</h6>
            <div class="muted small mb-3">Where your leads come from</div>
            <canvas id="sourcesChart" height="200"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4 fade-up h-100">
            <h6 class="fw-bold mb-1">Lead Status</h6>
            <div class="muted small mb-3">Pipeline breakdown</div>
            <canvas id="statusChart" height="200"></canvas>
        </div>
    </div>
</div>

@role('Admin')
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card fade-up h-100">
            <div class="p-4 pb-3 border-bottom"><h6 class="fw-bold mb-1">Staff Performance</h6><div class="muted small">Leads handled and won by each team member</div></div>
            @if($staffPerformance->isEmpty())
                <x-empty-state icon="bi-person-lines-fill" title="No assigned leads yet" description="Assign leads to staff to see performance here." />
            @else
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead><tr><th>Staff</th><th>Leads</th><th>Won</th><th>Conv.</th><th class="text-end">Value Won</th></tr></thead>
                        <tbody>
                        @foreach($staffPerformance as $row)
                            <tr>
                                <td class="row-title">{{ $row['name'] }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['won'] }}</td>
                                <td>{{ $row['conversion'] }}%</td>
                                <td class="text-end fw-bold">₹{{ number_format($row['value'], 0) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card fade-up h-100">
            <div class="p-4 pb-3 border-bottom"><h6 class="fw-bold mb-1">Source ROI</h6><div class="muted small">Conversion and won value by lead source</div></div>
            @if($sourceRoi->isEmpty())
                <x-empty-state icon="bi-graph-up" title="No leads yet" description="Lead source performance will appear here." />
            @else
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead><tr><th>Source</th><th>Leads</th><th>Won</th><th>Conv.</th><th class="text-end">Value Won</th></tr></thead>
                        <tbody>
                        @foreach($sourceRoi as $row)
                            <tr>
                                <td class="row-title">{{ $row['source'] }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['won'] }}</td>
                                <td>{{ $row['conversion'] }}%</td>
                                <td class="text-end fw-bold">₹{{ number_format($row['value'], 0) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card p-4 fade-up mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h6 class="fw-bold mb-1"><i class="bi bi-link-45deg me-1"></i>Your Lead Capture Form</h6>
            <p class="muted mb-0" style="font-size:12.8px;max-width:520px">Share this link on your website or ads — every submission lands directly in your Leads list, no login required.</p>
        </div>
    </div>
    <div class="d-flex gap-2 mt-3">
        <input type="text" class="form-control" readonly id="leadFormUrl" value="{{ route('public.lead-form', $client) }}">
        <button type="button" class="btn btn-light" onclick="navigator.clipboard.writeText(document.getElementById('leadFormUrl').value).then(() => showToast('Link copied to clipboard.', 'success'))"><i class="bi bi-clipboard me-1"></i>Copy</button>
        <a href="{{ route('public.lead-form', $client) }}" target="_blank" class="btn btn-primary"><i class="bi bi-box-arrow-up-right"></i></a>
    </div>
</div>
@endrole

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-4 fade-up h-100">
            <h6 class="fw-bold mb-3">Quick Actions</h6>
            <div class="d-flex flex-column gap-2">
                @can('create_leads')<x-quick-action icon="bi-person-plus" label="Add Lead" :href="route('client.leads.create')" />@endcan
                <x-quick-action icon="bi-calendar-check" label="View Follow-ups" :href="route('client.follow-ups.index')" />
                @role('Admin')
                <x-quick-action icon="bi-people" label="Manage Staff" :href="route('client.staff.index')" />
                @endrole
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card fade-up h-100">
            <div class="p-4 pb-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Recent Leads</h6>
                <a href="{{ route('client.leads.index') }}" class="btn btn-ghost btn-sm">View all</a>
            </div>
            @if($recentLeads->isEmpty())
                <x-empty-state icon="bi-people" title="No leads yet" description="Leads you create will show up here.">
                    @can('create_leads')
                    <x-slot:actions><a href="{{ route('client.leads.create') }}" class="btn btn-primary btn-sm">+ Add Lead</a></x-slot:actions>
                    @endcan
                </x-empty-state>
            @else
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-modern mb-0">
                        <thead><tr><th>Lead</th><th>Status</th><th>Priority</th><th>Created</th></tr></thead>
                        <tbody>
                        @foreach($recentLeads as $lead)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-avatar :name="$lead->name" size="sm" />
                                        <div>
                                            <a href="{{ route('client.leads.show', $lead) }}" class="row-title text-decoration-none">{{ $lead->name }}</a>
                                            <div class="row-sub">{{ $lead->lead_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><x-status-badge :status="$lead->status" /></td>
                                <td><x-priority-badge :priority="$lead->priority" /></td>
                                <td class="row-sub">{{ $lead->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-md-none d-flex flex-column gap-2 p-3">
                    @foreach($recentLeads as $lead)
                        <a href="{{ route('client.leads.show', $lead) }}" class="entity-card text-decoration-none">
                            <div class="top">
                                <x-avatar :name="$lead->name" size="sm" />
                                <div class="flex-grow-1"><div class="row-title">{{ $lead->name }}</div><div class="row-sub">{{ $lead->lead_number }} · {{ $lead->created_at->format('d M Y') }}</div></div>
                                <x-priority-badge :priority="$lead->priority" />
                            </div>
                            <div class="d-flex gap-2 mt-2 flex-wrap"><x-status-badge :status="$lead->status" /></div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
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
<script src="{{ asset('assets/vendor/chartjs/chart.umd.min.js') }}"></script>
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
            btn.closest('tr, .entity-card')?.remove();
            showToast('Follow-up completed.', 'success');
        }).catch(() => { btn.disabled = false; showToast('Could not complete follow-up.', 'error'); });
    });
});

(function () {
    const modalEl = document.getElementById('rescheduleModal');
    if (!modalEl) return;
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
</script>
<script>
(function () {
    const seed = @json($chart);
    const analyticsUrl = @json(route('client.analytics'));
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = '#8a90a6';
    const gridColor = '#f0f1f6';
    const primary = '#4f46e5';

    const leadGrowth = new Chart(document.getElementById('leadGrowthChart'), {
        type: 'line',
        data: { labels: seed.leadGrowth.labels, datasets: [{ data: seed.leadGrowth.data, borderColor: primary, backgroundColor: 'rgba(79,70,229,.08)', fill: true, tension: .35, pointRadius: 0, borderWidth: 2.5 }] },
        options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } } } },
    });

    const sourcesChart = new Chart(document.getElementById('sourcesChart'), {
        type: 'doughnut',
        data: { labels: seed.leadSources.labels, datasets: [{ data: seed.leadSources.data, backgroundColor: ['#4f46e5', '#22c55e', '#f59e0b', '#0891b2', '#ec4899', '#94a3b8'] }] },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, usePointStyle: true, padding: 12, font: { size: 11 } } } }, cutout: '68%' },
    });

    const statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: { labels: seed.leadStatus.labels, datasets: [{ data: seed.leadStatus.data, backgroundColor: primary, borderRadius: 6, maxBarThickness: 26 }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } } },
    });

    new Chart(document.getElementById('conversionChart'), {
        type: 'doughnut',
        data: { labels: ['Won', 'Other'], datasets: [{ data: [seed.conversionRate, Math.max(0, 100 - seed.conversionRate)], backgroundColor: [primary, '#eef0f5'], borderWidth: 0 }] },
        options: { plugins: { legend: { display: false }, tooltip: { enabled: false } }, cutout: '78%' },
    });

    document.querySelectorAll('[data-role="range-switch"] button').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-role="range-switch"] button').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            fetch(analyticsUrl + '?range=' + btn.dataset.range, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((r) => r.json())
                .then((data) => {
                    leadGrowth.data.labels = data.leadGrowth.labels;
                    leadGrowth.data.datasets[0].data = data.leadGrowth.data;
                    leadGrowth.update();
                });
        });
    });
})();
</script>
@endpush
