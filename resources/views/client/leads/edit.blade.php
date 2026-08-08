@extends('layouts.client')
@php($title = 'Edit Lead')
@section('content')
<x-page-header title="Edit Lead" :subtitle="$lead->lead_number" />
<form method="POST" action="{{ route('client.leads.update',$lead) }}">
    @csrf @method('PUT')
    @include('client.leads.form',['lead'=>$lead])
    <div class="d-flex gap-2">
        <button class="btn btn-primary">Update Lead</button>
        <a class="btn btn-light" href="{{ route('client.leads.index') }}">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('phoneInput');
    const hint = document.getElementById('duplicateHint');
    if (!input || !hint) return;
    let t;
    input.addEventListener('input', () => {
        clearTimeout(t);
        const phone = input.value.trim();
        if (phone.length < 5) { hint.style.display = 'none'; return; }
        t = setTimeout(() => {
            fetch(@json(route('client.leads.check-duplicate')) + '?phone=' + encodeURIComponent(phone) + '&exclude={{ $lead->id }}')
                .then((r) => r.json())
                .then((data) => {
                    if (data.duplicate) {
                        hint.innerHTML = '⚠ Possible duplicate — <a href="' + data.duplicate.url + '" target="_blank">' + data.duplicate.name + '</a> already has this number';
                        hint.style.display = 'block';
                    } else {
                        hint.style.display = 'none';
                    }
                });
        }, 400);
    });
})();
</script>
@endpush
