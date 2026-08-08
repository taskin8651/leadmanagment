@extends('layouts.client')
@php($title = 'Import Leads')
@section('content')

<x-page-header title="Import Leads" subtitle="Bulk-add leads from a CSV or Excel file">
    <x-slot:actions><a href="{{ route('client.leads.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back to Leads</a></x-slot:actions>
</x-page-header>

<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-file-earmark-arrow-up"></i>Upload File</div>
    <div class="form-section-sub">
        First row must be column headers. Supported columns: <code>name</code> (required), <code>phone</code> (required), <code>email</code>, <code>company_name</code>, <code>source</code>, <code>status</code>, <code>priority</code>, <code>estimated_value</code>, <code>notes</code>.
        Leads with a phone number that already exists are skipped automatically.
    </div>
    <form method="POST" action="{{ route('client.leads.import.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" class="form-control mb-3" name="file" accept=".csv,.txt,.xlsx" required>
        <button class="btn btn-primary">Import Leads</button>
    </form>
</div>

@endsection
