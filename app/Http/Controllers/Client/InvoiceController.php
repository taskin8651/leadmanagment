<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Lead;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    private function client()
    {
        return Auth::user()->client;
    }

    public function index(Request $request)
    {
        $invoices = $this->client()->invoices()
            ->with('lead')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('issue_date')
            ->paginate(20)
            ->withQueryString();

        return view('client.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $lead = $request->filled('lead_id')
            ? Lead::where('client_id', $this->client()->id)->findOrFail($request->lead_id)
            : null;

        return view('client.invoices.create', ['lead' => $lead]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'customer_address' => ['nullable', 'string', 'max:2000'],
            'customer_gstin' => ['nullable', 'string', 'max:20'],
            'place_of_supply' => ['nullable', 'string', 'max:100'],
            'is_interstate' => ['nullable', 'boolean'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.hsn_code' => ['nullable', 'string', 'max:20'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function nextInvoiceNumber(): string
    {
        return 'INV-' . str_pad((string) (Invoice::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $client = $this->client();

        if (!empty($data['lead_id'])) {
            abort_unless(Lead::where('client_id', $client->id)->where('id', $data['lead_id'])->exists(), 403);
        }

        $invoice = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => $this->nextInvoiceNumber(),
            'client_id' => $client->id,
            'lead_id' => $data['lead_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'customer_gstin' => $data['customer_gstin'] ?? null,
            'place_of_supply' => $data['place_of_supply'] ?? null,
            'is_interstate' => $request->boolean('is_interstate'),
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'] ?? null,
            'discount' => $data['discount'] ?? 0,
            'tax_percent' => $data['tax_percent'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'status' => 'unpaid',
            'created_by' => Auth::id(),
        ]);

        foreach ($data['items'] as $i => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'amount' => round($item['quantity'] * $item['rate'], 2),
                'sort_order' => $i,
            ]);
        }

        $invoice->load('items')->recalculateTotals();

        if ($invoice->lead) {
            $invoice->lead->logActivity('invoice_created', 'Invoice ' . $invoice->invoice_number . ' created (₹' . number_format($invoice->total, 2) . ')');
        }
        AuditLog::record('invoice.created', "Invoice {$invoice->invoice_number} created (₹" . number_format($invoice->total, 2) . ')', [], $invoice);

        return redirect()->route('client.invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice)
    {
        abort_unless($invoice->client_id === $this->client()->id, 403);
        $invoice->load('items', 'lead');
        return view('client.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        abort_unless($invoice->client_id === $this->client()->id, 403);
        $invoice->load('items');
        return view('client.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->client_id === $this->client()->id, 403);
        $data = $this->validated($request);

        $invoice->update([
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'customer_gstin' => $data['customer_gstin'] ?? null,
            'place_of_supply' => $data['place_of_supply'] ?? null,
            'is_interstate' => $request->boolean('is_interstate'),
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'] ?? null,
            'discount' => $data['discount'] ?? 0,
            'tax_percent' => $data['tax_percent'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);

        $invoice->items()->delete();
        foreach ($data['items'] as $i => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'amount' => round($item['quantity'] * $item['rate'], 2),
                'sort_order' => $i,
            ]);
        }

        $invoice->load('items')->recalculateTotals();

        return redirect()->route('client.invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        abort_unless($invoice->client_id === $this->client()->id, 403);
        AuditLog::record('invoice.deleted', "Invoice {$invoice->invoice_number} deleted", [], $invoice);
        $invoice->delete();
        return redirect()->route('client.invoices.index')->with('success', 'Invoice deleted.');
    }

    public function markPaid(Invoice $invoice)
    {
        abort_unless($invoice->client_id === $this->client()->id, 403);
        $invoice->update(['status' => $invoice->status === 'paid' ? 'unpaid' : 'paid']);
        AuditLog::record('invoice.status_changed', "Invoice {$invoice->invoice_number} marked {$invoice->status}", [], $invoice);
        return back()->with('success', $invoice->status === 'paid' ? 'Marked as paid.' : 'Marked as unpaid.');
    }

    public function download(Invoice $invoice)
    {
        abort_unless($invoice->client_id === $this->client()->id, 403);
        $invoice->load('items', 'client');

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'company' => $invoice->client]);

        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}
