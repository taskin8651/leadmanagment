@extends('layouts.client')
@php($title = 'Edit Invoice')
@section('content')

<x-page-header title="Edit Invoice" subtitle="{{ $invoice->invoice_number }}">
    <x-slot:actions><a href="{{ route('client.invoices.show',$invoice) }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back to Invoice</a></x-slot:actions>
</x-page-header>

<form method="POST" action="{{ route('client.invoices.update',$invoice) }}" id="invoiceForm">
    @csrf @method('PUT')

    <div class="card p-4 form-section fade-up mb-3">
        <div class="form-section-title"><i class="bi bi-person"></i>Bill To</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Customer Name <span class="req">*</span></label>
                <input class="form-control" name="customer_name" value="{{ old('customer_name', $invoice->customer_name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input class="form-control" name="customer_phone" value="{{ old('customer_phone', $invoice->customer_phone) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="customer_email" value="{{ old('customer_email', $invoice->customer_email) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="customer_address" rows="2">{{ old('customer_address', $invoice->customer_address) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Customer GSTIN</label>
                <input class="form-control" name="customer_gstin" value="{{ old('customer_gstin', $invoice->customer_gstin) }}" placeholder="22AAAAA0000A1Z5">
            </div>
            <div class="col-md-4">
                <label class="form-label">Place of Supply</label>
                <input class="form-control" name="place_of_supply" value="{{ old('place_of_supply', $invoice->place_of_supply) }}" placeholder="e.g. Maharashtra">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_interstate" value="1" id="isInterstate" @checked(old('is_interstate', $invoice->is_interstate))>
                    <label class="form-check-label" for="isInterstate">Interstate supply (IGST)</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-4 form-section fade-up mb-3">
        <div class="form-section-title"><i class="bi bi-calendar3"></i>Invoice Details</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Issue Date <span class="req">*</span></label>
                <input class="form-control" type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Due Date</label>
                <input class="form-control" type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
            </div>
        </div>
    </div>

    <div class="card p-4 form-section fade-up mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="form-section-title mb-0"><i class="bi bi-list-ul"></i>Items</div>
            <button type="button" class="btn btn-light btn-sm" id="addItemBtn"><i class="bi bi-plus-lg me-1"></i>Add Item</button>
        </div>
        <div class="table-responsive">
            <table class="table table-modern mb-0" id="itemsTable">
                <thead><tr><th>Description</th><th style="width:110px">HSN/SAC</th><th style="width:110px">Qty</th><th style="width:140px">Rate (₹)</th><th style="width:140px">Amount (₹)</th><th style="width:40px"></th></tr></thead>
                <tbody id="itemsBody">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td><input class="form-control" name="items[{{ $loop->index }}][description]" value="{{ $item->description }}" required></td>
                            <td><input class="form-control" name="items[{{ $loop->index }}][hsn_code]" value="{{ $item->hsn_code }}" placeholder="HSN/SAC"></td>
                            <td><input class="form-control qty" type="number" step="0.01" min="0.01" name="items[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}" required></td>
                            <td><input class="form-control rate" type="number" step="0.01" min="0" name="items[{{ $loop->index }}][rate]" value="{{ $item->rate }}" required></td>
                            <td><span class="row-sub amount-display">₹{{ number_format($item->amount, 2) }}</span></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-light remove-row" aria-label="Remove item"><i class="bi bi-trash text-danger"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @error('items')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

        <div class="row g-3 mt-1 justify-content-end">
            <div class="col-md-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Discount (₹)</label>
                    <input class="form-control" style="max-width:150px" type="number" step="0.01" min="0" name="discount" id="discountInput" value="{{ old('discount', $invoice->discount) }}">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Tax (%)</label>
                    <input class="form-control" style="max-width:150px" type="number" step="0.01" min="0" max="100" name="tax_percent" id="taxInput" value="{{ old('tax_percent', $invoice->tax_percent) }}">
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold" style="font-size:15px"><span>Total</span><span id="totalDisplay">₹0.00</span></div>
            </div>
        </div>
    </div>

    <div class="card p-4 form-section fade-up mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control" name="notes" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Changes</button>
        <a href="{{ route('client.invoices.show',$invoice) }}" class="btn btn-light">Cancel</a>
    </div>
</form>

@endsection

@push('scripts')
<script>
(function () {
    const body = document.getElementById('itemsBody');
    let rowIndex = {{ $invoice->items->count() }};
    const rowTpl = () => {
        const i = rowIndex++;
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input class="form-control" name="items[' + i + '][description]" placeholder="Item / service description" required></td>' +
            '<td><input class="form-control" name="items[' + i + '][hsn_code]" placeholder="HSN/SAC"></td>' +
            '<td><input class="form-control qty" type="number" step="0.01" min="0.01" name="items[' + i + '][quantity]" value="1" required></td>' +
            '<td><input class="form-control rate" type="number" step="0.01" min="0" name="items[' + i + '][rate]" value="0" required></td>' +
            '<td><span class="row-sub amount-display">₹0.00</span></td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-light remove-row" aria-label="Remove item"><i class="bi bi-trash text-danger"></i></button></td>';
        return tr;
    };

    function addRow() {
        body.appendChild(rowTpl());
        recalc();
    }

    function recalc() {
        let subtotal = 0;
        body.querySelectorAll('tr').forEach((tr) => {
            const qty = parseFloat(tr.querySelector('.qty')?.value) || 0;
            const rate = parseFloat(tr.querySelector('.rate')?.value) || 0;
            const amount = qty * rate;
            tr.querySelector('.amount-display').textContent = '₹' + amount.toFixed(2);
            subtotal += amount;
        });
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const taxPercent = parseFloat(document.getElementById('taxInput').value) || 0;
        const taxable = Math.max(0, subtotal - discount);
        const tax = taxable * taxPercent / 100;
        document.getElementById('totalDisplay').textContent = '₹' + (taxable + tax).toFixed(2);
    }

    document.getElementById('addItemBtn').addEventListener('click', addRow);
    document.getElementById('discountInput').addEventListener('input', recalc);
    document.getElementById('taxInput').addEventListener('input', recalc);
    body.addEventListener('input', (e) => { if (e.target.classList.contains('qty') || e.target.classList.contains('rate')) recalc(); });
    body.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;
        if (body.querySelectorAll('tr').length > 1) { btn.closest('tr').remove(); recalc(); }
    });

    if (body.querySelectorAll('tr').length === 0) addRow(); else recalc();

    document.getElementById('invoiceForm').addEventListener('submit', (e) => {
        if (body.querySelectorAll('tr').length === 0) {
            e.preventDefault();
            showToast('Add at least one item.', 'error');
        }
    });
})();
</script>
@endpush
