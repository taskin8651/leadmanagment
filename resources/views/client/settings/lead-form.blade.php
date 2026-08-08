@extends('layouts.client')
@php($title = 'Lead Capture Form')
@section('content')

<x-page-header title="Your Lead Capture Form" subtitle="Share this link on your website or ads — every submission lands directly in your Leads list, no login required." />

<div class="card p-4 fade-up mb-3">
    <div class="fw-bold mb-2"><i class="bi bi-link-45deg me-1"></i>Your form link</div>
    <div class="input-icon-group"><i class="bi bi-clipboard"></i><input type="text" class="form-control" readonly value="{{ $url }}" id="leadFormUrl"></div>
    <div class="d-flex gap-2 mt-2">
        <button type="button" class="btn btn-primary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('leadFormUrl').value).then(() => showToast('Copied.', 'success'))"><i class="bi bi-clipboard me-1"></i>Copy Link</button>
        <a class="btn btn-light btn-sm" href="{{ $url }}" target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>Preview Form</a>
    </div>
</div>

<div class="card p-4 fade-up">
    <div class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i>How it works</div>
    <ul class="mb-0" style="font-size:13.5px;color:var(--muted)">
        <li>Anyone with this link can submit a lead — no account or login needed.</li>
        <li>Submissions show up instantly in your <a href="{{ route('client.leads.index') }}">Leads</a> list, tagged with source "Public Form".</li>
        <li>Add fields to the form from <a href="{{ route('client.custom-fields.index') }}">Custom Fields</a>.</li>
        <li>Embed it as a link on your website, in ads, or share it directly with prospects.</li>
    </ul>
</div>

@endsection
