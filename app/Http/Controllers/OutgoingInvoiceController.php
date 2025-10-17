<?php
namespace App\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\TerbilangTrait;
use App\Models\OutgoingInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OutgoingInvoicesExport;
use Illuminate\Support\Facades\Storage;

class OutgoingInvoiceController extends Controller
{
    use TerbilangTrait; 

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load the required relationships: Client, Order, and the Department (D-CODE) via the Order.
        $outgoingInvoices = OutgoingInvoice::with(['order.department', 'client'])
            ->orderBy('inv_date', 'desc')
            ->paginate(20);

        // Transform data for display formatting (amount and dates)
        $outgoingInvoices->getCollection()->transform(function ($invoice) {
            $invoice->formatted_amount = $invoice->cur . ' ' . number_format($invoice->amount, 0, ',', '.'); // Assuming Indonesian format without decimals

            // FIX: Safely parse the date attribute using Carbon::parse() before calling format().
            // This handles cases where the attributes are stored as strings or raw date values.
            $invoice->inv_date_formatted = $invoice->inv_date
                ? Carbon::parse($invoice->inv_date)->format('Y-m-d')
                : '-';

            $invoice->due_date_formatted = $invoice->due_date
                ? Carbon::parse($invoice->due_date)->format('Y-m-d')
                : '-';

            $invoice->po_date_formatted = $invoice->order->purchaseOrder->po_date
                ? Carbon::parse($invoice->order->purchaseOrder->po_date)->format('Y-m-d')
                : '-';

            // Payment Date is the 'INC DATE'
            $invoice->payment_date_formatted = $invoice->income_date
                ? Carbon::parse($invoice->income_date)->format('Y-m-d')
                : 'Not Yet';

            return $invoice;
        });

        return view('pages.transactions.outgoing-invoices.index', compact('outgoingInvoices'));
    }

    /**
     * Display the specified resource.
     */
    public function show(OutgoingInvoice $outgoingInvoice)
    {
        // Eager load necessary relationships for the detail view
        $outgoingInvoice->load(['client', 'order.department', 'order.purchaseOrder']);

        // Transform data for display formatting
        $outgoingInvoice->formatted_amount       = $outgoingInvoice->cur . ' ' . number_format($outgoingInvoice->amount, 0, ',', '.');
        $outgoingInvoice->payment_date_formatted = $outgoingInvoice->payment_date
            ? Carbon::parse($outgoingInvoice->payment_date)->format('Y-m-d')
            : 'Not Yet';

        // You may want to add other formatting here if needed for the show view

        return view('pages.transactions.outgoing-invoices.show', [
            'invoice' => $outgoingInvoice,
        ]);
    }

    /**
     * Update multiple specified Outgoing Invoices in storage (Administrative Finalization).
     */
    // public function massUpdate(Request $request)
    // {
    //     // 1. Validation for the array of invoices
    //     $validated = $request->validate([
    //         'invoices'              => 'required|array',

    //         // Validation rules for each item in the 'invoices' array
    //         'invoices.*.id'         => 'required|exists:outgoing_invoices,id',
    //         'invoices.*.inv_number' => 'required|string|max:255',
    //         'invoices.*.inv_date'   => 'required|date',
    //         'invoices.*.due_date'   => 'nullable|date',
    //         'invoices.*.fp_number'  => 'nullable|string|max:255',
    //     ]);

    //     $invoicesUpdatedCount = 0;

    //     // 2. Update Logic
    //     foreach ($validated['invoices'] as $invoiceData) {
    //         $invoiceId = $invoiceData['id'];

    //         // Remove the ID before passing to the update method
    //         unset($invoiceData['id']);

    //         $invoice = OutgoingInvoice::find($invoiceId);

    //         if ($invoice) {
    //             $invoice->update($invoiceData);
    //             $invoicesUpdatedCount++;
    //         }
    //     }

    //     // 3. Redirect
    //     return redirect()->route('outgoing-invoices.index')->with('success', $invoicesUpdatedCount . ' Outgoing Invoice(s) successfully finalized and updated.');
    // }
public function massUpdate(Request $request)
{
    // 1. Validation for the array of invoices
    $validated = $request->validate([
        'invoices'              => 'required|array',

        // Validation rules for each item in the 'invoices' array
        'invoices.*.id'         => 'required|exists:outgoing_invoices,id',
        'invoices.*.inv_number' => 'required|string|max:255',
        'invoices.*.inv_date'   => 'required|date',
        'invoices.*.due_date'   => 'nullable|date',
        'invoices.*.fp_number'  => 'nullable|string|max:255',
    ]);

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
            if (!empty($invoiceChanges)) {
                $invoice->update($invoiceChanges);
                $invoicesUpdatedCount++;
            }
        }
    }

    // 3. Redirect
    return redirect()->route('outgoing-invoices.index')->with('success', $invoicesUpdatedCount . ' Outgoing Invoice(s) successfully finalized and updated (only changed fields were written).');
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

        // Generate the PDF from the blade view
        $pdf = Pdf::loadView('documents.outgoing-invoice', ['invoice' => $invoiceData]);

        $safeInvNumber = Str::slug($outgoingInvoice->inv_number, '_');
        $filename = "INV_{$safeInvNumber}.pdf";

        // Stream or download the PDF
        return $pdf->download($filename);
    }

    /**
     * Generate multiple Outgoing Invoice Documents as a zip or stream.
     */
    public function generateMassDocuments(Request $request)
    {
        // --- DEBUG LOG: START OF PROCESS ---
        \Log::info('Batch Document Generation started.');

        $validated = $request->validate([
            'invoice_ids'   => 'required|array',
            'invoice_ids.*' => 'exists:outgoing_invoices,id',
        ]);

        // Fetch all selected invoices
        $invoices = OutgoingInvoice::with(['client', 'order.department', 'order.purchaseOrder', 'lineItems'])
            ->whereIn('id', $validated['invoice_ids'])
            ->get();

        // --- DEBUG LOG: INVOICES FETCHED ---
        \Log::info('Found ' . $invoices->count() . ' invoices for batch processing.');

        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', 'No invoices found to generate documents.');
        }

        // 2. Setup file paths for temporary storage - ***CHANGED***
        $authId = auth()->check() ? auth()->id() : 'guest';
        $tempDir = 'temp/invoices/' . $authId . '/' . time(); // Relative path inside 'storage' disk
        $zipFileName = 'Invoices_Batch_' . time() . '.zip';
        // Get the ABSOLUTE path for ZipArchive
        $zipPath = Storage::path($tempDir . '/' . $zipFileName);

        // Ensure the necessary temporary directory exists in storage/app/
        // NOTE: ZipArchive needs the directory to exist, not just the file path
        if (!Storage::disk('local')->exists($tempDir)) {
            Storage::disk('local')->makeDirectory($tempDir);
        }


        // 3. Create the ZIP file and generate PDFs
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // --- DEBUG LOG: ZIP OPEN SUCCESS ---
            \Log::info('ZipArchive successfully opened at: ' . $zipPath);
            
            foreach ($invoices as $invoice) {
                $invNum = $invoice->inv_number ?? $invoice->id;
                $safeInvNum = Str::slug($invNum, '_');
                $pdfFileName = "Invoice_{$safeInvNum}.pdf";
                // --- DEBUG LOG: STARTING INDIVIDUAL PDF ---
                \Log::info("Starting PDF generation for Invoice: $invNum (ID: {$invoice->id})");

                try {
                    // Get the structured data for the PDF view
                    $invoiceData = $this->formatInvoiceForDocument($invoice); 
                    
                    // Generate PDF
                    $pdf = Pdf::loadView('documents.outgoing-invoice', ['invoice' => $invoiceData]);
                    
                    // $pdfFileName = 'Invoice-' . $invNum . '.pdf';

                    // Add the PDF contents directly to the ZIP
                    $zip->addFromString($pdfFileName, $pdf->output());
                    
                    // --- DEBUG LOG: PDF ADDED TO ZIP ---
                    \Log::info("Successfully added PDF $pdfFileName to ZIP.");

                } catch (\Exception $e) {
                    // --- CRITICAL LOG: PDF GENERATION FAILURE ---
                    \Log::error("Failed to generate or add PDF for Invoice ID {$invoice->id} ($invNum): " . $e->getMessage() . "\n" . $e->getTraceAsString());
                    
                    // Return immediately if one PDF fails to stop the whole process and alert the user
                    return back()->with('error', 'A critical error occurred during PDF generation for Invoice ' . $invNum . '. Check server logs for details.');
                }
            }
            
            $zip->close();
            // --- DEBUG LOG: ZIP CLOSED ---
            \Log::info('ZipArchive closed successfully. Total files: ' . $zip->numFiles);

            // 4. Serve the ZIP file and clean up
            if (file_exists($zipPath)) {
                // --- DEBUG LOG: DOWNLOAD START ---
                \Log::info('ZIP file exists. Initiating download for: ' . $zipFileName);

                // Return the download response and automatically delete the file
                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
                
            } else {
                // --- CRITICAL LOG: FINAL FILE MISSING ---
                \Log::error('Final ZIP file not found after closing ZipArchive at: ' . $zipPath);
                return back()->with('error', 'Failed to create the final ZIP file.');
            }
            
        } else {
            // --- CRITICAL LOG: ZIP OPEN FAILURE ---
            \Log::error('Failed to open ZipArchive at: ' . $zipPath . '. Check directory permissions for ' . storage_path('app/public'));
            return back()->with('error', 'Failed to open the ZIP file for creation. Check permissions on the storage/app/public directory.');
        }
    }

    /**
     * Helper to format invoice data for the document template.
     */
    protected function formatInvoiceForDocument(OutgoingInvoice $invoice)
    {
        // 1. Format Dates (Unchanged)
        $invDate = $invoice->inv_date ? Carbon::parse($invoice->inv_date)->format('Y-m-d') : '-';
        $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->format('Y-m-d') : '-';

        // Get po_date from the nested relationship: outgoingInvoice -> order -> purchaseOrder
        $poDate = data_get($invoice, 'order.purchaseOrder.po_date')
            ? Carbon::parse(data_get($invoice, 'order.purchaseOrder.po_date'))->format('Y-m-d')
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
        $formattedAmount        = $formatNumber($totalAmount); // Use the new total

        // 3. Extract Items using the correct 'lineItems' relationship (UPDATED LOGIC)
        $items = $invoice->lineItems->map(function ($item, $index) use ($formatNumber) {
            $qty      = $item->quantity ?? 0;
            $subTotal = $item->subtotal ?? 0; // Assuming this is the DPP/Taxable subtotal for the line item
            
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
                $details .= "\nSpecs:";
                
                // CORRECTED: Loop through $item->specs
                foreach ($item->specs as $spec) {
                    // CORRECTED: Use the 'item_description' field from the ItemSpec model
                    $details .= "\n- " . ($spec->item_description ?? 'N/A');
                }
            }

            return [
                'no'         => $index + 1,
                // The 'details' field now contains the name and specs separated by \n
                'details'    => $details, 
                'quantity'   => $qty,
                'unit'       => data_get($item, 'item.unit') ?? 'Set', 
                'unit_price' => $formatNumber($unitPrice),
                'sub_total'  => $formatNumber($subTotal),
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
