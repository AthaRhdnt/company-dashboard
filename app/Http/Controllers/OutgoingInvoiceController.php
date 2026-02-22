<?php
namespace App\Http\Controllers;

use App\Exports\DocumentExport;
use App\Exports\OutgoingInvoicesExport;
use App\Models\Client;
use App\Models\Order;
use App\Models\OutgoingInvoice;
use App\Services\LifecycleSorter;
use App\Services\ManualPaginator;
use App\Traits\TerbilangTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class OutgoingInvoiceController extends Controller
{
    use TerbilangTrait;
    use ManualPaginator;

    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $invoices = OutgoingInvoice::with([
            'order.outgoingInvoices',
            'order.incomingInvoices',
            'order.purchaseOrder',
            'client',
        ])->get();

        $invoices->each(function ($invoice) {
            $invoice->inv_date_formatted = optional($invoice->inv_date)
                ? Carbon::parse($invoice->inv_date)->format('d M Y')
                : '-';

            $invoice->payment_date_formatted = $invoice->income_date
                ? Carbon::parse($invoice->income_date)->format('d M Y')
                : 'UNPAID';

            $invoice->due_date_formatted = $invoice->due_date
                ? Carbon::parse($invoice->due_date)->format('d M Y')
                : '-';

            $invoice->po_date_formatted = optional($invoice->order?->purchaseOrder?->po_date)
                ? Carbon::parse($invoice->order->purchaseOrder->po_date)->format('d M Y')
                : '-';
        });

        $sorted = LifecycleSorter::sort($invoices);

        return view('pages.transactions.outgoing-invoices.index', [
            'outgoingInvoices' => $this->paginateCollection($sorted),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(OutgoingInvoice $outgoingInvoice)
    {
        $outgoingInvoice->load([
            'client',
            'order.department',
            'order.purchaseOrder',
            'lineItems.item',
            'lineItems.specs',
        ]);
        return view('pages.transactions.outgoing-invoices.show', [
            'invoice' => $outgoingInvoice,
        ]);
    }

    /**
     * Update multiple specified Outgoing Invoices in storage (Administrative Finalization).
     */
    public function massUpdate(Request $request)
    {
        // 1. Validation for the array of invoices
        $validated = $request->validate([
            'invoices'              => 'required|array',

            // Validation rules for each item in the 'invoices' array
            'invoices.*.id'         => 'required|exists:outgoing_invoices,id',
            'invoices.*.inv_number' => 'nullable|string|max:255',
            'invoices.*.inv_date'   => 'nullable|date',
            'invoices.*.due_date'   => 'nullable|date',
            'invoices.*.fp_number'  => 'nullable|string|max:255',
        ]);

        foreach ($validated['invoices'] as $invoiceData) {
            $fpNumber  = $invoiceData['fp_number'] ?? null;
            $invoiceId = $invoiceData['id'];

            if ($fpNumber !== null) {
                $exists = OutgoingInvoice::where('fp_number', $fpNumber)
                    ->where('id', '!=', $invoiceId)
                    ->exists();

                if ($exists) {
                    return back()
                        ->withErrors(["invoices.{$invoiceId}.fp_number" => "FP Number '{$fpNumber}' is already used by another outgoing invoice."])
                        ->withInput();
                }
            }
        }

        $invoicesUpdatedCount = 0;

        // 2. Optimized Update Logic
        foreach ($validated['invoices'] as $invoiceData) {

            $invoice = OutgoingInvoice::find($invoiceData['id']);

            if ($invoice) {
                // Get the data intended for update (excluding the 'id')
                $updatableData = collect($invoiceData)->except('id')->toArray();

                // Fill the model instance with new data
                $invoice->fill($updatableData);

                // Get the changed attributes (the "dirty" columns)
                $invoiceChanges = $invoice->getDirty();

                // Only update the database if actual changes exist
                if (! empty($invoiceChanges)) {
                    $invoice->update($invoiceChanges);
                    $invoicesUpdatedCount++;
                }
            }
        }

        if ($invoicesUpdatedCount === 0) {
            return redirect()->route('outgoing-invoices.index')
                ->with('info', 'No changes detected. Nothing was updated.');
        }

        // 3. Redirect
        return redirect()->route('outgoing-invoices.index')->with('success', $invoicesUpdatedCount . ' Outgoing Invoice(s) successfully finalized and updated.');
    }

    public function updateField(Request $request, OutgoingInvoice $outgoingInvoice, string $field)
    {

        if (! in_array($field, ['income_date', 'fp_number'])) {
            return response()->json(['error' => 'Invalid field'], 400);
        }

        $rules = [];
        if ($field === 'income_date') {
            $rules[$field] = ['nullable', 'date'];
        } else {
            $rules[$field] = ['nullable', 'string', 'max:255', Rule::unique('outgoing_invoices', 'fp_number')->ignore($outgoingInvoice->id)];
        }

        // This will automatically return 422 + JSON errors on failure (because it's an AJAX/XHR request)
        $validated = $request->validate($rules);

        $outgoingInvoice->update([
            $field => $validated[$field],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Generate a single Outgoing Invoice Document as a PDF.
     */
    public function generateSingleDocument(OutgoingInvoice $outgoingInvoice)
    {
        // CRITICAL: Load 'lineItems' and nested 'order.purchaseOrder' relationship
        $outgoingInvoice->load(['client', 'order.department', 'order.purchaseOrder', 'lineItems']);

        // Format data for the PDF template
        $invoiceData = $this->formatInvoiceForDocument($outgoingInvoice);

        $safeInvNumber = Str::slug($outgoingInvoice->inv_number, '_');
        $filename      = "INV_{$safeInvNumber}.xlsx";

        // Stream or download the PDF
        return Excel::download(
            new DocumentExport($invoiceData),
            $filename
        );
    }

    /**
     * Generate multiple Outgoing Invoice Documents as a zip or stream.
     */
    public function generateMassDocuments(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids'   => 'required|array',
            'invoice_ids.*' => 'exists:outgoing_invoices,id',
        ]);

        $invoices = OutgoingInvoice::with([
            'client',
            'order.department',
            'order.purchaseOrder',
            'lineItems',
        ])->whereIn('id', $validated['invoice_ids'])->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'No invoices found.');
        }

        $authId      = auth()->id() ?? 'guest';
        $tempDir     = 'temp/invoices/' . $authId . '/' . time();
        $zipFileName = 'Invoices_Batch_' . time() . '.zip';
        $zipPath     = Storage::path($tempDir . '/' . $zipFileName);

        Storage::disk('local')->makeDirectory($tempDir);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Unable to create ZIP file.');
        }

        foreach ($invoices as $invoice) {
            try {
                $invoiceData = $this->formatInvoiceForDocument($invoice);

                $safeInvNum = Str::slug($invoice->inv_number ?? $invoice->id, '_');
                $fileName   = "INV_{$safeInvNum}.xlsx";

                $excelBinary = Excel::raw(
                    new DocumentExport($invoiceData),
                    ExcelExcel::XLSX
                );

                $zip->addFromString($fileName, $excelBinary);
            } catch (\Throwable $e) {
                $zip->close();

                return back()->with(
                    'error',
                    "Failed generating invoice {$invoice->inv_number}. Check logs."
                );
            }
        }

        $zip->close();

        return response()
            ->download($zipPath, $zipFileName)
            ->deleteFileAfterSend(true);
    }

    /**
     * Helper to format invoice data for the document template.
     */
    protected function formatInvoiceForDocument(OutgoingInvoice $invoice)
    {
        // 1. Format Dates (Unchanged)
        $invDate = $invoice->inv_date ? Carbon::parse($invoice->inv_date)->format('d F Y') : '-';
        $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d F Y') : '-';

        // Get po_date from the nested relationship: outgoingInvoice -> order -> purchaseOrder
        $poDate = data_get($invoice, 'order.purchaseOrder.po_date')
            ? Carbon::parse(data_get($invoice, 'order.purchaseOrder.po_date'))->format('d F Y')
            : '-';

        // 2. Format Financials (UPDATED LOGIC)
        // Taxable Amount (DPP) is the amount stored in the model
        $taxableAmount = $invoice->amount;

        // VAT (PPN 11%) is rounddown(DPP * 11%)
        // We use floor() for rounddown to the nearest integer
        $vat = floor($taxableAmount * 0.11);

        // Total is DPP + PPN
        $totalAmount = $taxableAmount + $vat;

        // Helper function for Indonesian number format
        $formatNumber = fn($value) => number_format(round($value), 0, ',', '.');

        $formattedTaxableAmount = $formatNumber($taxableAmount);
        $formattedVat           = $formatNumber($vat);
        $formattedAmount        = $formatNumber($totalAmount);

        // 3. Extract Items using the correct 'lineItems' relationship (UPDATED LOGIC)
        $items = $invoice->lineItems->map(function ($item, $index) use ($formatNumber) {
            $qty      = $item->quantity ?? 0;
            $subTotal = $item->subtotal ?? 0;

            // Calculate Unit Price
            $unitPrice = ($qty > 0) ? $subTotal / $qty : 0;

            // Get item details
            $details = data_get($item, 'item.item_name')
            // Note: 'item.description' is included for robustness if you have it.
                ?: data_get($item, 'item.description')
                ?: 'Service/Product Line Item ' . ($index + 1);

            // Append Specs in the required format
            // CORRECTED: Use $item->specs, which is the belongsToMany relationship on InvoiceItem
            if ($item->specs->isNotEmpty()) {
                $details .= "\nSpec:";

                // CORRECTED: Loop through $item->specs
                foreach ($item->specs as $spec) {
                    // CORRECTED: Use the 'item_description' field from the ItemSpec model
                    $details .= "\n- " . ($spec->item_description ?? 'N/A');
                }
            }

            return [
                'no'         => $index + 1,
                'details'    => $details,
                'quantity'   => $qty,
                'unit'       => data_get($item, 'item.unit') ?? 'Set',
                'unit_price' => $unitPrice,
                'sub_total'  => $subTotal,
            ];
        })->toArray();

        // 4. Convert numbers to words (UPDATED PLACEHOLDER)
        // NOTE: You must implement a proper number-to-words function for Indonesian (Terbilang)
        $amountInWords = $this->terbilangRupiah($totalAmount);

        // Return structured data (Uses $totalAmount now)
        return [
            'invoice'    => $invoice,
            'client'     => $invoice->client,
            'order'      => $invoice->order,
            'items'      => $items,
            'dates'      => compact('invDate', 'dueDate', 'poDate'),
            'financials' => [
                'taxableAmount' => $formattedTaxableAmount,
                'vat'           => $formattedVat,
                'totalAmount'   => $formattedAmount,
                'amountInWords' => $amountInWords,
            ],
        ];
    }

    /**
     * Export the list of outgoing invoices to an Excel file.
     */
    public function export()
    {
        $filename = 'outgoing_invoices_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new OutgoingInvoicesExport, $filename);
    }
}
