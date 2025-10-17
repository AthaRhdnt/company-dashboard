<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Item;
use App\Models\Order;
use App\Models\Client;
use App\Models\Vendor;
use App\Models\Department;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Models\PurchaseOrder;
use App\Models\IncomingInvoice;
use App\Models\OutgoingInvoice;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::all();
        $departments = Department::all();
        // Eager load all relationships
        $orders = Order::with(['client', 'department', 'purchaseOrder', 'outgoingInvoices', 'incomingInvoices'])
            ->orderBy('ord_date', 'desc')
            ->paginate(15);

        // --- Data Transformation for Complex/Calculated Attributes ONLY ---
        $orders->getCollection()->transform(function ($order) {
            $outgoingInvoice = $order->outgoingInvoices->first();
            $incomingInvoice = $order->incomingInvoices->first();

            // 1. Total Revenue (Formatted) - REQUIRED custom property
            $order->formatted_amount = $order->cur . ' ' . number_format($order->amount, 2, ',', '.');
            
            // 2. Outgoing Invoice Status - REQUIRED custom property
            $order->outgoing_status = optional($outgoingInvoice)->income_date ? 'Billed' : 'Pending Billing';
            
            // 3. Incoming Invoice Status - REQUIRED custom property
            $order->incoming_status = optional($incomingInvoice)->payment_date ? 'Received' : 'Pending Vendor Inv';

            // NOTE: We remove the 'client_department' and 'po_number' logic 
            // to use dot notation access directly in the view.

            return $order;
        });

        return view('pages.transactions.orders.index', compact('orders', 'clients', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::all();
        $departments = Department::all();
        $vendors = Vendor::all(); 
        $items = Item::with('itemSpecs')->get(); // Assume Item has a relationship named itemSpecs
        $taxes = Tax::all(); 

        return view('pages.transactions.orders.create', compact('clients', 'departments', 'vendors', 'items', 'taxes'));
    }

    /**
     * Store a newly created resource in storage (The Single-Entry Atomic Transaction).
     */
    public function store(Request $request)
    {
        // --- DEBUG LOG 1: Log ALL raw incoming data ---
        \Log::info('--- ORDER STORE START ---');
        \Log::info('Raw Request Data:', $request->all());

        // =======================================================
        // 💡 FIX: CLEAN CURRENCY FORMATTING AND TEMPLATE DATA
        // =======================================================
        
        // 1. Clean Total Customer Payment
        $cleanedTotalPayment = str_replace(',', '', $request->input('total_customer_payment'));
        $request->merge(['total_customer_payment' => $cleanedTotalPayment]);

        // 2. Clean Subtotals and remove the template placeholder (_INDEX_)
        $items = $request->input('items', []);
        $cleanedItems = [];

        foreach ($items as $key => $item) {
            // Skip the template placeholder entry
            if (strpos($key, '_INDEX_') !== false) {
                continue;
            }

            // Clean the subtotal field
            if (isset($item['subtotal'])) {
                $item['subtotal'] = str_replace(',', '', $item['subtotal']);
            }

            // Remove unit_price as it's for display only and not used for calculation in the controller
            if (isset($item['unit_price'])) {
                unset($item['unit_price']);
            }
            
            $cleanedItems[$key] = $item;
        }
        
        $request->merge(['items' => $cleanedItems]);

        // 3. Log the clean data before validation
        \Log::info('Merged/Cleaned Request Data:', $request->all());

        // =======================================================
        // END FIX
        // =======================================================

        $validated = $request->validate([
            // Core Order Details
            'client_id' => 'required|exists:clients,id',
            'vendor_id' => 'required|exists:vendors,id', 
            'department_id' => 'required|exists:departments,id',
            'order_no' => 'required|string|unique:orders,ord_number', // Using ord_number
            'ord_date' => 'required|date', 
            'project_name' => 'nullable|string|max:255',
            'cur' => 'required|string|max:10',
            
            // PO Details
            'po_number' => 'required|string|unique:purchase_orders,po_number',
            'po_date' => 'required|date',

            // Financials & Profit Logic (Total Payment is the revenue base)
            'total_customer_payment' => 'required|numeric|min:0', 
            'agreement_percentage' => 'required|numeric|between:0,100', // Used for profit_percentage

            // Tax IDs for documentation and CALCULATION
            'outgoing_tax_ids' => 'nullable|array',
            'incoming_tax_ids' => 'nullable|array', // This array is used for Multi-Tax Cost Calculation

            // Invoice Items Array
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.subtotal' => 'required|numeric|min:0',
            'items.*.item_spec_ids' => 'nullable|array',
        ]);

        // --- DEBUG LOG 2: Log validated data and start transaction ---
        \Log::info('Validated Data:', $validated);

        DB::beginTransaction();

        try {
            $totalCustomerPayment = $validated['total_customer_payment'];
            $agreementPercentageRate = $validated['agreement_percentage'] / 100;
            $profitPercentage = $validated['agreement_percentage'];

            // --- NEW ADDITIVE CALCULATION LOGIC ---
            // 1. Calculate Total Incoming Tax Rate (Sum of percentages)
            $totalIncomingTaxRate = 0.0;
            if (!empty($validated['incoming_tax_ids'])) {
                // Fetch the percentage for ALL selected incoming taxes
                $taxesForCost = Tax::whereIn('id', $validated['incoming_tax_ids'])->get();
                foreach ($taxesForCost as $tax) {
                    // SUMMING the rates (0.02 + 0.01 + ...)
                    $totalIncomingTaxRate += ($tax->tax_percentage / 100);
                }
            }
            
            // 2. Calculate Total Deduction Rate (Agreement + Total Tax Rate)
            $totalDeductionRate = $agreementPercentageRate + $totalIncomingTaxRate;
            
            // 3. Apply single deduction to get the Vendor Amount (Cost)
            $vendorAmount = $totalCustomerPayment * (1 - $totalDeductionRate);
            // --- END NEW LOGIC ---

            // --- DEBUG LOG 3: Log crucial financial values ---
            \Log::info('Financial Calculations:', [
                'totalCustomerPayment' => $totalCustomerPayment,
                'totalIncomingTaxRate' => $totalIncomingTaxRate,
                'totalDeductionRate' => $totalDeductionRate,
                'vendorAmount (Cost)' => $vendorAmount,
            ]);

            // 2. Record Customer PO
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $validated['po_number'], 
                'po_date' => $validated['po_date'], 
                // Note: client_id and amount are NOT in your po table. We omit them.
            ]);

            // 3. Create Core Order
            $order = Order::create([
                'ord_number' => $validated['order_no'], // Use ord_number
                'ord_date' => $validated['ord_date'],
                'client_id' => $validated['client_id'],
                'department_id' => $validated['department_id'],
                'project_name' => $validated['project_name'],
                'cur' => $validated['cur'], 
                'amount' => $totalCustomerPayment, // Amount is Total Revenue
                'purchase_order_id' => $purchaseOrder->id,
            ]);

            // 4. Generate Outgoing Invoice (Sales)
            $outgoingInvoice = OutgoingInvoice::create([
                'order_id' => $order->id, 
                'client_id' => $validated['client_id'], 
                'department_id' => $validated['department_id'], 
                'amount' => $totalCustomerPayment,
                'cur' => $validated['cur'],
                'po_number' => $validated['po_number'],
                // Other columns (inv_number, inv_date, etc.) are NULL initially
            ]);

            // 5. Calculate & Create Incoming Invoice (Cost)
            $incomingInvoice = IncomingInvoice::create([
                'order_id' => $order->id, 
                'vendor_id' => $validated['vendor_id'], 
                'department_id' => $validated['department_id'], 
                'amount' => round($vendorAmount, 2), // The calculated cost is saved
                'cur' => $validated['cur'],
                'profit_percentage' => $profitPercentage,
            ]);

            // --- DEBUG LOG 4: Log Invoice Item details before creation ---
            \Log::info('Invoice Items Data Being Processed:', $validated['items']);

            // 6. & 7. Detail Invoice Items & Apply Item Specifications
            foreach ($validated['items'] as $index => $itemData) {
                \Log::info("Processing Item Index $index", ['item_id' => $itemData['item_id'], 'subtotal' => $itemData['subtotal']]);
                $invoiceItem = InvoiceItem::create([
                    'outgoing_invoice_id' => $outgoingInvoice->id, 
                    'item_id' => $itemData['item_id'], 
                    'quantity' => $itemData['quantity'], 
                    'subtotal' => $itemData['subtotal'] // Corrected quotes and line break
                ]);
                if (! empty($itemData['item_spec_ids'])) {
                    $invoiceItem->specs()->attach($itemData['item_spec_ids']);
                    \Log::info("Attached Specs for Item $index:", $itemData['item_spec_ids']);
                }
            }

            // 8. Apply Outgoing Taxes (Linkage Only)
            if (!empty($validated['outgoing_tax_ids'])) { 
                $outgoingInvoice->taxes()->attach($validated['outgoing_tax_ids']);
                \Log::info('Attached Outgoing Tax IDs:', $validated['outgoing_tax_ids']);
            }
            
            // 9. Apply Incoming Taxes (Linkage AND Calculation Source)
            if (!empty($validated['incoming_tax_ids'])) { 
                $incomingInvoice->taxes()->attach($validated['incoming_tax_ids']);
                \Log::info('Attached Incoming Tax IDs:', $validated['incoming_tax_ids']);
            }

            // 10. Commit Transaction
            DB::commit();
            \Log::info('--- ORDER STORE SUCCESS ---');

            return redirect()->route('orders.show', $order)->with('success', 'New Order and all related documents successfully created.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order creation failed: ' . $e->getMessage());
            \Log::error('Order creation failed and transaction rolled back. Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            \Log::info('--- ORDER STORE FAILURE ---');
            return back()->withInput()->with('error', 'Failed to create order. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * (Prepares data array for the x-pages.show component)
     */
    // public function show(Order $order)
    // {
    //     // Eager load all related documents
    //     $order->load([
    //         'client', 
    //         'department', 
    //         'purchaseOrder', 
    //         'outgoingInvoices.lineItems.item', // Use lineItems relationship
    //         'outgoingInvoices.taxes',
    //         'incomingInvoices.vendor',
    //         'incomingInvoices.taxes',
    //     ]);

    //     // Access the first invoice for convenience, as only one of each is created
    //     $outgoingInvoice = $order->outgoingInvoices->first();
    //     $incomingInvoice = $order->incomingInvoices->first();
        
    //     return view('pages.transactions.orders.show', compact('order', 'outgoingInvoice', 'incomingInvoice'));
    // }
public function show(Order $order)
{
    $clients = Client::all();
    $departments = Department::all();
    $vendors = Vendor::all();
    $items = Item::all(); 
    // Eager load all related documents
    $order->load([
        'client', 
        'department', 
        'purchaseOrder', 
        // CRITICAL: Deep load lineItems and their Specs
        'outgoingInvoices.lineItems.item',
        'outgoingInvoices.lineItems.specs', // NEW: Load specs for line items
        'outgoingInvoices.taxes',
        'incomingInvoices.vendor',
        'incomingInvoices.taxes',
    ]);

    $outgoingInvoice = $order->outgoingInvoices->first();
    $incomingInvoice = $order->incomingInvoices->first();
    
    // CALCULATE PROFIT for display (assuming Revenue is Outgoing Amount)
    $revenue = optional($outgoingInvoice)->amount ?? 0;
    $cost = optional($incomingInvoice)->amount ?? 0;
    $profit = $revenue - $cost;

    return view('pages.transactions.orders.show', compact(
        'order',
        'clients', 
        'departments',
        'vendors',
        'items',
        'outgoingInvoice', 
        'incomingInvoice',
        'profit' // NEW: Pass profit to the view
    ));
}

    /**
     * Update the specified resource in storage.
     * * Handles Subsequent Edits (Only updating document numbers/dates).
     */
    // public function update(Request $request, Order $order)
    // {
    //     $outgoingInvoice = $order->outgoingInvoices->first();
    //     $incomingInvoice = $order->incomingInvoices->first();

    //     $validated = $request->validate([
    //         // Order Updates
    //         'ord_date' => 'required|date',
    //         'project_name' => 'nullable|string|max:255',
            
    //         // Outgoing Invoice Edit Fields (Unique check only on MCN Inv No)
    //         'inv_number' => 'nullable|string|max:255|unique:outgoing_invoices,inv_number,'.optional($outgoingInvoice)->id.',id',
    //         'inv_date' => 'nullable|date',
    //         'due_date' => 'nullable|date',
    //         'fp_number' => 'nullable|string|max:255',
            
    //         // Incoming Invoice Edit Fields
    //         'incoming_inv_number' => 'nullable|string|max:255',
    //         'inv_received_date' => 'nullable|date',
    //         'incoming_fp_date' => 'nullable|date',
    //     ]);
        
    //     DB::beginTransaction();
    //     try {
    //         $order->update([
    //             'ord_date' => $validated['ord_date'], 
    //             'project_name' => $validated['project_name'],]);
            
    //         if ($outgoingInvoice) {
    //             $outgoingInvoice->update([
    //                 'inv_number' => $validated['inv_number'], 
    //                 'inv_date' => $validated['inv_date'], 
    //                 'due_date' => $validated['due_date'],
    //                 'fp_number' => $validated['fp_number'],
    //             ]);
    //         }

    //         if ($incomingInvoice) {
    //             $incomingInvoice->update([
    //                 'inv_number' => $validated['incoming_inv_number'], 
    //                 'inv_received_date' => $validated['inv_received_date'], 
    //                 'fp_date' => $validated['incoming_fp_date'],
    //             ]);
    //         }
            
    //         DB::commit();
    //         return redirect()->route('orders.show', $order)->with('success', 'Order details updated successfully.');
            
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->withInput()->with('error', 'Failed to update order. Error: ' . $e->getMessage());
    //     }
    // }
public function update(Request $request, Order $order)
{
    $outgoingInvoice = $order->outgoingInvoices->first();
    $incomingInvoice = $order->incomingInvoices->first();

    // Dynamically build validation for line items and new line items
    $rules = [
        // Core Order Updates... (unchanged)
        'ord_number' => 'required|string|max:255|unique:orders,ord_number,' . $order->id, 
        'ord_date' => 'required|date',
        'project_name' => 'nullable|string|max:255',
        'client_id' => 'required|exists:clients,id',
        'department_id' => 'required|exists:departments,id',
        'cur' => 'required|string|max:10',
        'amount' => 'required|numeric|min:0', // Will be overwritten by line item total
        
        // Purchase Order Updates... (unchanged)
        'po_number' => 'required|string|max:255|unique:purchase_orders,po_number,'.optional($order->purchaseOrder)->id.',id',
        'po_date' => 'required|date', 
        
        // Outgoing Invoice Edit Fields... (unchanged)
        'inv_number' => 'nullable|string|max:255|unique:outgoing_invoices,inv_number,'.optional($outgoingInvoice)->id.',id',
        'inv_date' => 'nullable|date',
        'due_date' => 'nullable|date',
        'fp_number' => 'nullable|string|max:255',
        'income_date' => 'nullable|date',

        // Incoming Invoice Edit Fields... (unchanged)
        'incoming_inv_number' => 'nullable|string|max:255',
        'inv_received_date' => 'nullable|date',
        'inv_received_date' => 'nullable|date',
        'incoming_fp_date' => 'nullable|date',
        'payment_date' => 'nullable|date',
        'vendor_id' => 'required|exists:vendors,id',
        'profit_percentage' => 'nullable|numeric|min:0|max:100', 

        // Existing Line Items Validation
        'line_items' => 'array',
        'line_items.*.item_id' => 'required_with:line_items|exists:items,id',
        'line_items.*.quantity' => 'required_with:line_items|numeric|min:0',
        'line_items.*.subtotal' => 'required_with:line_items|numeric|min:0',
        'line_items.*.specs' => 'nullable|array',
        'line_items.*.specs.*' => 'exists:item_specs,id',
        'line_items.*.delete' => 'nullable|boolean',

        // NEW Line Items Validation
        'new_line_items' => 'array',
        'new_line_items.*.item_id' => 'required_with:new_line_items|exists:items,id',
        'new_line_items.*.quantity' => 'required_with:new_line_items|numeric|min:0',
        'new_line_items.*.subtotal' => 'required_with:new_line_items|numeric|min:0',
        'new_line_items.*.specs' => 'nullable|array',
        'new_line_items.*.specs.*' => 'exists:item_specs,id',
    ];

    $validated = $request->validate($rules);
    
    DB::beginTransaction();
    try {
        $totalSubtotal = 0;
        $itemsToKeep = []; // To track existing items that were not deleted

        // --- 1. Process EXISTING Line Items (Update/Mark for Delete) ---
        if (isset($validated['line_items']) && $outgoingInvoice) {
            foreach ($validated['line_items'] as $lineItemId => $itemData) {
                // If the 'delete' flag is set, skip update and deletion logic will handle it
                if (isset($itemData['delete']) && $itemData['delete'] == '1') {
                    continue; 
                }

                $subtotal = (float) $itemData['subtotal'];
                $totalSubtotal += $subtotal;

                $lineItem = $outgoingInvoice->lineItems()->find($lineItemId);
                if ($lineItem) {
                    $lineItem->update([
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'subtotal' => $subtotal,
                    ]);
                    
                    // Sync the many-to-many relationship for specs
                    $specs = $itemData['specs'] ?? [];
                    $lineItem->specs()->sync($specs);

                    $itemsToKeep[] = $lineItemId;
                }
            }
        }
        
        // Delete items that were in the original list but not in $itemsToKeep 
        // (i.e., those that had the 'delete' flag set)
        if ($outgoingInvoice) {
             $outgoingInvoice->lineItems()
                ->whereIn('id', $outgoingInvoice->lineItems->pluck('id'))
                ->whereNotIn('id', $itemsToKeep)
                ->delete();
        }


        // --- 2. Process NEW Line Items ---
        if (isset($validated['new_line_items']) && $outgoingInvoice) {
            foreach ($validated['new_line_items'] as $newIndex => $itemData) {
                $subtotal = (float) $itemData['subtotal'];
                $totalSubtotal += $subtotal;
                
                // Create the new line item
                $newLineItem = $outgoingInvoice->lineItems()->create([ 
                    'outgoing_invoice_id' => $outgoingInvoice->id, 
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $subtotal,
                ]);
                
                // Sync the many-to-many relationship for specs
                $specs = $itemData['specs'] ?? [];
                $newLineItem->specs()->sync($specs);
            }
        }

        // --- 3. Recalculate and update the main Order/Outgoing Invoice amount ---
        $newOrderAmount = round($totalSubtotal, 2); 
        
        // 4. Update Core Order (RECALCULATED AMOUNT)
        $order->update([
            'ord_number' => $validated['ord_number'],
            'ord_date' => $validated['ord_date'], 
            'project_name' => $validated['project_name'],
            'client_id' => $validated['client_id'],
            'department_id' => $validated['department_id'],
            'cur' => $validated['cur'],
            'amount' => $newOrderAmount, // <--- Save RECALCULATED Total Revenue
        ]);

        // 5. Update Purchase Order (Unchanged)
        if ($order->purchaseOrder) {
            $order->purchaseOrder->update([
                'po_number' => $validated['po_number'],
                'po_date' => $validated['po_date'],
            ]);
        }
        
        // 6. Update Outgoing Invoice (Sync FKs and new 'amount')
        if ($outgoingInvoice) {
            $outgoingInvoice->update([
                'inv_number' => $validated['inv_number'], 
                'inv_date' => $validated['inv_date'], 
                'due_date' => $validated['due_date'],
                'fp_number' => $validated['fp_number'],
                'income_date' => $validated['income_date'], 
                'client_id' => $validated['client_id'], 
                'department_id' => $validated['department_id'], 
                'amount' => $newOrderAmount, // <--- IMPORTANT: Sync Outgoing Invoice amount with Order amount
            ]);
        }

        // 7. Update Incoming Invoice (recalculate cost based on new revenue and profit percentage)
        if ($incomingInvoice) {
            $newProfitPercentage = (float) $validated['profit_percentage'];
            
            // Cost = Revenue * (1 - Profit Percentage / 100)
            $newCost = $newOrderAmount * (1 - ($newProfitPercentage / 100));

            $incomingInvoice->update([
                'inv_number' => $validated['incoming_inv_number'], 
                'inv_received_date' => $validated['inv_received_date'], 
                'fp_date' => $validated['incoming_fp_date'],
                'payment_date' => $validated['payment_date'],
                'vendor_id' => $validated['vendor_id'],
                'department_id' => $validated['department_id'],
                'profit_percentage' => $newProfitPercentage, 
                'amount' => round($newCost, 2), // <--- Recalculate and save Incoming Invoice amount (Cost)
            ]);
        }
        
        DB::commit();
        return redirect()->route('orders.show', $order)->with('success', 'Order details and financials updated successfully.');
        
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Failed to update order. Error: ' . $e->getMessage());
    }
}

    // --- NEW: Implement Mass Update for the Index Page ---
    /**
     * Update multiple specified Orders in storage (e.g., updating Order Date or Project Name).
     */
    // public function massUpdate(Request $request)
    // {
    //     $validated = $request->validate([
    //         'orders' => 'required|array',
    //         'orders.*.id' => 'required|exists:orders,id',
    //         // Only allow simple, non-unique core Order fields for mass update
    //         'orders.*.ord_date' => 'required|date',
    //         'orders.*.project_name' => 'nullable|string|max:255',
    //     ]);
        
    //     $ordersUpdatedCount = 0;

    //     DB::beginTransaction();
    //     try {
    //         foreach ($validated['orders'] as $orderData) {
    //             $orderId = $orderData['id'];
                
    //             unset($orderData['id']); // Remove the ID before passing to the update method
                
    //             $order = Order::find($orderId);
                
    //             if ($order) {
    //                 $order->update($orderData);
    //                 $ordersUpdatedCount++;
    //             }
    //         }
            
    //         DB::commit();
    //         return redirect()->route('orders.index')->with('success', $ordersUpdatedCount . ' Order(s) successfully updated.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error('Order mass update failed: ' . $e->getMessage());
    //         return back()->withInput()->with('error', 'Failed to mass update orders. Error: ' . $e->getMessage());
    //     }
    // }
// public function massUpdate(Request $request, Order $order)
// {
//     // Note: The logic for cleaning currency is usually done in the store method.
//     // We assume the front-end sends clean numbers or we'd need another cleaning step here.

//     $validated = $request->validate([
//         'orders' => 'required|array',
//         'orders.*.id' => 'required|exists:orders,id',
        
//         // Order Fields
//         'orders.*.ord_number' => 'required|string|max:255',
//         'orders.*.ord_date' => 'required|date',
//         'orders.*.project_name' => 'nullable|string|max:255',
//         'orders.*.client_id' => 'required|exists:clients,id', // NEW
//         'orders.*.department_id' => 'required|exists:departments,id', // NEW
//         'orders.*.amount' => 'required|numeric|min:0', // NEW: Total Revenue
//         'orders.*.cur' => 'required|string|max:10', // NEW

//         // Purchase Order Field (Need to ensure PO number remains unique)
//         // Note: A unique check in a mass update is highly complex and usually avoided. 
//         // We will remove the unique check here for simplicity, or it will fail on existing numbers.
//         'orders.*.po_number' => 'required|string|max:255', // NEW: Updates the related PurchaseOrder
//         'orders.*.po_date' => 'required|date', // NEW
//     ]);
    
//     $ordersUpdatedCount = 0;

//     DB::beginTransaction();
//     try {
//         foreach ($validated['orders'] as $orderData) {
//             $orderId = $orderData['id'];
//             $order = Order::with('purchaseOrder')->find($orderId);
            
//             if ($order) {
//                 // 1. Update Core Order fields
//                 $order->update([
//                     'ord_number' => $orderData['ord_number'],
//                     'ord_date' => $orderData['ord_date'],
//                     'project_name' => $orderData['project_name'],
//                     'client_id' => $orderData['client_id'],
//                     'department_id' => $orderData['department_id'],
//                     'amount' => $orderData['amount'],
//                     'cur' => $orderData['cur'],
//                 ]);
                
//                 // 2. Update Related Purchase Order
//                 if ($order->purchaseOrder) {
//                     $order->purchaseOrder->update([
//                         'po_number' => $orderData['po_number'],
//                         'po_date' => $orderData['po_date'],
//                     ]);
//                 }
                
//                 // 3. Update Outgoing/Incoming Invoices with new amount/currency
//                 // NOTE: This is critical to keep financial documents in sync.
//                 $order->outgoingInvoices()->update([
//                     'amount' => $orderData['amount'],
//                     'cur' => $orderData['cur'],
//                     'client_id' => $orderData['client_id'],
//                     'department_id' => $orderData['department_id'],
//                     'po_number' => $orderData['po_number'],
//                 ]);
                
//                 // NOTE: Incoming Invoice amount logic is complex and should NOT be edited here, 
//                 // as it's a calculated field based on profit %. We will skip updating II amount.
//                 $order->incomingInvoices()->update([
//                     'cur' => $orderData['cur'],
//                     'department_id' => $orderData['department_id'],
//                 ]);

//                 $ordersUpdatedCount++;
//             }
//         }
        
//         DB::commit();
//         return redirect()->route('orders.index')->with('success', $ordersUpdatedCount . ' Order(s) successfully updated.');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         \Log::error('Order mass update failed: ' . $e->getMessage());
//         return back()->withInput()->with('error', 'Failed to mass update orders. Error: ' . $e->getMessage());
//     }
// }

public function massUpdate(Request $request, Order $order)
{
    // Note: The $order parameter is a route model binding placeholder 
    // and is not used inside the loop, but it's kept for context/consistency.

    $validated = $request->validate([
        'orders' => 'required|array',
        'orders.*.id' => 'required|exists:orders,id',
        
        // Order Fields
        'orders.*.ord_number' => 'required|string|max:255', // Removed unique check logic as per your comment
        'orders.*.ord_date' => 'required|date',
        'orders.*.project_name' => 'nullable|string|max:255',
        'orders.*.client_id' => 'required|exists:clients,id',
        'orders.*.department_id' => 'required|exists:departments,id',
        'orders.*.amount' => 'required|numeric|min:0',
        'orders.*.cur' => 'required|string|max:10',

        // Purchase Order Field
        'orders.*.po_number' => 'required|string|max:255',
        'orders.*.po_date' => 'required|date',
    ]);
    
    $ordersUpdatedCount = 0;

    DB::beginTransaction();
    try {
        foreach ($validated['orders'] as $orderData) {
            $order = Order::with('purchaseOrder')->find($orderData['id']);
            
            if (!$order) {
                continue; // Skip if order not found
            }
            
            // --- 1. Update Core Order fields (Only changed fields) ---
            
            // Define all fields that belong to the Order model
            $orderFields = collect($orderData)->only([
                'ord_number', 'ord_date', 'project_name', 'client_id', 
                'department_id', 'amount', 'cur'
            ])->toArray();

            // Fill the model instance with new data and get the differences (dirty attributes)
            $order->fill($orderFields);
            $orderChanges = $order->getDirty();

            if (!empty($orderChanges)) {
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
                $poChanges = $order->purchaseOrder->getDirty(); // Capture PO changes
                
                if (!empty($poChanges)) {
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

            if (!empty($outgoingInvoiceChanges)) {
                $order->outgoingInvoices()->update($outgoingInvoiceChanges);
            }
            
            // --- 4. Update Incoming Invoices (Only based on confirmed changes) ---
            
            $incomingInvoiceChanges = [];
            
            // Propagate Order changes to Incoming Invoices (amount is skipped as per original note)
            $orderFieldsForII = ['cur', 'department_id'];
            foreach ($orderFieldsForII as $field) {
                if (isset($orderChanges[$field])) {
                    $incomingInvoiceChanges[$field] = $orderChanges[$field];
                }
            }

            if (!empty($incomingInvoiceChanges)) {
                $order->incomingInvoices()->update($incomingInvoiceChanges);
            }
        }
        
        DB::commit();
        return redirect()->route('orders.index')->with('success', $ordersUpdatedCount . ' Order(s) successfully updated (only changed data written).');
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Order mass update failed: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Failed to mass update orders. Error: ' . $e->getMessage());
    }
}

    // --- NEW: Implement Destroy for the Show/Index Page ---
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
            // Assuming a one-to-one relationship where the PO can be deleted via the Order's foreign key
            if ($order->purchaseOrder) {
                $order->purchaseOrder()->delete();
            }
            
            // 4. Delete the Order itself (The core record)
            $order->delete();
            
            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Order and all related documents successfully deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete order. Error: ' . $e->getMessage());
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
}
