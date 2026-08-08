@extends('layouts.client')
@php($title = 'Invoices')
@section('content')

<x-page-header title="Invoices" subtitle="Bill your leads and customers">
    <x-slot:actions><a href="{{ route('client.invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create Invoice</a></x-slot:actions>
</x-page-header>

<div class="card p-3 mb-3 fade-up">
    <form class="d-flex gap-2">
        <select class="form-select" name="status" onchange="this.form.submit()" style="max-width:200px">
            <option value="">All Status</option>
            @foreach(['unpaid','paid','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
    </form>
</div>

@if($invoices->isEmpty())
    <div class="card">
        <x-empty-state icon="bi-receipt" title="No invoices yet" description="Invoices you create for leads and customers will show up here.">
            <x-slot:actions><a href="{{ route('client.invoices.create') }}" class="btn btn-primary">+ Create Invoice</a></x-slot:actions>
        </x-empty-state>
    </div>
@else
    <div class="card fade-up">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-modern mb-0">
                <thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Status</th><th class="text-end">Amount</th><th></th></tr></thead>
                <tbody>
                @foreach($invoices as $inv)
                    <tr>
                        <td><a class="row-title text-decoration-none" href="{{ route('client.invoices.show',$inv) }}">{{ $inv->invoice_number }}</a>@if($inv->lead)<div class="row-sub">Lead: {{ $inv->lead->name }}</div>@endif</td>
                        <td>{{ $inv->customer_name }}<div class="row-sub">{{ $inv->customer_phone }}</div></td>
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
        <div class="p-3 d-none d-md-block">{{ $invoices->links() }}</div>

        <div class="d-md-none d-flex flex-column gap-2 p-3">
            @foreach($invoices as $inv)
                <a href="{{ route('client.invoices.show',$inv) }}" class="entity-card text-decoration-none">
                    <div class="top">
                        <div class="flex-grow-1"><div class="row-title">{{ $inv->invoice_number }}</div><div class="row-sub">{{ $inv->customer_name }} · {{ $inv->issue_date->format('d M Y') }}</div></div>
                        @if($inv->status === 'paid')<span class="badge badge-success">Paid</span>
                        @elseif($inv->status === 'cancelled')<span class="badge badge-neutral">Cancelled</span>
                        @else<span class="badge badge-warning">Unpaid</span>@endif
                    </div>
                    <div class="d-flex gap-2 mt-2 flex-wrap"><span class="badge badge-neutral">₹{{ number_format($inv->total, 2) }}</span></div>
                </a>
            @endforeach
            <div>{{ $invoices->links() }}</div>
        </div>
    </div>
@endif

@endsection
