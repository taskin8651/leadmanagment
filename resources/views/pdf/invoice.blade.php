<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; font-size: 13px; }
.header { width: 100%; margin-bottom: 24px; }
.header td { vertical-align: top; }
.logo { width: 56px; height: 56px; }
.brand { font-size: 19px; font-weight: bold; color: #4f46e5; }
.muted { color: #64748b; }
.small { font-size: 11px; }
.right { text-align: right; }
.eyebrow { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #4f46e5; font-weight: bold; }
.section-title { color: #64748b; font-size: 10.5px; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
table.items th, table.items td { text-align: left; padding: 9px 10px; border-bottom: 1px solid #e6e8f0; }
table.items th { background: #fafbfd; font-size: 10.5px; text-transform: uppercase; color: #64748b; }
table.items td.num, table.items th.num { text-align: right; }
.totals { width: 100%; margin-top: 6px; }
.totals td { padding: 4px 0; }
.totals .label { color: #64748b; }
.totals .grand td { font-weight: bold; font-size: 15px; border-top: 2px solid #0f172a; padding-top: 8px; }
.badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 10.5px; font-weight: bold; }
.badge-paid { background: #eafbef; color: #15803d; }
.badge-unpaid { background: #fef6e7; color: #b45309; }
.badge-cancelled { background: #f1f2f6; color: #4a5164; }
.box { margin-top: 22px; }
.footer { margin-top: 40px; text-align: center; color: #9aa0b4; font-size: 10.5px; }
</style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:60%">
                <table><tr>
                    @if($company->logo_path)<td style="padding-right:10px"><img class="logo" src="{{ public_path('storage/' . $company->logo_path) }}"></td>@endif
                    <td>
                        <div class="brand">{{ $company->company_name }}</div>
                        @if($company->address)<div class="muted small" style="white-space:pre-wrap">{{ $company->address }}</div>@endif
                        @if($company->gstin)<div class="muted small">GSTIN: {{ $company->gstin }}</div>@endif
                        @if($company->phone)<div class="muted small">{{ $company->phone }}</div>@endif
                        @if($company->email)<div class="muted small">{{ $company->email }}</div>@endif
                    </td>
                </tr></table>
            </td>
            <td class="right">
                <div class="eyebrow">Invoice</div>
                <div class="brand" style="font-size:16px">{{ $invoice->invoice_number }}</div>
                <div class="muted small">Issued: {{ $invoice->issue_date->format('d M Y') }}</div>
                @if($invoice->due_date)<div class="muted small">Due: {{ $invoice->due_date->format('d M Y') }}</div>@endif
                <div style="margin-top:6px"><span class="badge badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></div>
            </td>
        </tr>
    </table>

    <div class="box">
        <div class="section-title">Bill To</div>
        <div style="font-weight:bold">{{ $invoice->customer_name }}</div>
        @if($invoice->customer_phone)<div>{{ $invoice->customer_phone }}</div>@endif
        @if($invoice->customer_email)<div>{{ $invoice->customer_email }}</div>@endif
        @if($invoice->customer_address)<div class="muted small" style="white-space:pre-wrap">{{ $invoice->customer_address }}</div>@endif
        @if($invoice->customer_gstin)<div class="muted small">GSTIN: {{ $invoice->customer_gstin }}</div>@endif
        @if($invoice->place_of_supply)<div class="muted small">Place of Supply: {{ $invoice->place_of_supply }}</div>@endif
    </div>

    <table class="items">
        <thead><tr><th>Description</th><th>HSN/SAC</th><th class="num">Qty</th><th class="num">Rate</th><th class="num">Amount</th></tr></thead>
        <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ $item->hsn_code ?: '—' }}</td>
                <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                <td class="num">₹{{ number_format($item->rate, 2) }}</td>
                <td class="num">₹{{ number_format($item->amount, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table style="width:100%">
        <tr>
            <td style="width:60%"></td>
            <td style="width:40%">
                <table class="totals">
                    <tr><td class="label">Subtotal</td><td class="right">₹{{ number_format($invoice->subtotal, 2) }}</td></tr>
                    @if($invoice->discount > 0)<tr><td class="label">Discount</td><td class="right">-₹{{ number_format($invoice->discount, 2) }}</td></tr>@endif
                    @if($invoice->is_interstate)
                        @if($invoice->igst_amount > 0)<tr><td class="label">IGST ({{ rtrim(rtrim(number_format($invoice->tax_percent,2),'0'),'.') }}%)</td><td class="right">₹{{ number_format($invoice->igst_amount, 2) }}</td></tr>@endif
                    @else
                        @if($invoice->cgst_amount > 0)<tr><td class="label">CGST ({{ rtrim(rtrim(number_format($invoice->tax_percent/2,2),'0'),'.') }}%)</td><td class="right">₹{{ number_format($invoice->cgst_amount, 2) }}</td></tr>
                        <tr><td class="label">SGST ({{ rtrim(rtrim(number_format($invoice->tax_percent/2,2),'0'),'.') }}%)</td><td class="right">₹{{ number_format($invoice->sgst_amount, 2) }}</td></tr>@endif
                    @endif
                    <tr class="grand"><td>Total</td><td class="right">₹{{ number_format($invoice->total, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    @if($company->payment_details)
        <div class="box">
            <div class="section-title">Payment Details</div>
            <div class="small" style="white-space:pre-wrap">{{ $company->payment_details }}</div>
        </div>
    @endif

    @if($invoice->notes)
        <div class="box">
            <div class="section-title">Notes</div>
            <div style="white-space:pre-wrap">{{ $invoice->notes }}</div>
        </div>
    @endif

    <div class="footer">Thank you for your business.</div>
</body>
</html>
