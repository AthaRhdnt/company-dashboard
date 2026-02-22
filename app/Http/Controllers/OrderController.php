<?php
namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use App\Models\Client;
use App\Models\Department;
use App\Models\IncomingInvoice;
use App\Models\Item;
use App\Models\ItemSpec;
use App\Models\Order;
use App\Models\OutgoingInvoice;
use App\Models\OutgoingInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\Tax;
use App\Models\Vendor;
use App\Services\LifecycleSorter;
use App\Services\ManualPaginator;
use App\Services\NumberGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{

    use ManualPaginator;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients     = Client::all();
        $departments = Department::all();

        $orders = Order::with([
            'client',
            'department',
            'purchaseOrder',
            'outgoingInvoices',
            'incomingInvoices',
        ])->get();

        $sorted = LifecycleSorter::sort($orders);

        return view('pages.transactions.orders.index', [
            'orders'      => $this->paginateCollection($sorted),
            'clients'     => $clients,
            'departments' => $departments,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients     = Client::all();
        $departments = Department::all();
        $vendors     = Vendor::all();
        $items       = Item::with('itemSpecs')->get();
        $taxes       = Tax::all();

        $orders = Order::select('id', 'ord_number', 'client_id', 'department_id', 'project_name', 'cur', 'amount')
            ->orderBy('ord_date', 'desc')
            ->get();

        $suggestedOrderNumber = NumberGenerator::generateOrderNumber(now());

        return view('pages.transactions.orders.create', compact('clients', 'departments', 'vendors', 'items', 'taxes', 'orders', 'suggestedOrderNumber'));
    }

    public function create2()
    {
        $clients     = Client::all();
        $departments = Department::all();
        $vendors     = Vendor::all();
        $items       = Item::with('itemSpecs')->get();
        $taxes       = Tax::all();

        $orders = Order::select('id', 'ord_number', 'client_id', 'department_id', 'project_name', 'cur', 'amount')
            ->orderBy('ord_date', 'desc')
            ->get();

        return view('pages.transactions.orders.create2', compact('clients', 'departments', 'vendors', 'items', 'taxes', 'orders'));
    }

    /**
     * Store a newly created resource in storage (The Single-Entry Atomic Transaction).
     */
    public function store(Request $request)
    {
        $items        = $request->input('items', []);
        $cleanedItems = [];
        foreach ($items as $key => $item) {
            // Skip template placeholder and empty items
            if ($key === '_INDEX_' || ! is_array($item) || empty($item['item_id'] ?? null)) {
                continue;
            }
            // Clean subtotal
            $subtotal       = str_replace(',', '', $item['subtotal'] ?? '0');
            $cleanedItems[] = [
                'item_id'       => $item['item_id'],
                'quantity'      => $item['quantity'],
                'subtotal'      => $subtotal,
                'item_spec_ids' => $item['item_spec_ids'] ?? [],
            ];
        }

        // Merge cleaned items back into request
        $request->merge(['items' => $cleanedItems]);

        // Now validate — no _INDEX_ exists anymore
        try {
            $validated = $request->validate([
                'client_id'             => 'required|exists:clients,id',
                'department_id'         => 'required|exists:departments,id',
                'ord_number'            => 'required|string|unique:orders,ord_number',
                'ord_date'              => 'required|date',
                'project_name'          => 'nullable|string|max:255',
                'cur'                   => 'required|string|max:10',
                'remark'                => 'nullable|string|max:255',
                'po_number'             => 'required|string',
                'po_date'               => 'required|date',
                'is_pph23_prepaid'      => 'nullable|boolean',
                'items'                 => 'required|array|min:1',
                'items.*.item_id'       => 'required|exists:items,id',
                'items.*.quantity'      => 'required|integer|min:1',
                'items.*.subtotal'      => 'required|numeric|min:0',
                'items.*.item_spec_ids' => 'nullable|array',
            ]);

            $totalCustomerPayment = collect($cleanedItems)->sum('subtotal');
            $isPph23Prepaid       = (bool) ($validated['is_pph23_prepaid'] ?? false);

            if ($isPph23Prepaid) {
                $currentRemark = trim($validated['remark'] ?? '');
                $normalized    = preg_replace('/[^a-z0-9]/', '', strtolower($currentRemark));
                if (! str_contains($normalized, 'pph23')) {
                    $validated['remark'] = $currentRemark !== ''
                        ? $currentRemark . ', PPh23'
                        : 'PPh23';
                }
            }

            DB::beginTransaction();

            // 1. Create Purchase Order
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $validated['po_number'],
                'po_date'   => $validated['po_date'],
            ]);

            // 2. Create Order
            $order = Order::create([
                'ord_number'        => $validated['ord_number'],
                'ord_date'          => $validated['ord_date'],
                'client_id'         => $validated['client_id'],
                'department_id'     => $validated['department_id'],
                'project_name'      => $validated['project_name'],
                'cur'               => $validated['cur'],
                'amount'            => $totalCustomerPayment,
                'purchase_order_id' => $purchaseOrder->id,
                'remark'            => $validated['remark'],
                'is_pph23_prepaid'  => $isPph23Prepaid,
            ]);

            // 3. Create Outgoing Invoice
            $invDate   = $validated['ord_date'];
            $dueDate   = Carbon::parse($invDate)->addDays(14);
            $invNumber = NumberGenerator::generateOutgoingInvoiceNumber(Carbon::parse($invDate));
            $rptNumber = NumberGenerator::generateReceiptNumber(Carbon::parse($invDate));
            $doNumber  = NumberGenerator::generateDeliverOrderNumber(Carbon::parse($invDate));

            $outgoingInvoice = OutgoingInvoice::create([
                'order_id'      => $order->id,
                'inv_number'    => $invNumber,
                'client_id'     => $validated['client_id'],
                'department_id' => $validated['department_id'],
                'amount'        => $totalCustomerPayment,
                'cur'           => $validated['cur'],
                'po_number'     => $validated['po_number'],
                'inv_date'      => $invDate,
                'due_date'      => $dueDate,
                'rpt_number'    => $rptNumber,
                'do_number'     => $doNumber,
                'remark'        => $validated['remark'],
            ]);

            // 4. Create Invoice Items & Attach Specs
            foreach ($cleanedItems as $index => $itemData) {
                $invoiceItem = OutgoingInvoiceItem::create([
                    'outgoing_invoice_id' => $outgoingInvoice->id,
                    'item_id'             => $itemData['item_id'],
                    'quantity'            => $itemData['quantity'],
                    'subtotal'            => $itemData['subtotal'],
                ]);

                if (! empty($itemData['item_spec_ids'])) {
                    $invoiceItem->specs()->attach($itemData['item_spec_ids']);
                }
            }

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'New Order and all related documents successfully created.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * (Prepares data array for the x-pages.show component)
     */
    public function show(Order $order)
    {
        // Fetch all static lists
        $clients     = Client::all();
        $departments = Department::all();
        $vendors     = Vendor::all();
        $items       = Item::all();

        // CRITICAL IMPROVEMENT: Move heavy DB query out of the Blade view's @php block.
        // Load ALL ItemSpecs grouped by item_id and prepare JSON for Alpine/JS consumption.
        $allItemSpecs     = ItemSpec::all()->groupBy('item_id');
        $allItemSpecsJson = $allItemSpecs->toJson();

        // Eager load all required relationships
        // NOTE: The load paths are correct for deep nested data.
        $order->load([
            'client',
            'department',
            'purchaseOrder',
            'outgoingInvoices.lineItems.item',
            'outgoingInvoices.lineItems.specs',
            'outgoingInvoices.taxes',
            'incomingInvoices.vendor',
            'incomingInvoices.taxes',
        ]);

        // Determine the primary invoices. This will be null if no relation exists.
        $outgoingInvoice = $order->outgoingInvoices->first();

        // Calculate profit (will be 0 if either invoice is null)
        $revenue = optional($outgoingInvoice)->amount ?? 0;
        $cost    = $order->incomingInvoices->sum('amount');
        $profit  = $revenue - $cost;

        return view('pages.transactions.orders.show', compact(
            'order',
            'clients',
            'departments',
            'vendors',
            'items',
            'outgoingInvoice',
            'profit',
            'cost',
            'allItemSpecs',
            'allItemSpecsJson'
        ));
    }

    /**
     * Update the specified resource in storage.
     * * Handles Subsequent Edits (Only updating document numbers/dates).
     */
    public function update(Request $request, Order $order)
    {
        $outgoingInvoice          = $order->outgoingInvoices->first();
        $existingIncomingInvoices = $order->incomingInvoices->keyBy('id');

        // --- VALIDATION ---
        $rules = [
            // Order
            'ord_number'                            => 'required|string|max:255|unique:orders,ord_number,' . $order->id,
            'ord_date'                              => 'required|date',
            'project_name'                          => 'nullable|string|max:255',
            'client_id'                             => 'required|exists:clients,id',
            'department_id'                         => 'required|exists:departments,id',
            'cur'                                   => 'required|string|max:10',
            'remark'                                => 'nullable|string|max:255',
            'is_pph23_prepaid'                      => 'boolean',

            // Purchase Order
            'po_number'                             => 'required|string|max:255|unique:purchase_orders,po_number,' . optional($order->purchaseOrder)->id . ',id',
            'po_date'                               => 'required|date',

            // Outgoing Invoice (only if it exists)
            'inv_number'                            => $outgoingInvoice ? 'nullable|string|max:255|unique:outgoing_invoices,inv_number,' . $outgoingInvoice->id . ',id' : 'nullable|string|max:255',
            'inv_date'                              => 'nullable|date',
            'due_date'                              => 'nullable|date',
            'fp_number'                             => ['nullable', 'string', 'max:255', Rule::unique('outgoing_invoices', 'fp_number')->ignore($outgoingInvoice?->id)],
            'income_date'                           => 'nullable|date',

            // Incoming Invoices (array of invoices)
            'incoming_invoices'                     => 'nullable|array',
            'incoming_invoices.*.id'                => 'required|exists:incoming_invoices,id,order_id,' . $order->id,
            'incoming_invoices.*.vendor_id'         => 'required_with:incoming_invoices.*.id|exists:vendors,id',
            'incoming_invoices.*.inv_number'        => 'nullable|string|max:255',
            'incoming_invoices.*.inv_received_date' => 'nullable|date',
            'incoming_invoices.*.fp_date'           => 'nullable|date',
            'incoming_invoices.*.payment_date'      => 'nullable|date',
            'incoming_invoices.*.amount'            => 'nullable|numeric|min:0',

            // Line Items (for outgoing invoice)
            'line_items'                            => 'nullable|array',
            'line_items.*.item_id'                  => 'required_with:line_items|exists:items,id',
            'line_items.*.quantity'                 => 'required_with:line_items|numeric|min:0',
            'line_items.*.subtotal'                 => 'required_with:line_items|numeric|min:0',
            'line_items.*.specs'                    => 'nullable|array',
            'line_items.*.specs.*'                  => 'exists:item_specs,id',
            'line_items.*.delete'                   => 'nullable|boolean',

            'new_line_items'                        => 'nullable|array',
            'new_line_items.*.item_id'              => 'required_with:new_line_items|exists:items,id',
            'new_line_items.*.quantity'             => 'required_with:new_line_items|numeric|min:0',
            'new_line_items.*.subtotal'             => 'required_with:new_line_items|numeric|min:0',
            'new_line_items.*.specs'                => 'nullable|array',
            'new_line_items.*.specs.*'              => 'exists:item_specs,id',
        ];

        $validated      = $request->validate($rules);
        $isPph23Prepaid = $request->boolean('is_pph23_prepaid');

        DB::beginTransaction();
        try {
            // --- PROCESS OUTGOING INVOICE LINE ITEMS ---
            $totalSubtotal = 0;
            $itemsToKeep   = [];

            if ($outgoingInvoice && ! empty($validated['line_items'])) {
                foreach ($validated['line_items'] as $itemId => $itemData) {
                    if (($itemData['delete'] ?? null) == '1') {
                        continue;
                    }

                    $subtotal       = (float) $itemData['subtotal'];
                    $totalSubtotal += $subtotal;

                    if ($lineItem = $outgoingInvoice->lineItems()->find($itemId)) {
                        $lineItem->update([
                            'item_id'  => $itemData['item_id'],
                            'quantity' => $itemData['quantity'],
                            'subtotal' => $subtotal,
                        ]);
                        $lineItem->specs()->sync($itemData['specs'] ?? []);
                        $itemsToKeep[] = $itemId;
                    }
                }
                $outgoingInvoice->lineItems()->whereNotIn('id', $itemsToKeep)->delete();
            }

            if ($outgoingInvoice && ! empty($validated['new_line_items'])) {
                foreach ($validated['new_line_items'] as $itemData) {
                    $subtotal       = (float) $itemData['subtotal'];
                    $totalSubtotal += $subtotal;

                    $newLineItem = $outgoingInvoice->lineItems()->create([
                        'item_id'  => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'subtotal' => $subtotal,
                    ]);
                    $newLineItem->specs()->sync($itemData['specs'] ?? []);
                }
            }

            $newOrderAmount  = round($totalSubtotal, 2);

            // --- UPDATE ORDER ---
            $order->update([
                'ord_number'       => $validated['ord_number'],
                'ord_date'         => $validated['ord_date'],
                'project_name'     => $validated['project_name'],
                'client_id'        => $validated['client_id'],
                'department_id'    => $validated['department_id'],
                'cur'              => $validated['cur'],
                'amount'           => $newOrderAmount,
                'remark'           => $isPph23Prepaid
                    ? ($validated['remark'] ?? '') . ', PPH23 prepaid'
                    : $validated['remark'] ?? null,
                'is_pph23_prepaid' => $isPph23Prepaid,
            ]);

            // --- UPDATE PURCHASE ORDER ---
            if ($order->purchaseOrder) {
                $order->purchaseOrder->update([
                    'po_number' => $validated['po_number'],
                    'po_date'   => $validated['po_date'],
                ]);
            }

            // --- UPDATE OUTGOING INVOICE ---
            if ($outgoingInvoice) {
                $invDate = $validated['inv_date'] ?? $validated['ord_date'];
                $dueDate = $validated['due_date'] ?? Carbon::parse($invDate)->addDays(14)->format('Y-m-d');

                $outgoingInvoice->update([
                    'inv_number'    => $validated['inv_number'],
                    'inv_date'      => $invDate,
                    'due_date'      => $dueDate,
                    'fp_number'     => $validated['fp_number'],
                    'income_date'   => $validated['income_date'],
                    'client_id'     => $validated['client_id'],
                    'department_id' => $validated['department_id'],
                    'amount'        => $newOrderAmount,
                ]);
            }

            // --- PROCESS INCOMING INVOICES ---
            if (! empty($validated['incoming_invoices'])) {
                foreach ($validated['incoming_invoices'] as $invoiceData) {
                    $invoiceId = $invoiceData['id'] ?? null;
                    if (empty($invoiceData['id'])) {
                        continue;
                    }
                    // Skip if no ID (shouldn't happen)

                    $invoice = IncomingInvoice::where('id', $invoiceId)
                        ->where('order_id', $order->id)
                        ->first();

                    if ($invoice) {
                        $invoice->update([
                            'vendor_id'         => $invoiceData['vendor_id'],
                            'inv_number'        => $invoiceData['inv_number'] ?? null,
                            'inv_received_date' => $invoiceData['inv_received_date'] ?? null,
                            'fp_date'           => $invoiceData['fp_date'] ?? null,
                            'payment_date'      => $invoiceData['payment_date'] ?? null,
                            'amount'            => $invoiceData['amount'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update order. Please check the logs.');
        }
    }

    /**
     * Update multiple specified Orders in storage (e.g., updating Order Date or Project Name).
     */
    public function massUpdate(Request $request, Order $order)
    {
        // Note: The $order parameter is a route model binding placeholder
        // and is not used inside the loop, but it's kept for context/consistency.

        $validated = $request->validate([
            'orders'                 => 'required|array',
            'orders.*.id'            => 'required|exists:orders,id',

            // Order Fields
            'orders.*.ord_number'    => 'required|string|max:255',
            'orders.*.ord_date'      => 'required|date',
            'orders.*.project_name'  => 'nullable|string|max:255',
            'orders.*.client_id'     => 'required|exists:clients,id',
            'orders.*.department_id' => 'required|exists:departments,id',
            'orders.*.amount'        => 'required|numeric|min:0',
            'orders.*.cur'           => 'required|string|max:10',
            'orders.*.remark'        => 'nullable|string|max:255',

            // Purchase Order Field
            'orders.*.po_number'     => 'required|string|max:255',
            'orders.*.po_date'       => 'required|date',
        ]);

        $ordersUpdatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($validated['orders'] as $orderData) {
                $order = Order::with('purchaseOrder')->find($orderData['id']);

                if (! $order) {
                    continue; // Skip if order not found
                }

                // --- 1. Update Core Order fields (Only changed fields) ---

                // Define all fields that belong to the Order model
                $orderFields = collect($orderData)->only([
                    'ord_number', 'ord_date', 'project_name', 'client_id',
                    'department_id', 'amount', 'cur', 'remark',
                ])->toArray();

                // Fill the model instance with new data and get the differences (dirty attributes)
                $order->fill($orderFields);
                $orderChanges = $order->getDirty();

                if (! empty($orderChanges)) {
                    $order->update($orderChanges);
                    $ordersUpdatedCount++;
                }

                // --- 2. Update Related Purchase Order (Only changed fields) ---
                $poChanges = [];
                if ($order->purchaseOrder) {
                    // Define all fields that belong to the PurchaseOrder model
                    $poFields = collect($orderData)->only(['po_number', 'po_date'])->toArray();

                    // Fill the related model instance and get the differences
                    $order->purchaseOrder->fill($poFields);
                    $poChanges = $order->purchaseOrder->getDirty();

                    if (! empty($poChanges)) {
                        $order->purchaseOrder->update($poChanges);
                        // No need to increment $ordersUpdatedCount again, as it's part of the order update
                    }
                }

                // --- 3. Update Outgoing/Incoming Invoices (Only based on confirmed changes) ---

                $outgoingInvoiceChanges = [];

                // a. Propagate Order changes to Outgoing Invoices
                $orderFieldsForOI = ['amount', 'cur', 'client_id', 'department_id'];
                foreach ($orderFieldsForOI as $field) {
                    if (isset($orderChanges[$field])) {
                        $outgoingInvoiceChanges[$field] = $orderChanges[$field];
                    }
                }

                // b. Propagate PO changes to Outgoing Invoices (only po_number)
                if (isset($poChanges['po_number'])) {
                    $outgoingInvoiceChanges['po_number'] = $poChanges['po_number'];
                }

                if (! empty($outgoingInvoiceChanges)) {
                    $order->outgoingInvoices()->update($outgoingInvoiceChanges);
                }
            }

            DB::commit();
            return redirect()->route('orders.index')->with('success', $ordersUpdatedCount . ' Order(s) successfully updated (only changed data written).');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to mass update orders. Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Deletes the Order and its cascade-related documents (Invoices, Items, PO).
     */
    public function destroy(Order $order)
    {
        DB::beginTransaction();
        try {
            // 1. Delete Invoice Items and Detach Taxes (Must be done before deleting parent invoices)
            $order->outgoingInvoices->each(function ($invoice) {
                $invoice->lineItems()->delete();
                $invoice->taxes()->detach();
            });
            $order->incomingInvoices->each(function ($invoice) {
                $invoice->taxes()->detach();
            });

            // 2. Delete Outgoing/Incoming Invoices
            $order->outgoingInvoices()->delete();
            $order->incomingInvoices()->delete();

            // 3. Delete Purchase Order
            if ($order->purchaseOrder) {
                $order->purchaseOrder()->delete();
            }

            // 4. Delete the Order itself (The core record)
            $order->delete();

            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Order and all related documents successfully deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete order. Error: ' . $e->getMessage());
        }
    }

    public function cancel(Order $order)
    {
        DB::beginTransaction();
        try {
            // 1) Cancel the order
            if (! str_contains($order->remark ?? '', 'Cancelled')) {
                $order->remark = $order->remark
                    ? $order->remark . ', Cancelled'
                    : 'Cancelled';
            }
            $order->amount = 0;
            $order->save();

            // 2) Outgoing invoices (always exist)
            foreach ($order->outgoingInvoices as $invoice) {

                // Set invoice amount & remark
                $invoice->amount = 0;
                if (! str_contains($invoice->remark ?? '', 'Cancelled')) {
                    $invoice->remark = $invoice->remark
                        ? $invoice->remark . ', Cancelled'
                        : 'Cancelled';
                }
                $invoice->save();

                // Set line items subtotal = 0 (if they exist)
                $invoice->lineItems()->update(['subtotal' => 0]);
            }

            // 3) Incoming invoices (may not exist)
            if ($order->incomingInvoices->isNotEmpty()) {
                foreach ($order->incomingInvoices as $invoice) {
                    $invoice->amount = 0;
                    if (! str_contains($invoice->remark ?? '', 'Cancelled')) {
                        $invoice->remark = $invoice->remark
                            ? $invoice->remark . ', Cancelled'
                            : 'Cancelled';
                    }
                    $invoice->save();
                }
            }

            DB::commit();
            return redirect()
                ->route('orders.index')
                ->with('success', 'Order successfully cancelled.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Cancellation failed: ' . $e->getMessage());
        }
    }

    /**
     * Export the list of orders to an Excel file.
     */
    public function export()
    {
        $filename = 'orders_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new OrdersExport, $filename);
    }

    /**
     * Fetch data of an existing order to pre-fill the creation form via AJAX.
     */
    public function getTemplateData(Order $order)
    {
        $order->load([
            'client:id,client_name',
            'department:id,department_code',
            'purchaseOrder:id,po_number,po_date',
            'incomingInvoices.taxes:id,tax_name,tax_percentage',
            'outgoingInvoices.taxes:id,tax_name,tax_percentage',
            'incomingInvoices.vendor:id,vendor_name',
            'outgoingInvoices.lineItems.item:id,item_name,item_price',
            'outgoingInvoices.lineItems.specs:id,item_description',
        ]);

        $incomingInvoice = $order->incomingInvoices->first();
        $outgoingInvoice = $order->outgoingInvoices->first();

        // Safe date helper
        $safeDate = function ($date) {
            if (! $date) {
                return null;
            }

            return is_string($date) ? $date : $date->format('Y-m-d');
        };

        $suggestedOrderNumber = NumberGenerator::generateOrderNumber(now());

        return response()->json([
            // Core Order Info
            'id'                   => $order->id,
            'ord_number'           => $suggestedOrderNumber,
            'ord_date'             => $safeDate($order->ord_date),
            'client_id'            => $order->client_id,
            'department_id'        => $order->department_id,
            'project_name'         => $order->project_name,
            'cur'                  => $order->cur,
            'amount'               => $order->amount,
            'remark'               => $order->remark,

            // Purchase Order
            'po_number'            => optional($order->purchaseOrder)->po_number,
            'po_date'              => $safeDate(optional($order->purchaseOrder)->po_date),

            // Vendor & Profit
            'vendor_id'            => optional($incomingInvoice)->vendor_id,
            'agreement_percentage' => optional($incomingInvoice)->profit_percentage ?? 0,

            // Taxes
            'is_pph23_prepaid'     => (bool) $order->is_pph23_prepaid,
            'incoming_tax_ids'     => $incomingInvoice
                ? $incomingInvoice->taxes->pluck('id')->toArray()
                : [],
            'outgoing_tax_ids'     => $outgoingInvoice
                ? $outgoingInvoice->taxes->pluck('id')->toArray()
                : [],

            // Items
            'items'                => $outgoingInvoice
                ? $outgoingInvoice->lineItems->map(function ($item) {
                return [
                    'item_id'  => $item->item_id,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'specs'    => $item->specs->map(fn($s) => [
                        'id'               => $s->id,
                        'item_description' => $s->item_description,
                    ]),
                ];
            })->values()->toArray()
                : [],
        ]);
    }
}
