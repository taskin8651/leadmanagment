@extends('layouts.client')
@php($title = $invoice->invoice_number)
@section('content')

<x-page-header title="{{ $invoice->invoice_number }}" subtitle="{{ $invoice->customer_name }} · {{ $invoice->issue_date->format('d M Y') }}">
    <x-slot:actions>
        <a href="{{ route('client.invoices.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back</a>
        <a href="{{ route('client.invoices.edit',$invoice) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form method="POST" action="{{ route('client.invoices.destroy',$invoice) }}" data-confirm="This permanently deletes invoice {{ $invoice->invoice_number }}." data-confirm-title="Delete invoice?">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form>
    </x-slot:actions>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card p-3 fade-up d-flex flex-row flex-wrap gap-2 align-items-center">
            @if($invoice->status === 'paid')<span class="badge badge-success">Paid</span>
            @elseif($invoice->status === 'cancelled')<span class="badge badge-neutral">Cancelled</span>
            @else<span class="badge badge-warning">Unpaid</span>@endif

            <div class="ms-auto d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('client.invoices.mark-paid',$invoice) }}">@csrf
                    <button class="btn btn-light btn-sm"><i class="bi bi-check2-circle me-1"></i>{{ $invoice->status === 'paid' ? 'Mark Unpaid' : 'Mark Paid' }}</button>
                </form>
                <a href="{{ route('client.invoices.download',$invoice) }}" class="btn btn-light btn-sm"><i class="bi bi-download me-1"></i>Download PDF</a>
                @if($invoice->customer_phone)
                    <a target="_blank" class="btn btn-primary btn-sm" href="https://wa.me/{{ preg_replace('/\D/','',$invoice->customer_phone) }}?text={{ urlencode('Hi ' . $invoice->customer_name . ", here's your invoice " . $invoice->invoice_number . ' (₹' . number_format($invoice->total, 2) . '): ' . route('public.invoice.show', $invoice)) }}">
                        <i class="bi bi-whatsapp me-1"></i>Send via WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card p-4 fade-up">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            @if($invoice->client->logo_path)
                <img src="{{ asset('storage/' . $invoice->client->logo_path) }}" alt="Logo" style="width:52px;height:52px;object-fit:contain">
            @endif
            <div>
                <div class="fw-800" style="font-size:18px">{{ $invoice->client->company_name }}</div>
                @if($invoice->client->address)<div class="row-sub" style="white-space:pre-wrap">{{ $invoice->client->address }}</div>@endif
                @if($invoice->client->gstin)<div class="row-sub">GSTIN: {{ $invoice->client->gstin }}</div>@endif
                @if($invoice->client->phone)<div class="row-sub">{{ $invoice->client->phone }}</div>@endif
            </div>
        </div>
        <div class="text-md-end">
            <div class="page-eyebrow">Invoice</div>
            <div class="fw-800" style="font-size:18px">{{ $invoice->invoice_number }}</div>
            <div class="row-sub">Issued {{ $invoice->issue_date->format('d M Y') }}</div>
            @if($invoice->due_date)<div class="row-sub">Due {{ $invoice->due_date->format('d M Y') }}</div>@endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="muted small mb-1">Bill To</div>
            <div class="fw-bold">{{ $invoice->customer_name }}</div>
            @if($invoice->customer_phone)<div>{{ $invoice->customer_phone }}</div>@endif
            @if($invoice->customer_email)<div>{{ $invoice->customer_email }}</div>@endif
            @if($invoice->customer_address)<div class="row-sub" style="white-space:pre-wrap">{{ $invoice->customer_address }}</div>@endif
            @if($invoice->customer_gstin)<div class="row-sub">GSTIN: {{ $invoice->customer_gstin }}</div>@endif
            @if($invoice->place_of_supply)<div class="row-sub">Place of Supply: {{ $invoice->place_of_supply }}</div>@endif
            @if($invoice->lead)<div class="mt-1"><a href="{{ route('client.leads.show',$invoice->lead) }}">View lead <i class="bi bi-arrow-up-right"></i></a></div>@endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead><tr><th>Description</th><th>HSN/SAC</th><th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="row-sub">{{ $item->hsn_code ?: '—' }}</td>
                    <td class="text-end">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                    <td class="text-end">₹{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end mt-3">
        <div class="col-md-4">
            <div class="d-flex justify-content-between mb-1"><span class="muted">Subtotal</span><span>₹{{ number_format($invoice->subtotal, 2) }}</span></div>
            @if($invoice->discount > 0)<div class="d-flex justify-content-between mb-1"><span class="muted">Discount</span><span>-₹{{ number_format($invoice->discount, 2) }}</span></div>@endif
            @if($invoice->is_interstate)
                @if($invoice->igst_amount > 0)<div class="d-flex justify-content-between mb-1"><span class="muted">IGST ({{ rtrim(rtrim(number_format($invoice->tax_percent,2),'0'),'.') }}%)</span><span>₹{{ number_format($invoice->igst_amount, 2) }}</span></div>@endif
            @else
                @if($invoice->cgst_amount > 0)<div class="d-flex justify-content-between mb-1"><span class="muted">CGST ({{ rtrim(rtrim(number_format($invoice->tax_percent/2,2),'0'),'.') }}%)</span><span>₹{{ number_format($invoice->cgst_amount, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-1"><span class="muted">SGST ({{ rtrim(rtrim(number_format($invoice->tax_percent/2,2),'0'),'.') }}%)</span><span>₹{{ number_format($invoice->sgst_amount, 2) }}</span></div>@endif
            @endif
            <hr>
            <div class="d-flex justify-content-between fw-bold" style="font-size:16px"><span>Total</span><span>₹{{ number_format($invoice->total, 2) }}</span></div>
        </div>
    </div>

    @if($invoice->client->payment_details)
        <div class="mt-4"><div class="muted small mb-1">Payment Details</div><div style="white-space:pre-wrap;font-size:13px">{{ $invoice->client->payment_details }}</div></div>
    @endif

    @if($invoice->notes)
        <div class="mt-4"><div class="muted small mb-1">Notes</div><div style="white-space:pre-wrap">{{ $invoice->notes }}</div></div>
    @endif
</div>

@endsection
