<?php
namespace App\Http\Controllers;

use App\Exports\IncomingInvoicesExport;
use App\Models\Client;
use App\Models\Department;
use App\Models\IncomingInvoice;
use App\Models\IncomingInvoiceItem;
use App\Models\Item;
use App\Models\Order;
use App\Models\Tax;
use App\Models\Vendor;
use App\Services\LifecycleSorter;
use App\Services\ManualPaginator;
use App\Services\NumberGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class IncomingInvoiceController extends Controller
{

    use ManualPaginator;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = IncomingInvoice::with([
            'order.outgoingInvoices',
            'order.incomingInvoices',
            'vendor',
        ])->get();

        $invoices->each(function ($invoice) {
            $invoice->inv_received_date_formatted = optional($invoice->inv_received_date)
                ? Carbon::parse($invoice->inv_received_date)->format('d M Y')
                : '-';

            $invoice->payment_date_formatted = $invoice->payment_date
                ? Carbon::parse($invoice->payment_date)->format('d M Y')
                : 'UNPAID';

            $invoice->fp_date_formatted = $invoice->fp_date
                ? Carbon::parse($invoice->fp_date)->format('d M Y')
                : '-';
        });

        $sorted = LifecycleSorter::sort($invoices);

        return view('pages.transactions.incoming-invoices.index', [
            'incomingInvoices' => $this->paginateCollection($sorted),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $clients     = Client::all();
        $departments = Department::all();
        $vendors     = Vendor::all();
        $items       = Item::with('itemSpecs')->get();
        $taxes       = Tax::all();

        $orders = Order::select('id', 'ord_number', 'client_id', 'department_id', 'project_name', 'cur', 'amount')
            ->orderBy('ord_date', 'desc')
            ->get();

        $invoices = IncomingInvoice::with('vendor')
            ->select('id', 'inv_number', 'vendor_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $forOrderId = $request->query('for_order');

        return view('pages.transactions.incoming-invoices.create', compact('clients', 'departments', 'vendors', 'items', 'taxes', 'orders', 'invoices', 'forOrderId'));
    }

    public function store(Request $request)
    {
        // Start the database transaction
        DB::beginTransaction();

        try {
            $items        = $request->input('items', []);
            $cleanedItems = [];
            foreach ($items as $key => $item) {
                if (strpos($key, '_INDEX_') !== false) {
                    continue;
                }

                $basePrice          = $item['base_unit_price'] ?? null;
                $displayPrice       = $item['unit_price'] ?? 0;
                $usedPrice          = $basePrice !== null ? $basePrice : $displayPrice;
                $cleanedItems[$key] = [
                    'description' => $item['description'] ?? null,
                    'quantity'    => $item['quantity'] ?? 0,
                    'unit_price'  => str_replace(',', '', $usedPrice),
                    // 'subtotal'    => str_replace(',', '', $item['subtotal'] ?? 0),
                ];
            }
            $request->merge(['items' => $cleanedItems]);

            // 1. VALIDATION ----------------------------------------------------------
            $validated = $request->validate([
                'vendor_id'             => 'required|exists:vendors,id',
                'inv_number'            => 'nullable|string|max:255',
                'inv_received_date'     => 'nullable|date',
                'due_date'              => 'nullable|date',
                'fp_date'               => 'nullable|date',
                'fp_number'             => 'nullable|string|max:255',
                'department_id'         => 'required|exists:departments,id',
                'usage_department_id'   => 'nullable|exists:departments,id',
                'cur'                   => 'required|string|max:10',
                'remark'                => 'nullable|string|max:1000',
                'ppn'                   => 'nullable|boolean',

                // Optional connections
                'referenced_invoice_id' => 'nullable|exists:incoming_invoices,id',
                'order_id'              => 'nullable|exists:orders,id',

                // Items
                'items'                 => 'required|array|min:1',
                'items.*.description'   => 'nullable|string',
                'items.*.quantity'      => 'required|numeric|min:1',
                'items.*.unit_price'    => 'required|numeric|min:0',
                'items.*.subtotal'      => 'nullable|numeric|min:0',

                // Taxes
                'incoming_tax_ids'      => 'nullable|array',
                'incoming_tax_ids.*'    => 'exists:taxes,id',

                // Agreement Fee
                'agreement_percentage'  => 'nullable|numeric|min:0|max:100',
            ]);

            // 2. RE-CALCULATE SUBTOTALS (SECURITY) ----------------------------------
            $totalItemSubtotal = 0;
            $processedItems    = [];

            foreach ($validated['items'] as $item) {
                $qty  = floatval($item['quantity'] ?? 0);
                $unit = floatval(str_replace(',', '', $item['unit_price'] ?? 0));
                $sub  = $qty * $unit;

                $totalItemSubtotal += $sub;

                $processedItems[] = array_merge($item, [
                    'quantity'            => $qty,
                    'unit_price'          => $unit,
                    'calculated_subtotal' => $sub,
                ]);
            }

            // Replace $items with processed version
            $items = $processedItems;

            // 3. AGREEMENT + TAX DEDUCTION RATE (ADDITIVE) ---------------------------
            $base = $totalItemSubtotal;

            // Add PPN if checkbox is checked
            $isPpn        = ! empty($validated['ppn']);
            $grossWithPpn = $isPpn ? $base * 1.11 : $base;

            $totalDeductionRate  = 0.0;

            // Agreement Fee (as %)
            if (! empty($validated['agreement_percentage'])) {
                $totalDeductionRate += $validated['agreement_percentage'] / 100;
            }

            // Incoming Taxes (sum of %)
            $taxIds         = $validated['incoming_tax_ids'] ?? [];
            $taxPercentages = [];

            if (! empty($taxIds)) {
                $taxPercentages = Tax::whereIn('id', $taxIds)->pluck('tax_percentage')->toArray();
                foreach ($taxPercentages as $pct) {
                    $totalDeductionRate += $pct / 100;
                }
            }

            $pph23Rate = 0.0;
            if (! empty($validated['order_id'])) {
                $isPrepaid = Order::where('id', $validated['order_id'])
                    ->value('is_pph23_prepaid');

                if ($isPrepaid) {
                    $pph23Rate           = 0.02;
                    $totalDeductionRate += $pph23Rate;
                }
            }

            // 4. FINAL AMOUNT -------------------------------------------------------
            // Deduction AMOUNT = base × totalDeductionRate
            $deductionAmount = $base * $totalDeductionRate;

            // Final amount
            $finalAmount = $grossWithPpn - $deductionAmount;

            // Ensure non-negative
            if ($finalAmount < 0) {
                $finalAmount = 0;
            }

            // 6. SAVE PARENT RECORD --------------------------------------------------
            $invoice = IncomingInvoice::create([
                // ... (Invoice fields)
                'vendor_id'             => $validated['vendor_id'],
                'inv_number'            => $validated['inv_number'],
                'inv_received_date'     => $validated['inv_received_date'],
                'due_date'              => $validated['due_date'] ?? null,
                'fp_date'               => $validated['fp_date'],
                'fp_number'             => $validated['fp_number'],
                'department_id'         => $validated['department_id'],
                'usage_department_id'   => $validated['usage_department_id'] ?? null,
                'cur'                   => $validated['cur'],
                'remark'                => $validated['remark'] ?? null,
                'order_id'              => $validated['order_id'] ?? null,
                'referenced_invoice_id' => $validated['referenced_invoice_id'] ?? null,

                'profit_percentage'     => $validated['agreement_percentage'] ?? null,
                'amount'                => $finalAmount,
                'ppn'                   => ! empty($validated['ppn']),
            ]);

            // 7. SAVE ITEMS ----------------------------------------------------------
            foreach ($items as $item) {
                IncomingInvoiceItem::create([
                    'incoming_invoice_id' => $invoice->id,
                    'description'         => $item['description'],
                    'quantity'            => $item['quantity'] ?? 0,
                    'unit_price'          => $item['unit_price'] ?? 0,
                    'subtotal'            => $item['calculated_subtotal'],
                ]);
            }

            // 8. SAVE TAX RELATION ---------------------------------------------------
            $taxIds = $validated['incoming_tax_ids'] ?? [];
            if (! empty($taxIds)) {
                $invoice->taxes()->sync($taxIds);
            }

            // Commit the transaction since everything succeeded
            DB::commit();

            // 10. DONE ---------------------------------------------------------------
            return redirect()->route('incoming-invoices.index')
                ->with('success', 'Incoming Invoice created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation exceptions should be logged separately if needed, but Laravel handles
            // returning the errors automatically. We still roll back the transaction just in case.
            DB::rollBack();

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->input());

        } catch (\Exception $e) {
            // Rollback the transaction on any other database or logic error
            DB::rollBack();

            // Redirect back with a generic error message
            return redirect()->back()
                ->withInput($request->input())
                ->with('error', 'Failed to create Incoming Invoice. An unexpected error occurred. Please check the logs.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomingInvoice $incomingInvoice)
    {
        $incomingInvoice->load([
            'vendor',
            'order.department',
            'items',
        ]);
        return view('pages.transactions.incoming-invoices.show', [
            'invoice' => $incomingInvoice,
        ]);
    }

    /**
     * Update multiple specified Incoming Invoices in storage (Administrative Finalization).
     */
    public function massUpdate(Request $request)
    {
        // 1. Validation for the array of invoices
        $validated = $request->validate([
            'invoices'                     => 'required|array',

            // Validation rules for each item in the 'invoices' array
            'invoices.*.id'                => 'required|exists:incoming_invoices,id',
            'invoices.*.inv_number'        => 'nullable|string|max:255',
            'invoices.*.inv_received_date' => 'nullable|date',
            'invoices.*.fp_date'           => 'nullable|date',
            'invoices.*.fp_number'         => 'nullable|string|max:255',
        ]);

        foreach ($validated['invoices'] as $invoiceData) {
            $fpNumber  = $invoiceData['fp_number'] ?? null;
            $invoiceId = $invoiceData['id'];

            if ($fpNumber !== null) {
                $exists = IncomingInvoice::where('fp_number', $fpNumber)
                    ->where('id', '!=', $invoiceId)
                    ->exists();

                if ($exists) {
                    // Correct error key for array field
                    return back()->withErrors([
                        "invoices.{$invoiceId}.fp_number" => "FP Number '{$fpNumber}' is already used by another incoming invoice.",
                    ])->withInput();
                }
            }
        }

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
                if (! empty($invoiceChanges)) {
                    $invoice->update($invoiceChanges);
                    $invoicesUpdatedCount++;
                }
            }
        }

        if ($invoicesUpdatedCount === 0) {
            return redirect()->route('incoming-invoices.index')
                ->with('info', 'No changes detected. Nothing was updated.');
        }

        // 3. Redirect
        return redirect()->route('incoming-invoices.index')->with('success', $invoicesUpdatedCount . ' Incoming Invoice(s) successfully finalized and updated.');
    }

    /**
     * Export the list of incoming invoices to an Excel file.
     */
    public function export()
    {
        $filename = 'incoming_invoices_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new IncomingInvoicesExport, $filename);
    }

    public function updateField(Request $request, IncomingInvoice $incomingInvoice, string $field)
    {
        if (! in_array($field, ['payment_date', 'fp_number'])) {
            return response()->json(['error' => 'Invalid field'], 400);
        }

        $rules = [];
        if ($field === 'payment_date') {
            $rules[$field] = ['nullable', 'date'];
        } else {
            $rules[$field] = ['nullable', 'string', 'max:255', Rule::unique('incoming_invoices', 'fp_number')->ignore($incomingInvoice->id)];
        }

        // This will automatically return 422 + JSON errors on failure (because it's an AJAX/XHR request)
        $validated = $request->validate($rules);

        $incomingInvoice->update([
            $field => $validated[$field],
        ]);

        return response()->json(['success' => true]);
    }

    public function getOrderData(Order $order)
    {
        $order->load([
            'purchaseOrder',
            'outgoingInvoices.lineItems',
        ]);

        $outgoingInvoice = $order->outgoingInvoices->first();

        $ordDate         = Carbon::parse($order->ord_date);
        $invReceivedDate = $ordDate->format('Y-m-d');
        $fpDate          = $ordDate->format('Y-m-d');
        $dueDate         = $ordDate->copy()->addDays(14)->format('Y-m-d');
        $invNumber       = NumberGenerator::generateIncomingInvoiceNumber($ordDate);

        $vendorId            = 1;
        $agreementPercentage = 6.00;

        $items = [];
        if ($outgoingInvoice) {
            $items = $outgoingInvoice->lineItems->map(function ($lineItem) use ($order) {
                return [
                    'description' => $order->project_name ?? 'Project not specified',
                    'quantity'    => $lineItem->quantity,
                    'unit_price'  => $lineItem->item?->item_price ?? 0,
                    'subtotal'    => $lineItem->subtotal,
                ];
            })->values()->toArray();
        }

        return response()->json([
            'id'                   => $order->id,
            'ord_number'           => $order->ord_number,
            'inv_number'           => $invNumber,
            'ord_date'             => $invReceivedDate,
            'vendor_id'            => $vendorId,
            'department_id'        => $order->department_id,
            'cur'                  => $order->cur ?? 'IDR',
            'project_name'         => $order->project_name,
            'inv_received_date'    => $invReceivedDate,
            'fp_date'              => $fpDate,
            'due_date'             => $dueDate,
            'agreement_percentage' => $agreementPercentage,
            'incoming_tax_ids'     => [],
            'items'                => $items,
            'is_pph23_prepaid'     => $order->is_pph23_prepaid,
            'ppn'                  => $order->is_pph23_prepaid,
        ]);
    }

    public function getTemplateData(IncomingInvoice $incomingInvoice)
    {
        $incomingInvoice->load(['vendor', 'department', 'taxes', 'items']);

        return response()->json([
            'id'                  => $incomingInvoice->id,
            'vendor_id'           => $incomingInvoice->vendor_id,
            'department_id'       => $incomingInvoice->department_id,
            'usage_department_id' => $incomingInvoice->usage_department_id,
            'cur'                 => $incomingInvoice->cur,
            // 'agreement_percentage' => $incomingInvoice->profit_percentage ?? 0,
            'inv_received_date'   => $incomingInvoice->inv_received_date,
            'inv_received_date'   => $incomingInvoice->inv_received_date,
            'fp_date'             => $incomingInvoice->fp_date,
            'fp_number'           => $incomingInvoice->fp_number,
            'remark'              => $incomingInvoice->remark,
            'incoming_tax_ids'    => $incomingInvoice->taxes->pluck('id')->toArray(),
            'items'               => $incomingInvoice->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'subtotal'    => $item->subtotal,
                ];
            })->toArray(),
            'inv_number'          => $incomingInvoice->inv_number,
            'ppn'                 => $incomingInvoice->ppn,
        ]);
    }
}
