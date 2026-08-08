<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class PublicInvoiceController extends Controller
{
    public function show(Invoice $invoice)
    {
        $invoice->load('items', 'client');

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'company' => $invoice->client]);

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }
}
