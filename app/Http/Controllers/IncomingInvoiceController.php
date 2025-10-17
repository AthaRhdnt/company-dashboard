<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\IncomingInvoice;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncomingInvoicesExport;

class IncomingInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load the required relationships: Vendor, Order, and the Department (D-CODE) via the Order.
        $incomingInvoices = IncomingInvoice::with(['order.department', 'vendor'])
            ->orderBy('inv_received_date', 'desc')
            ->paginate(20);

        // Transform data for display formatting (amount and dates)
        $incomingInvoices->getCollection()->transform(function ($invoice) {
            // Amount is the calculated Vendor Cost
            $invoice->formatted_amount = $invoice->cur . ' ' . number_format($invoice->amount, 0, ',', '.'); 

            // FIX: Safely parse the date attributes using Carbon::parse() before calling format().
            $invoice->inv_received_date_formatted = $invoice->inv_received_date
                ? Carbon::parse($invoice->inv_received_date)->format('Y-m-d')
                : '-';
                
            // INV / FP DATE
            $invoice->fp_date_formatted = $invoice->fp_date
                ? Carbon::parse($invoice->fp_date)->format('Y-m-d')
                : '-';

            // Payment Date ('PMT DATE')
            $invoice->payment_date_formatted = $invoice->payment_date
                ? Carbon::parse($invoice->payment_date)->format('Y-m-d')
                : 'Not Yet';
            
            return $invoice;
        });

        return view('pages.transactions.incoming-invoices.index', compact('incomingInvoices'));
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomingInvoice $incomingInvoice)
    {
        // Eager load necessary relationships for the detail view
        $incomingInvoice->load(['vendor', 'order.department']);

        return view('pages.transactions.incoming-invoices.show', [
            'invoice' => $incomingInvoice
        ]);
    }

    /**
     * Update multiple specified Incoming Invoices in storage (Administrative Finalization).
     */
    // public function massUpdate(Request $request)
    // {
    //     // 1. Validation for the array of invoices
    //     $validated = $request->validate([
    //         'invoices' => 'required|array',
            
    //         // Validation rules for each item in the 'invoices' array
    //         'invoices.*.id' => 'required|exists:incoming_invoices,id',
    //         'invoices.*.inv_number' => 'required|string|max:255',
    //         'invoices.*.inv_received_date' => 'required|date',
    //         'invoices.*.fp_date' => 'nullable|date',
    //         'invoices.*.fp_number' => 'nullable|string|max:255',
    //     ]);
        
    //     $invoicesUpdatedCount = 0;

    //     // 2. Update Logic
    //     foreach ($validated['invoices'] as $invoiceData) {
    //         $invoiceId = $invoiceData['id'];
            
    //         // Remove the ID before passing to the update method
    //         unset($invoiceData['id']); 
            
    //         $invoice = IncomingInvoice::find($invoiceId);
            
    //         if ($invoice) {
    //             $invoice->update($invoiceData);
    //             $invoicesUpdatedCount++;
    //         }
    //     }

    //     // 3. Redirect
    //     return redirect()->route('incoming-invoices.index')->with('success', $invoicesUpdatedCount . ' Incoming Invoice(s) successfully finalized and updated.');
    // }

public function massUpdate(Request $request)
{
    // 1. Validation for the array of invoices
    $validated = $request->validate([
        'invoices' => 'required|array',
        
        // Validation rules for each item in the 'invoices' array
        'invoices.*.id' => 'required|exists:incoming_invoices,id',
        'invoices.*.inv_number' => 'required|string|max:255',
        'invoices.*.inv_received_date' => 'required|date',
        'invoices.*.fp_date' => 'nullable|date',
        'invoices.*.fp_number' => 'nullable|string|max:255',
    ]);
    
    $invoicesUpdatedCount = 0;

    // 2. Optimized Update Logic
    foreach ($validated['invoices'] as $invoiceData) {
        
        $invoice = IncomingInvoice::find($invoiceData['id']);
        
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
    return redirect()->route('incoming-invoices.index')->with('success', $invoicesUpdatedCount . ' Incoming Invoice(s) successfully finalized and updated (only changed fields were written).');
}

    /**
     * Export the list of incoming invoices to an Excel file.
     */
    public function export()
    {
        $filename = 'incoming_invoices_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new IncomingInvoicesExport, $filename);
    }
}
