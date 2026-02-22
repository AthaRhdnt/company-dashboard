<x-app-layout>
    {{-- ---------------------------------------- --}}
    {{-- PHP SETUP & VARIABLE FALLBACKS (Moved Inline/Simplified) --}}
    {{-- ---------------------------------------- --}}
    @php
        // Define assumed variables for the view's context
$clients = $clients ?? collect();
$departments = $departments ?? collect();
$vendors = $vendors ?? collect();
$items = $items ?? collect(); // All Items for select dropdowns
$currencies = $currencies ?? ['IDR', 'USD', 'SGD', 'EUR', 'JPY'];
$allItemSpecsJson = $allItemSpecsJson ?? '[]'; // Use controller-passed JSON
$profit = $profit ?? 0;
$outgoingInvoice = $outgoingInvoice ?? null;
$incomingInvoice = $incomingInvoice ?? null;
$lineItems = optional($outgoingInvoice)->lineItems ?? collect();

// Date Formatting Helper (for display and input values)
$formatDate = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('Y-m-d') : null;
$displayDate = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('d M Y') : 'N/A';

// Class for editable selects (to reduce inline code)
$selectClasses =
    'block w-full text-sm rounded-md shadow-sm p-2 transition duration-150 focus:ring focus:ring-opacity-50';
$selectEditableClasses =
    $selectClasses . ' bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400';
$selectErrorClasses = ' border-red-500 focus:border-red-500 focus:ring-red-200';
    @endphp

    {{-- ---------------------------------------- --}}
    {{-- ALPINE.JS GLOBAL STATE & METHODS --}}
    {{-- ---------------------------------------- --}}
    <div x-data="{
        isEditing: {{ count($errors) > 0 ? 'true' : 'false' }},
        newLineItems: [], // Array to hold new line item objects
        allItemSpecs: JSON.parse(@js($allItemSpecsJson)),
    
        // Add a new line item to the 'new' array
        addNewLineItem() {
            const newIndex = 'new_' + Date.now();
            this.newLineItems.push({
                id: newIndex,
                item_id: '',
                specs: [],
                quantity: 1,
                subtotal: 0.00,
                availableSpecs: [],
                // Default item ID, assuming first item is general or empty
                defaultItem: '{{ $items->first()->id ?? '' }}'
            });
            this.$nextTick(() => {
                document.getElementById('line-item-' + newIndex)?.scrollIntoView({ behavior: 'smooth', block: 'end' });
            });
        },
    
        // Remove a line item (existing marks for deletion, new is just removed from array)
        removeLineItem(index, isNew = false) {
            if (isNew) {
                this.newLineItems = this.newLineItems.filter(item => item.id !== index);
            } else if (confirm('Are you sure you want to delete this existing line item?')) {
                const row = document.getElementById('line-item-' + index);
                if (row) {
                    row.classList.add('hidden'); // 1. Visually hide the row
                    let deleteInput = document.getElementById('delete-input-' + index);
                    if (!deleteInput) {
                        // 2. Add a hidden input to mark it for deletion
                        deleteInput = document.createElement('input');
                        deleteInput.type = 'hidden';
                        deleteInput.name = `line_items[${index}][delete]`;
                        deleteInput.value = '1';
                        deleteInput.id = 'delete-input-' + index;
                        row.prepend(deleteInput);
                    }
                }
            }
        },
    
        // Format number to currency style (1234.56 -> 1.234,56)
        formatCurrencyCustom(value) {
            const num = parseFloat(value) || 0;
            let parts = num.toFixed(2).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return parts.join(',');
        },
    
        // Rollback function to exit edit mode and discard changes
        exitEditMode() {
            this.isEditing = false;
            this.newLineItems = []; // Discard new rows
    
            // Restore hidden (marked for deletion) existing rows
            document.querySelectorAll('#line-items-tbody tr.hidden').forEach(row => {
                row.classList.remove('hidden');
                const lineItemId = row.id.split('-').pop();
                const deleteInput = document.getElementById('delete-input-' + lineItemId);
                if (deleteInput) {
                    deleteInput.remove();
                }
            });
        }
    }" class="p-6">
        <div class="bg-white p-6 rounded-lg shadow-md space-y-6">

            {{-- ---------------------------------------- --}}
            {{-- ORDER UPDATE FORM --}}
            {{-- ---------------------------------------- --}}
            <form action="{{ route('orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- HEADER: Navigation and Toggle/Save Buttons --}}
                <div
                    class="sticky top-0 z-10 bg-white/90 backdrop-blur-md p-4 -mt-4 transition-all duration-300 ease-in-out flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('orders.index') }}"
                            class="inline-flex items-center p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition duration-150 ease-in-out"
                            title="Back to Orders List">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-3xl font-bold text-gray-800">
                            Order: <span class="text-indigo-600">{{ $order->ord_number }}</span>
                        </h1>
                    </div>

                    <div class="space-x-2 flex-shrink-0">
                        {{-- Save Button (Only visible in Edit Mode) --}}
                        <button type="submit" x-show="isEditing"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Save Changes
                        </button>

                        {{-- Toggle Button --}}
                        <button type="button" @click="isEditing = !isEditing"
                            x-bind:class="isEditing ? 'bg-red-500 hover:bg-red-600 focus:ring-red-500' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <span x-text="isEditing ? 'Exit ' : 'Edit Mode'"></span>
                        </button>
                    </div>
                </div>

                {{-- FLASH MESSAGES & ERRORS --}}
                @if (session('success'))
                    <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                        class="p-4 mb-4 bg-green-100 text-green-700 rounded-md border border-green-200 flex justify-between items-start">
                        <span>{{ session('success') }}</span>
                        <button type="button" @click="showMessage = false"
                            class="ml-4 -mt-1 p-1 rounded-full text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-600/50">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div x-data="{ show: true }" x-show="show" class="p-4 mb-4 bg-red-100 text-red-700 rounded-md border border-red-200">
                        <div class="flex justify-between">
                            <strong class="font-bold">Please fix the following errors:</strong>
                            <button type="button" @click="show = false" class="text-red-700 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <ul class="mt-2 list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ---------------------------------------- --}}
                {{-- SECTION: CORE ORDER DETAILS --}}
                {{-- ---------------------------------------- --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-md shadow-sm">
                    <h2 class="col-span-full text-xl font-semibold pb-2 border-b text-indigo-700">Order Details</h2>

                    {{-- 1. Order Number --}}
                    <x-editable-field label="Order Number" is-editing="isEditing" type="text" name="ord_number"
                        :value="$order->ord_number" display-value="{{ $order->ord_number }}" required />

                    {{-- 2. Client Name (Select) --}}
                    <div class="space-y-1">
                        <x-input-label for="client_id" value="Customer Name" />
                        <p x-show="!isEditing" class="text-sm text-gray-900 font-medium pt-2">
                            {{ $order->client->client_name ?? 'N/A' }}
                        </p>
                        <select x-show="isEditing" id="client_id" name="client_id"
                            x-bind:class="{
                                '{{ $selectEditableClasses }}': isEditing,
                                '{{ $selectErrorClasses }}': {{ $errors->has('client_id') ? 'true' : 'false' }},
                            }"
                            required>
                            <option value="">Select Client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id', $order->client_id) == $client->id)>
                                    {{ $client->client_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. D-Code (Select) --}}
                    <div class="space-y-1">
                        <x-input-label for="department_id" value="D-Code" />
                        <p x-show="!isEditing" class="text-sm text-gray-900 font-medium py-2">
                            {{ $order->department->department_code ?? 'N/A' }}
                        </p>
                        <select x-show="isEditing" id="department_id" name="department_id"
                            x-bind:class="{
                                '{{ $selectEditableClasses }}': isEditing,
                                '{{ $selectErrorClasses }}': {{ $errors->has('department_id') ? 'true' : 'false' }},
                            }"
                            required>
                            <option value="">Select Dept.</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id', $order->department_id) == $department->id)>
                                    {{ $department->department_code }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 4. Order Date --}}
                    <x-editable-field label="Order Date" is-editing="isEditing" type="date" name="ord_date"
                        :value="$formatDate($order->ord_date)" display-value="{{ $displayDate($order->ord_date) }}" required />

                    {{-- 5. Project Name --}}
                    <x-editable-field label="Project Name" is-editing="isEditing" type="text" name="project_name"
                        :value="$order->project_name" display-value="{{ $order->project_name ?? 'N/A' }}" />

                    {{-- 6. Customer P.O. Number --}}
                    @php $po_number = optional($order->purchaseOrder)->po_number; @endphp
                    <x-editable-field label="Customer PO Number" is-editing="isEditing" type="text" name="po_number"
                        :value="$po_number" display-value="{{ $po_number ?? 'N/A' }}" required />

                    {{-- 7. Customer P.O. Date --}}
                    @php $poDate = optional($order->purchaseOrder)->po_date; @endphp
                    <x-editable-field label="Customer PO Date" is-editing="isEditing" type="date" name="po_date"
                        :value="$formatDate($poDate)" display-value="{{ $displayDate($poDate) }}" required />

                    {{-- 8. Order Amount --}}
                    <x-editable-field label="Order Amount" is-editing="isEditing" type="text" name="amount"
                        :value="$order->amount"
                        display-value="{{ $order->cur . ' ' . number_format($order->amount, 2, ',', '.') }}"
                        x-bind:class="{ 'bg-yellow-200': isEditing }" required />

                    {{-- 9. Currency (Select) --}}
                    <div class="space-y-1">
                        <x-input-label for="cur" value="Currency" />
                        <p x-show="!isEditing" class="text-sm text-gray-900 font-medium py-2">
                            {{ $order->cur ?? 'N/A' }}
                        </p>
                        <select x-show="isEditing" id="cur" name="cur"
                            x-bind:class="{
                                '{{ $selectEditableClasses }}': isEditing,
                                '{{ $selectErrorClasses }}': {{ $errors->has('cur') ? 'true' : 'false' }},
                            }"
                            required>
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency }}" @selected(old('cur', $order->cur) == $currency)>
                                    {{ $currency }}
                                </option>
                            @endforeach
                        </select>
                        @error('cur')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- 10. Remark(s) --}}
                    <x-editable-field label="Remark(s)" is-editing="isEditing" type="text" name="remark"
                        class="w-full" :value="$order->remark" display-value="{{ $order->remark ?? 'N/A' }}" />
                    
                    {{-- 11. Status --}}
                    <x-detail-field label="Order Status"
                        value="{{ $order->transaction_status }}" />
                </div>

                <hr class="my-6">

                {{-- ---------------------------------------- --}}
                {{-- SECTION: OUTGOING INVOICE DETAILS --}}
                {{-- ---------------------------------------- --}}
                @if ($outgoingInvoice)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-md shadow-sm mt-6">
                        <h2 class="col-span-full text-xl font-semibold pb-2 border-b text-indigo-700">Outgoing Invoice
                            <a href="{{ route('incoming-invoices.show', $outgoingInvoice->order->id) }}">Details</a></h2>

                        {{-- 10. Invoice Number --}}
                        <x-editable-field label="Invoice Number" is-editing="isEditing" type="text"
                            name="inv_number" :value="$outgoingInvoice->inv_number"
                            display-value="{{ $outgoingInvoice->inv_number ?? 'N/A' }}" />

                        {{-- 11. Invoice Date --}}
                        <x-editable-field label="Invoice Date" is-editing="isEditing" type="date" name="inv_date"
                            :value="$formatDate(optional($outgoingInvoice)->inv_date)"
                            display-value="{{ $displayDate(optional($outgoingInvoice)->inv_date) }}" />

                        {{-- 12. Due Date --}}
                        <x-editable-field label="Due Date" is-editing="isEditing" type="date" name="due_date"
                            :value="$formatDate(optional($outgoingInvoice)->due_date)"
                            display-value="{{ $displayDate(optional($outgoingInvoice)->due_date) }}" />

                        {{-- 13. FP S/N --}}
                        <x-editable-field label="FP S/N" is-editing="isEditing" type="text" name="fp_number"
                            :value="$outgoingInvoice->fp_number" display-value="{{ $outgoingInvoice->fp_number ?? 'N/A' }}" />

                        {{-- 14. Incoming Payment Date --}}
                        <x-editable-field label="Incoming Payment Date" is-editing="isEditing" type="date"
                            name="income_date" :value="$formatDate(optional($outgoingInvoice)->income_date)"
                            display-value="{{ $displayDate(optional($outgoingInvoice)->income_date) }}" />

                        {{-- Invoice Status (Static) --}}
                        <x-detail-field label="Invoice Status"
                            value="{{ optional($outgoingInvoice)->income_date ? 'PAID' : 'UNPAID' }}" />
                    </div>
                @else
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-md">
                        No Outgoing Invoice record found for this Order.
                    </div>
                @endif

                <hr class="my-6">

                {{-- ---------------------------------------- --}}
                {{-- SECTION: INCOMING INVOICE DETAILS --}}
                {{-- ---------------------------------------- --}}
                @php
                    $incomingInvoices = $order->incomingInvoices;
                @endphp

                @if ($incomingInvoices->isNotEmpty())
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-indigo-700">Incoming Invoice Details</h2>
                        <a href="{{ route('incoming-invoices.create', ['for_order' => $order->id]) }}"
                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-rose-600 text-white text-xs font-semibold uppercase tracking-wider shadow hover:bg-rose-700">
                            {{-- Plus Icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Record
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-md shadow-sm mb-6">
                        @foreach ($incomingInvoices as $index => $invoice)
                            @if ($loop->index > 0)
                                <hr class="col-span-full my-4 border-gray-300">
                            @endif

                            <input type="hidden" name="incoming_invoices[{{ $index }}][id]"
                                value="{{ $invoice->id }}">

                            {{-- 15. Vendor Name --}}
                            <div class="space-y-1">
                                <x-input-label for="vendor_id_{{ $index }}" value="Vendor Name" />
                                <p x-show="!isEditing" class="text-sm text-gray-900 font-medium py-2">
                                    {{ $invoice->vendor->vendor_name ?? 'N/A' }}
                                </p>
                                <select x-show="isEditing" id="vendor_id_{{ $index }}"
                                    name="incoming_invoices[{{ $index }}][vendor_id]"
                                    x-bind:class="{
                                        '{{ $selectEditableClasses }}': isEditing,
                                        '{{ $selectErrorClasses }}': {{ $errors->has("incoming_invoices.{$index}.vendor_id") ? 'true' : 'false' }},
                                    }"
                                    required>
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected(old("incoming_invoices.{$index}.vendor_id", $invoice->vendor_id) == $vendor->id)>
                                            {{ $vendor->vendor_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("incoming_invoices.{$index}.vendor_id")
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Invoice Amount --}}
                            <x-editable-field label="Invoice Amount" is-editing="isEditing" type="text"
                                name="incoming_invoices[{{ $index }}][amount]" :value="$invoice->amount"
                                display-value="{{ $order->cur . ' ' . number_format($invoice->amount ?? 0, 2, ',', '.') }}"
                                x-bind:class="{ 'bg-yellow-200': isEditing }" required />

                            {{-- Invoice Number --}}
                            <x-editable-field label="Invoice Number" is-editing="isEditing" type="text"
                                name="incoming_invoices[{{ $index }}][inv_number]" :value="$invoice->inv_number"
                                display-value="{{ $invoice->inv_number ?? 'N/A' }}" />

                            {{-- Received Date --}}
                            <x-editable-field label="Received Date" is-editing="isEditing" type="date"
                                name="incoming_invoices[{{ $index }}][inv_received_date]" :value="$formatDate($invoice->inv_received_date)"
                                display-value="{{ $displayDate($invoice->inv_received_date) }}" />

                            {{-- Invoice/FP Date --}}
                            <x-editable-field label="Invoice/FP Date" is-editing="isEditing" type="date"
                                name="incoming_invoices[{{ $index }}][fp_date]" :value="$formatDate($invoice->fp_date)"
                                display-value="{{ $displayDate($invoice->fp_date) }}" />

                            {{-- Payment Date --}}
                            <x-editable-field label="Payment Date" is-editing="isEditing" type="date"
                                name="incoming_invoices[{{ $index }}][payment_date]" :value="$formatDate($invoice->payment_date)"
                                display-value="{{ $displayDate($invoice->payment_date) }}" />

                            {{-- Invoice Status --}}
                            <x-detail-field label="Invoice Status"
                                value="{{ $invoice->payment_date ? 'PAID' : 'UNPAID' }}" />

                            {{-- Learn More Button --}}
                            <a href="{{ route('incoming-invoices.show', $invoice->order->id) }}">Learn More</a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-md">
                        <p class="font-semibold">No Incoming Invoices Found</p>
                        <p class="text-sm text-red-700 mt-1">
                            There are currently no incoming invoices recorded for this order.
                        </p>
                        <a href="{{ route('incoming-invoices.create', ['for_order' => $order->id]) }}"
                            class="inline-flex items-center mt-4 px-4 py-2 rounded-md bg-rose-600 text-white text-xs font-semibold uppercase tracking-wider shadow hover:bg-rose-700">
                            Record Invoice
                        </a>
                    </div>
                @endif

                <hr class="my-6">

                {{-- ---------------------------------------- --}}
                {{-- SECTION: LINE ITEMS --}}
                {{-- ---------------------------------------- --}}
                <div class="grid grid-cols-1 gap-4 p-4 border rounded-md shadow-sm">
                    <h2 class="col-span-full text-xl font-semibold pb-2 border-b text-indigo-700">Ordered Items</h2>

                    {{-- Add Item Button --}}
                    <div x-show="isEditing" class="mb-4">
                        <button type="button" @click="addNewLineItem()"
                            class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 transition ease-in-out duration-150">
                            + Add New Item Row
                        </button>
                    </div>

                    <div class="overflow-x-auto shadow-lg rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Item Name / Specs</th>
                                    <th
                                        class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit Price ({{ $order->cur }})</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity</th>
                                    <th
                                        class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subtotal ({{ $order->cur }})</th>
                                    <th x-show="isEditing"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                        Action</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200" id="line-items-tbody">

                                {{-- EXISTING LINE ITEMS --}}
                                @forelse ($lineItems as $itemDetail)
                                    @php
                                        $id = $itemDetail->id;
                                        $quantity = (float) old("line_items.{$id}.quantity", $itemDetail->quantity);
                                        $subtotal = (float) old("line_items.{$id}.subtotal", $itemDetail->subtotal);
                                        $unitPrice = $itemDetail->item->item_price ?? 0; // Added fallback 0
                                        $currentSpecIds = $itemDetail->specs->pluck('id')->toArray();
                                    @endphp

                                    <tr class="hover:bg-gray-50" id="line-item-{{ $id }}"
                                        x-data="{
                                            qty: {{ $quantity }},
                                            unitPrice: {{ $unitPrice }},
                                            subTotal: {{ $subtotal }},
                                            updateSubtotal() {
                                                this.subTotal = (this.qty * this.unitPrice);
                                            }
                                        }">

                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 space-y-2">
                                            {{-- Item Name --}}
                                            <div class="space-y-1">
                                                <x-input-label value="Item Name" class="!text-xs font-bold" />
                                                <p x-show="!isEditing" class="text-sm text-gray-900 font-medium p-1">
                                                    {{ optional($itemDetail->item)->item_name ?? 'N/A' }}
                                                </p>
                                                <select x-show="isEditing"
                                                    name="line_items[{{ $id }}][item_id]"
                                                    class="block w-full text-sm rounded-md shadow-sm p-1 transition duration-150 bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400"
                                                    required>
                                                    <option value="">Select Item</option>
                                                    @foreach ($items as $item)
                                                        <option value="{{ $item->id }}"
                                                            @selected(old("line_items.{$id}.item_id", optional($itemDetail->item)->id) == $item->id)>
                                                            {{ $item->item_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("line_items.{$id}.item_id")
                                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- Specs --}}
                                            <div class="mt-2 space-y-1">
                                                <x-input-label value="Specs/Description" class="!text-xs font-bold" />
                                                <p x-show="!isEditing" class="text-xs text-gray-500 p-1">
                                                    @forelse ($itemDetail->specs as $spec)
                                                        <span class="block">- {{ $spec->item_description }}</span>
                                                    @empty
                                                        <span class="text-xs text-gray-400">No Specs</span>
                                                    @endforelse
                                                </p>
                                                <select x-show="isEditing"
                                                    name="line_items[{{ $id }}][specs][]" multiple
                                                    class="block w-full text-xs rounded-md shadow-sm p-1 transition duration-150 bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400 h-20">
                                                    @php
                                                        // Ensure allItemSpecs is a collection for consistency
                                                        $availableSpecs = collect(
                                                            $allItemSpecs->get(optional($itemDetail->item)->id, []),
                                                        );
                                                    @endphp
                                                    @foreach ($availableSpecs as $spec)
                                                        <option value="{{ $spec->id }}"
                                                            @selected(in_array($spec->id, old("line_items.{$id}.specs", $currentSpecIds)))>
                                                            {{ $spec->item_description }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("line_items.{$id}.specs")
                                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </td>

                                        {{-- Unit Price (Display Only) --}}
                                        <td class="px-6 py-2 text-sm text-right text-gray-700">
                                            <span
                                                class="font-semibold">{{ number_format($unitPrice, 2, ',', '.') }}</span>
                                        </td>

                                        {{-- Editable Quantity --}}
                                        <td class="px-6 py-2 text-sm text-gray-500 text-center">
                                            <span x-show="!isEditing" x-text="qty"></span>
                                            <input x-show="isEditing" type="number"
                                                name="line_items[{{ $id }}][quantity]" x-model.number="qty"
                                                x-on:input.debounce.150ms="updateSubtotal()" min="0"
                                                step="1" required
                                                class="block w-20 text-sm rounded-md shadow-sm p-1 text-center bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400 mx-auto">
                                            @error('line_items.' . $id . '.quantity')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        {{-- Editable Subtotal --}}
                                        <td class="px-6 py-2 text-sm text-right font-medium text-gray-700">
                                            <span x-show="!isEditing" class="font-bold"
                                                x-text="formatCurrencyCustom(subTotal)"></span>
                                            <input x-show="isEditing" type="number"
                                                name="line_items[{{ $id }}][subtotal]"
                                                x-model.number="subTotal" min="0" step="1" required
                                                class="block w-32 ml-auto text-sm rounded-md shadow-sm p-1 text-right bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400">
                                            @error('line_items.' . $id . '.subtotal')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        {{-- Action (Delete Button) --}}
                                        <td x-show="isEditing" class="px-6 py-4 text-sm text-gray-500 text-center">
                                            <button type="button" @click="removeLineItem({{ $id }})"
                                                class="text-red-600 hover:text-red-900 text-xs">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No available Data. Please click "Add New Item Row" to start.
                                        </td>
                                    </tr>
                                @endforelse

                                {{-- NEW LINE ITEMS (ALPINE TEMPLATE, REPLACES @include) --}}
                                <template x-for="(item, index) in newLineItems" :key="item.id">
                                    <tr class="hover:bg-yellow-50/50 transition duration-100"
                                        :id="'line-item-' + item.id" x-data="{
                                            // Set initial values from Alpine's main component
                                            itemIndex: item.id,
                                            item_id: item.defaultItem,
                                            unitPrice: 0.00,
                                            qty: item.quantity,
                                            subTotal: item.subtotal,
                                            allSpecs: @js($allItemSpecs),
                                        
                                            // Fetches unit price based on selected item_id
                                            fetchUnitPrice() {
                                                const selectedItem = @js($items->keyBy('id'));
                                                const itemData = selectedItem[this.item_id];
                                                this.unitPrice = itemData ? itemData.item_price : 0.00;
                                                this.updateSubtotal();
                                            },
                                        
                                            // Calculates subtotal
                                            updateSubtotal() {
                                                this.subTotal = parseFloat(this.qty) * parseFloat(this.unitPrice);
                                            },
                                        
                                            // Retrieves available specs for the selected item
                                            getSpecs() {
                                                return this.allSpecs[this.item_id] || [];
                                            }
                                        }" x-init="fetchUnitPrice">

                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 space-y-2">
                                            {{-- Item Name --}}
                                            <div class="space-y-1">
                                                <x-input-label value="Item Name" class="!text-xs font-bold" />
                                                <select :name="'line_items[' + item.id + '][item_id]'"
                                                    x-model="item.item_id"
                                                    x-on:change="item_id = $el.value; fetchUnitPrice()"
                                                    class="block w-full text-sm rounded-md shadow-sm p-1 transition duration-150 bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400"
                                                    required>
                                                    <option value="">Select Item</option>
                                                    @foreach ($items as $itemSelect)
                                                        <option value="{{ $itemSelect->id }}">
                                                            {{ $itemSelect->item_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                {{-- Error handling for new items needs to be done on the server side after submission --}}
                                            </div>

                                            {{-- Specs --}}
                                            <div class="mt-2 space-y-1">
                                                <x-input-label value="Specs/Description" class="!text-xs font-bold" />
                                                <select :name="'line_items[' + item.id + '][specs][]'" multiple
                                                    class="block w-full text-xs rounded-md shadow-sm p-1 transition duration-150 bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400 h-20">
                                                    <template x-for="spec in getSpecs()" :key="spec.id">
                                                        <option :value="spec.id" x-text="spec.item_description">
                                                        </option>
                                                    </template>
                                                </select>
                                            </div>
                                        </td>

                                        {{-- Unit Price (Read Only from Alpine) --}}
                                        <td class="px-6 py-2 text-sm text-right text-gray-700">
                                            <span class="font-semibold"
                                                x-text="'{{ $order->cur }} ' + formatCurrencyCustom(unitPrice)"></span>
                                        </td>

                                        {{-- Editable Quantity --}}
                                        <td class="px-6 py-2 text-sm text-gray-500 text-center">
                                            <input type="number" :name="'line_items[' + item.id + '][quantity]'"
                                                x-model.number="qty" x-on:input.debounce.150ms="updateSubtotal()"
                                                min="1" step="1" required
                                                class="block w-20 text-sm rounded-md shadow-sm p-1 text-center bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400 mx-auto">
                                        </td>

                                        {{-- Editable Subtotal (Calculated) --}}
                                        <td class="px-6 py-2 text-sm text-right font-medium text-gray-700">
                                            <span x-text="'{{ $order->cur }} ' + formatCurrencyCustom(subTotal)"
                                                class="block"></span>
                                            <input type="hidden" :name="'line_items[' + item.id + '][subtotal]'"
                                                :value="subTotal">
                                        </td>

                                        {{-- Action (Delete Button) --}}
                                        <td class="px-6 py-4 text-sm text-gray-500 text-center">
                                            <button type="button" @click="removeLineItem(item.id, true)"
                                                class="text-red-600 hover:text-red-900 text-xs">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            <hr class="my-6">

            {{-- ---------------------------------------- --}}
            {{-- FINANCIAL SUMMARY SECTION (Read-Only) --}}
            {{-- ---------------------------------------- --}}
            <div class="bg-gray-50 border p-4 rounded-lg">
                <h3 class="text-xl font-bold mb-4 text-green-700">Financial Summary</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Revenue --}}
                    <x-detail-field label="Order Amount" :value="'Rp ' . number_format(optional($outgoingInvoice)->amount ?? 0, 2, ',', '.')" />

                    {{-- Cost --}}
                    <x-detail-field label="Order Cost" :value="'Rp ' . number_format($cost ?? 0, 2, ',', '.')" />

                    {{-- Profit --}}
                    <div class="p-2 border-b bg-green-100 border-green-400 rounded-md">
                        <dt class="text-sm font-extrabold text-green-800">Profit</dt>
                        <dd class="mt-1 text-lg text-green-900 font-extrabold">
                            {{ 'Rp ' . number_format($profit, 2, ',', '.') }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
