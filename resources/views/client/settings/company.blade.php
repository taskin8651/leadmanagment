@extends('layouts.client')
@php($title = 'Company Details')
@section('content')

<x-page-header title="Company Details" subtitle="Shown on every invoice you send to customers" />

<div class="card p-4 form-section fade-up">
    <form method="POST" action="{{ route('client.company.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Logo</label>
                <div class="d-flex align-items-center gap-3">
                    @if($client->logo_path)
                        <img src="{{ asset('storage/' . $client->logo_path) }}" alt="Logo" style="width:64px;height:64px;object-fit:contain;border:1px solid var(--border);border-radius:12px;background:#fff">
                    @else
                        <div class="icon-wrap" style="width:64px;height:64px;background:var(--background);color:var(--muted)"><i class="bi bi-image" style="font-size:22px"></i></div>
                    @endif
                    <input type="file" class="form-control" name="logo" accept="image/*">
                </div>
                @error('logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Company Name <span class="req">*</span></label>
                <input class="form-control" name="company_name" value="{{ old('company_name', $client->company_name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Owner Name <span class="req">*</span></label>
                <input class="form-control" name="owner_name" value="{{ old('owner_name', $client->owner_name) }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input class="form-control" name="phone" value="{{ old('phone', $client->phone) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input class="form-control" value="{{ $client->email }}" disabled>
                <div class="form-section-sub mt-1 mb-0">Login email — contact support to change it.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">GSTIN / Tax Number</label>
                <input class="form-control" name="gstin" value="{{ old('gstin', $client->gstin) }}" placeholder="e.g. 22AAAAA0000A1Z5">
            </div>

            <div class="col-12">
                <label class="form-label">Business Address</label>
                <textarea class="form-control" name="address" rows="2" placeholder="Shown on invoices">{{ old('address', $client->address) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Payment Details</label>
                <textarea class="form-control" name="payment_details" rows="3" placeholder="Bank name, account number, IFSC, UPI ID — whatever you want customers to see on the invoice">{{ old('payment_details', $client->payment_details) }}</textarea>
            </div>
        </div>

        <button class="btn btn-primary mt-3"><i class="bi bi-check2 me-1"></i>Save Changes</button>
    </form>
</div>

@endsection
