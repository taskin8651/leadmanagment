@extends('layouts.client')
@php($title = 'Attendance')
@section('content')

<x-page-header title="Attendance" subtitle="Staff check-in / check-out log" />

<div class="kpi-strip mb-3 fade-up">
    <div class="kpi-mini"><div class="n">{{ $kpis['today'] }}</div><div class="l"><i class="bi bi-calendar-day me-1"></i>Today</div></div>
    <div class="kpi-mini"><div class="n">{{ $kpis['present'] }}</div><div class="l"><i class="bi bi-check-circle me-1"></i>Present</div></div>
    <div class="kpi-mini"><div class="n">{{ $kpis['late'] }}</div><div class="l"><i class="bi bi-clock-history me-1"></i>Late</div></div>
    <div class="kpi-mini"><div class="n">{{ $kpis['active_now'] }}</div><div class="l"><i class="bi bi-broadcast me-1"></i>Active Now</div></div>
</div>

<div class="card p-4 fade-up mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h6 class="fw-bold mb-1">Your Attendance Today</h6>
            @if($myToday && $myToday->check_in_at)
                <div class="muted small">
                    Checked in at {{ $myToday->check_in_at->format('h:i A') }}
                    @if($myToday->check_out_at) · Checked out at {{ $myToday->check_out_at->format('h:i A') }} @endif
                </div>
            @else
                <div class="muted small">You haven't checked in today.</div>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if(!$myToday || !$myToday->check_in_at || $myToday->check_out_at)
                <button type="button" class="btn btn-primary" id="checkInBtn"><i class="bi bi-box-arrow-in-right me-1"></i>Check In</button>
            @else
                <button type="button" class="btn btn-light" id="checkOutBtn"><i class="bi bi-box-arrow-right me-1"></i>Check Out</button>
            @endif
        </div>
    </div>
    <div id="geoError" class="text-danger small mt-2" style="display:none">Location access is required to check in/out. Please allow location permission and try again.</div>
</div>

<div class="card p-3 mb-3 fade-up">
    <form class="toolbar" method="GET">
        <select class="form-select" style="max-width:200px" name="user_id" onchange="this.form.submit()">
            <option value="">All Staff</option>
            @foreach($staff as $s)<option value="{{ $s->id }}" @selected(request('user_id')==$s->id)>{{ $s->name }}</option>@endforeach
        </select>
        <select class="form-select" style="max-width:170px" name="status" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['present','late','half_day'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
        </select>
        <input class="form-control" style="max-width:170px" type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()">
        <input class="form-control" style="max-width:170px" type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()">
        @if(request()->anyFilled(['user_id','status','date_from','date_to']))<a href="{{ route('client.attendances.index') }}" class="btn btn-ghost">Clear filters</a>@endif
    </form>
</div>

@if($attendances->isEmpty())
    <div class="card">
        <x-empty-state icon="bi-geo-alt" title="No attendance records" description="Check-ins from your team will show up here." />
    </div>
@else
    <div class="card fade-up">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-modern mb-0">
                <thead><tr><th>Staff</th><th>Date</th><th>Check-in</th><th>Check-out</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($attendances as $a)
                    <tr>
                        <td><div class="d-flex align-items-center gap-2"><x-avatar :name="$a->user->name" size="sm" /><span class="row-title">{{ $a->user->name }}</span></div></td>
                        <td class="row-sub">{{ $a->date->format('d M Y') }}</td>
                        <td>{{ $a->check_in_at?->format('h:i A') ?? '—' }}</td>
                        <td>{{ $a->check_out_at?->format('h:i A') ?? '—' }}</td>
                        <td>
                            @if($a->status === 'present')<span class="badge badge-success">Present</span>
                            @elseif($a->status === 'late')<span class="badge badge-warning">Late</span>
                            @else<span class="badge badge-neutral">Half Day</span>@endif
                        </td>
                        <td class="text-end"><a class="btn btn-sm btn-light" href="{{ route('client.attendances.show',$a) }}">View</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 d-none d-md-block">{{ $attendances->links() }}</div>

        <div class="d-md-none d-flex flex-column gap-2 p-3">
            @foreach($attendances as $a)
                <a href="{{ route('client.attendances.show',$a) }}" class="entity-card text-decoration-none">
                    <div class="top">
                        <x-avatar :name="$a->user->name" size="sm" />
                        <div class="flex-grow-1"><div class="row-title">{{ $a->user->name }}</div><div class="row-sub">{{ $a->date->format('d M Y') }}</div></div>
                        @if($a->status === 'present')<span class="badge badge-success">Present</span>
                        @elseif($a->status === 'late')<span class="badge badge-warning">Late</span>
                        @else<span class="badge badge-neutral">Half Day</span>@endif
                    </div>
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <span class="badge badge-neutral"><i class="bi bi-box-arrow-in-right me-1"></i>{{ $a->check_in_at?->format('h:i A') ?? '—' }}</span>
                        <span class="badge badge-neutral"><i class="bi bi-box-arrow-right me-1"></i>{{ $a->check_out_at?->format('h:i A') ?? '—' }}</span>
                    </div>
                </a>
            @endforeach
            <div>{{ $attendances->links() }}</div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
(function () {
    function withLocation(cb) {
        const err = document.getElementById('geoError');
        err.style.display = 'none';
        if (!navigator.geolocation) { err.style.display = 'block'; return; }
        navigator.geolocation.getCurrentPosition(
            (pos) => cb(pos.coords.latitude, pos.coords.longitude),
            () => { err.style.display = 'block'; }
        );
    }

    function post(url, lat, lng) {
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ latitude: lat, longitude: lng }),
        }).then(() => window.location.reload());
    }

    document.getElementById('checkInBtn')?.addEventListener('click', function () {
        withLocation((lat, lng) => post(@json(route('client.attendances.check-in')), lat, lng));
    });
    document.getElementById('checkOutBtn')?.addEventListener('click', function () {
        withLocation((lat, lng) => post(@json(route('client.attendances.check-out')), lat, lng));
    });
})();
</script>
@endpush
