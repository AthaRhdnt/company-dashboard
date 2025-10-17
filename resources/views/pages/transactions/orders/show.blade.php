<x-app-layout>
    @php
        // Define assumed variables for the view's context
        $clients = $clients ?? collect(); 
        $departments = $departments ?? collect(); 
        $vendors = $vendors ?? collect();
        $items = $items ?? collect(); // All Items for select dropdowns
        $currencies = $currencies ?? ['IDR', 'USD', 'SGD', 'EUR', 'JPY'];
        $profit = $profit ?? 0;
        $outgoingInvoice = $outgoingInvoice ?? null;
        $incomingInvoice = $incomingInvoice ?? null;
        
        // Use an empty collection if no line items exist for structural consistency
        $lineItems = optional($outgoingInvoice)->lineItems ?? collect();
        
        // Load all ItemSpecs grouped by item_id to pass to Alpine/JS/Partial
        $allItemSpecs = App\Models\ItemSpec::all()->groupBy('item_id'); 
        $allItemSpecsJson = $allItemSpecs->toJson();
    @endphp

    {{-- Alpine component for Edit Toggle State and New Items --}}
    <div x-data="{ 
        isEditing: {{ count($errors) > 0 ? 'true' : 'false' }},
        newLineItems: [], // Array to hold new line item objects
        allItemSpecs: JSON.parse('{{ $allItemSpecsJson }}'),
        
        addNewLineItem() {
            // Use a unique temporary key (like timestamp) for the new row index
            const newIndex = 'new_' + Date.now(); 
            // Add a new placeholder object to the array to trigger reactivity
            this.newLineItems.push({ 
                id: newIndex, 
                item_id: '',
                specs: [],
                quantity: 1,
                subtotal: 0.00,
                availableSpecs: [],
            }); 
            this.$nextTick(() => {
                document.getElementById('line-item-' + newIndex)?.scrollIntoView({ behavior: 'smooth', block: 'end' });
            });
        },
        
        updateAvailableSpecs(index, selectedItemId, isNew = false) {
            const specs = this.allItemSpecs[selectedItemId] || [];
            if (isNew) {
                const item = this.newLineItems.find(i => i.id === index);
                if (item) item.availableSpecs = specs;
            } else {
                // Not using Alpine for existing rows, this is mainly for the new rows
                console.log('Specs update triggered for existing item ' + index);
            }
        },
        
        removeLineItem(index, isNew = false) {
            if (isNew) {
                this.newLineItems = this.newLineItems.filter(item => item.id !== index);
            } else if (confirm('Are you sure you want to delete this existing line item?')) {
                const row = document.getElementById('line-item-' + index);
                if (row) {
                    // 1. Visually hide the row using Tailwind's 'hidden' class
                    row.classList.add('hidden');
                    
                    // 2. Add a hidden input to mark it for deletion upon form submission
                    let deleteInput = document.getElementById('delete-input-' + index);
                    if (!deleteInput) {
                        deleteInput = document.createElement('input');
                        deleteInput.type = 'hidden';
                        deleteInput.name = `line_items[${index}][delete]`;
                        deleteInput.value = '1';
                        deleteInput.id = 'delete-input-' + index; // <-- CRITICAL ID FOR ROLLBACK
                        row.prepend(deleteInput);
                    }
                }
            }
        },

        formatCurrencyCustom(value) {
            // Ensure the value is a number
            const num = parseFloat(value) || 0;
            
            // 1. Format the number to two decimal places (e.g., 1234.56)
            let parts = num.toFixed(2).split('.');
            
            // 2. Add thousands separator (dot '.') to the integer part
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            
            // 3. Join with the decimal comma (',')
            return parts.join(',');
        },
            
        // *** THE CORRECT ROLLBACK FUNCTION ***
        exitEditMode() {
            this.isEditing = false;
            this.newLineItems = []; // Discard new rows
            
            // Find all hidden rows (which were marked for deletion)
            document.querySelectorAll('#line-items-tbody tr.hidden').forEach(row => {
                row.classList.remove('hidden'); // Restore visibility
                
                // Remove the hidden delete input so it's not submitted
                const lineItemId = row.id.split('-').pop();
                const deleteInput = document.getElementById('delete-input-' + lineItemId);
                if (deleteInput) {
                    deleteInput.remove();
                }
            });
        }
    }" 
    class="p-6">
        <div class="bg-white p-6 rounded-lg shadow-md space-y-6">

            {{-- Order Update Form (Now wraps ALL editable content) --}}
            <form action="{{ route('orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Header and Toggle/Save Buttons (Unchanged) --}}
                {{-- <div class="flex justify-between items-start mb-6 border-b pb-4"> --}}
                <div 
                    class="sticky top-0 z-10 bg-white/95 rounded-lg backdrop-blur-sm p-4 -mt-4 flex justify-between items-start pb-4 transition-all duration-300 ease-in-out">
                    {{-- ... (Header content remains unchanged) ... --}}
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
                            Order Details: <span class="text-indigo-600">{{ $order->ord_number }}</span>
                        </h1>
                    </div>
                    
                    <div class="space-x-2 flex-shrink-0">
                        {{-- Toggle Button --}}
                        <button type="button" 
                            @click="isEditing ? exitEditMode() : (isEditing = true)"
                            x-bind:class="isEditing ? 'bg-red-500 hover:bg-red-600' : 'bg-indigo-600 hover:bg-indigo-700'"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                            <span x-text="isEditing ? 'Exit Edit Mode' : 'Toggle Edit Mode'"></span>
                        </button>
                        
                        {{-- Save Button (Only visible in Edit Mode) --}}
                        <button type="submit" x-show="isEditing"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Save Changes
                        </button>
                    </div>
                </div>
                
                {{-- Flash Messages (Unchanged) --}}
                @if (session('success'))
                    <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                        class="p-4 mb-4 bg-green-100 text-green-700 rounded-md border border-green-200 flex justify-between items-start">
                        <span>{{ session('success') }}</span>
                        <button type="button" @click="showMessage = false"
                            class="ml-4 -mt-1 p-1 rounded-full text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-600/50">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                        class="p-4 mb-4 bg-red-100 text-red-700 rounded-md border border-red-200 flex justify-between items-start">
                        <span>{{ session('error') }}</span>
                        <button type="button" @click="showMessage = false"
                            class="ml-4 -mt-1 p-1 rounded-full text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-600/50">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="p-4 mb-4 bg-red-100 text-red-700 rounded-md font-medium">
                        Please check the errors below to save your changes.
                    </div>
                @endif

                {{-- SECTION: Core Order Details & Relationships --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 border rounded-md shadow-sm">
                    <h2 class="col-span-full text-xl font-semibold mb-2 text-indigo-700">Core Transaction Details</h2>
                    
                    {{-- 1. MCN Order Number (Editable) --}}
                    <x-editable-field label="Order Number" is-editing="isEditing" type="text"
                        name="ord_number" :value="$order->ord_number"
                        display-value="{{ $order->ord_number }}" required />

                    {{-- 2. Client Name (Editable Select) --}}
                    <div class="space-y-1">
                        <x-input-label for="client_id" value="Client Name" />
                        <p x-show="!isEditing" class="text-sm text-gray-900 font-medium p-2">
                            {{ $order->client->client_name ?? 'N/A' }}
                        </p>
                        <select x-show="isEditing" id="client_id" name="client_id"
                            x-bind:class="{
                                'block w-full text-sm rounded-md shadow-sm p-2 transition duration-150 focus:ring focus:ring-opacity-50': true,
                                'bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400': isEditing,
                                'border-red-500 focus:border-red-500 focus:ring-red-200': {{ $errors->has('client_id') ? 'true' : 'false' }},
                            }" required>
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

                    {{-- 3. D-Code (Editable Select) --}}
                    <div class="space-y-1">
                        <x-input-label for="department_id" value="D-Code" />
                        <p x-show="!isEditing" class="text-sm text-gray-900 font-medium p-2">
                            {{ $order->department->department_code ?? 'N/A' }}
                        </p>
                        <select x-show="isEditing" id="department_id" name="department_id"
                            x-bind:class="{
                                'block w-full text-sm rounded-md shadow-sm p-2 transition duration-150 focus:ring focus:ring-opacity-50': true,
                                'bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400': isEditing,
                                'border-red-500 focus:border-red-500 focus:ring-red-200': {{ $errors->has('department_id') ? 'true' : 'false' }},
                            }" required>
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

                    {{-- 4. Order Date (Editable) --}}
                    @php $ordDateValue = $order->ord_date ? \Carbon\Carbon::parse($order->ord_date)->format('Y-m-d') : null; @endphp
                    <x-editable-field label="Order Date" is-editing="isEditing" type="date" 
                        name="ord_date" :value="$ordDateValue" 
                        display-value="{{ $order->ord_date ? \Carbon\Carbon::parse($order->ord_date)->format('d M Y') : 'N/A' }}" required />

                    {{-- 5. Project Name (Editable) --}}
                    <x-editable-field label="Project Name" is-editing="isEditing" type="text" 
                        name="project_name" :value="$order->project_name" 
                        display-value="{{ $order->project_name ?? 'N/A' }}" />
                    
                    {{-- 6. P.O. Number (Editable Text) --}}
                    @php $po_number = optional($order->purchaseOrder)->po_number; @endphp
                    <x-editable-field label="Customer PO Number" is-editing="isEditing" type="text"
                        name="po_number" :value="$po_number"
                        display-value="{{ $po_number ?? 'N/A' }}" required />

                    {{-- 7. P.O. Date (Editable Date) --}}
                    @php $poDateValue = optional($order->purchaseOrder)->po_date ? \Carbon\Carbon::parse($order->purchaseOrder->po_date)->format('Y-m-d') : null; @endphp
                    <x-editable-field label="PO Date" is-editing="isEditing" type="date"
                        name="po_date" :value="$poDateValue"
                        display-value="{{ $poDateValue ? \Carbon\Carbon::parse($poDateValue)->format('d M Y') : 'N/A' }}" required />
                    
                    {{-- 8. Total Revenue (Editable) --}}
                    <x-editable-field label="Total Revenue" is-editing="isEditing" type="text" 
                        name="amount" :value="$order->amount" 
                        display-value="{{ $order->cur . ' ' . number_format($order->amount, 2, ',', '.') }}" 
                        x-bind:class="{ 'bg-yellow-200': isEditing }" 
                        required />
                    
                    {{-- 9. Currency (Editable Select) --}}
                    <div class="space-y-1">
                        <x-input-label for="cur" value="Currency" />
                        <p x-show="!isEditing" class="text-sm text-gray-900 font-medium p-2">
                            {{ $order->cur ?? 'N/A' }}
                        </p>
                        <select x-show="isEditing" id="cur" name="cur"
                            x-bind:class="{
                                'block w-full text-sm rounded-md shadow-sm p-2 transition duration-150 focus:ring focus:ring-opacity-50': true,
                                'bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400': isEditing,
                                'border-red-500 focus:border-red-500 focus:ring-red-200': {{ $errors->has('cur') ? 'true' : 'false' }},
                            }" required>
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
                </div>


                {{-- SECTION: Outgoing Invoice Details (Editable) --}}
                @if ($outgoingInvoice)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 border rounded-md shadow-sm mt-6">
                        <h2 class="col-span-full text-xl font-semibold mb-2 text-indigo-700">Outgoing Invoice Details</h2>
                        
                        {{-- 10. Outgoing Inv. No (Editable) --}}
                        <x-editable-field label="Outgoing Inv. No." is-editing="isEditing" type="text" 
                            name="inv_number" :value="$outgoingInvoice->inv_number" 
                            display-value="{{ $outgoingInvoice->inv_number ?? 'PENDING' }}" />

                        {{-- 11. Inv Date (Editable) --}}
                        @php $invDateValue = optional($outgoingInvoice)->inv_date ? \Carbon\Carbon::parse($outgoingInvoice->inv_date)->format('Y-m-d') : null; @endphp
                        <x-editable-field label="Invoice Date" is-editing="isEditing" type="date" 
                            name="inv_date" :value="$invDateValue" 
                            display-value="{{ $invDateValue ? \Carbon\Carbon::parse($invDateValue)->format('d M Y') : 'PENDING' }}" />

                        {{-- 12. Due Date (Editable) --}}
                        @php $dueDateValue = optional($outgoingInvoice)->due_date ? \Carbon\Carbon::parse($outgoingInvoice->due_date)->format('Y-m-d') : null; @endphp
                        <x-editable-field label="Due Date" is-editing="isEditing" type="date" 
                            name="due_date" :value="$dueDateValue" 
                            display-value="{{ $dueDateValue ? \Carbon\Carbon::parse($dueDateValue)->format('d M Y') : 'N/A' }}" />
                        
                        {{-- 13. FP S/N (Editable) --}}
                        <x-editable-field label="FP S/N" is-editing="isEditing" type="text" 
                            name="fp_number" :value="$outgoingInvoice->fp_number" 
                            display-value="{{ $outgoingInvoice->fp_number ?? 'N/A' }}" />

                        {{-- 14. Revenue Rec. Date (Editable) --}}
                        @php $incomeDateValue = optional($outgoingInvoice)->income_date ? \Carbon\Carbon::parse($outgoingInvoice->income_date)->format('Y-m-d') : null; @endphp
                        <x-editable-field label="Revenue Rec. Date" is-editing="isEditing" type="date"
                            name="income_date" :value="$incomeDateValue"
                            display-value="{{ $incomeDateValue ? \Carbon\Carbon::parse($incomeDateValue)->format('d M Y') : 'PENDING' }}" />
                            
                        {{-- Outgoing Inv. Status (Static) --}}
                        <x-detail-field label="Outgoing Inv. Status" value="{{ optional($outgoingInvoice)->income_date ? 'COMPLETED' : 'PENDING' }}" />
                    </div>
                @else
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-md">
                        No Outgoing Invoice record found for this Order.
                    </div>
                @endif
                
                <hr class="my-6">


                {{-- SECTION: Incoming Invoice Details (Editable) --}}
                @if ($incomingInvoice)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 border rounded-md shadow-sm">
                        <h2 class="col-span-full text-xl font-semibold mb-2 text-indigo-700">Incoming Invoice Details</h2>
                        
                        {{-- 15. Vendor Name (Editable Select) --}}
                        <div class="space-y-1">
                            <x-input-label for="vendor_id" value="Vendor Name" />
                            <p x-show="!isEditing" class="text-sm text-gray-900 font-medium p-2">
                                {{ $incomingInvoice->vendor->vendor_name ?? 'N/A' }}
                            </p>
                            <select x-show="isEditing" id="vendor_id" name="vendor_id"
                                x-bind:class="{
                                    'block w-full text-sm rounded-md shadow-sm p-2 transition duration-150 focus:ring focus:ring-opacity-50': true,
                                    'bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400': isEditing,
                                    'border-red-500 focus:border-red-500 focus:ring-red-200': {{ $errors->has('vendor_id') ? 'true' : 'false' }},
                                }" required>
                                <option value="">Select Vendor</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" @selected(old('vendor_id', $incomingInvoice->vendor_id) == $vendor->id)>
                                        {{ $vendor->vendor_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 16. Vendor Profit Margin (%) (Editable) --}}
                        <x-editable-field label="Vendor Profit Margin (%)" is-editing="isEditing" type="number" 
                            name="profit_percentage" :value="optional($incomingInvoice)->profit_percentage ?? 0"
                            display-value="{{ (optional($incomingInvoice)->profit_percentage ?? 0) . '%' }}" 
                            step="0.01" />

                        {{-- 17. Incoming Inv. No (Editable) --}}
                        <x-editable-field label="Incoming Inv. No." is-editing="isEditing" type="text" 
                            name="incoming_inv_number" :value="$incomingInvoice->inv_number" 
                            display-value="{{ $incomingInvoice->inv_number ?? 'PENDING' }}" />

                        {{-- 18. Inv Received Date (Editable) --}}
                        @php $invReceivedDateValue = optional($incomingInvoice)->inv_received_date ? \Carbon\Carbon::parse($incomingInvoice->inv_received_date)->format('Y-m-d') : null; @endphp
                        <x-editable-field label="Invoice Received Date" is-editing="isEditing" type="date" 
                            name="inv_received_date" :value="$invReceivedDateValue" 
                            display-value="{{ $invReceivedDateValue ? \Carbon\Carbon::parse($invReceivedDateValue)->format('d M Y') : 'PENDING' }}" />

                        {{-- 19. Incoming FP Date (Editable) --}}
                        @php $incomingFpDateValue = optional($incomingInvoice)->fp_date ? \Carbon\Carbon::parse($incomingInvoice->fp_date)->format('Y-m-d') : null; @endphp
                        <x-editable-field label="Incoming FP Date" is-editing="isEditing" type="date" 
                            name="incoming_fp_date" :value="$incomingFpDateValue" 
                            display-value="{{ $incomingFpDateValue ? \Carbon\Carbon::parse($incomingFpDateValue)->format('d M Y') : 'N/A' }}" />

                        {{-- 20. Payment Date (Editable) --}}
                        @php $paymentDateValue = optional($incomingInvoice)->payment_date ? \Carbon\Carbon::parse($incomingInvoice->payment_date)->format('Y-m-d') : null; @endphp
                        <x-editable-field label="Payment Date" is-editing="isEditing" type="date"
                            name="payment_date" :value="$paymentDateValue"
                            display-value="{{ $paymentDateValue ? \Carbon\Carbon::parse($paymentDateValue)->format('d M Y') : 'N/A' }}" />

                        {{-- Incoming Inv. Status (Static) --}}
                        <x-detail-field label="Incoming Inv. Status" value="{{ optional($incomingInvoice)->payment_date ? 'PAID' : 'PENDING' }}" />
                    </div>
                @else
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-md">
                        No Incoming Invoice record found for this Order.
                    </div>
                @endif

                <hr class="my-6">
                
                {{-- LINE ITEMS SECTION (PERMANENT AND EDITABLE) --}}
                <h2 class="text-xl font-bold text-gray-800 mb-4">Outgoing Invoice Line Items (Editable)</h2>
                
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name / Specs</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price ({{ $order->cur }})</th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal ({{ $order->cur }})</th>
                                <th x-show="isEditing" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-10">Action</th>
                            </tr>
                        </thead>
                        
<tbody class="bg-white divide-y divide-gray-200" id="line-items-tbody">
    
    {{-- 1. EXISTING LINE ITEMS (LOOP) --}}
    @forelse ($lineItems as $itemDetail)
        @php
            $id = $itemDetail->id; // Use $id for cleaner access below
            $quantity = (float)old("line_items.{$id}.quantity", $itemDetail->quantity);
            $subtotal = (float)old("line_items.{$id}.subtotal", $itemDetail->subtotal);
            $unitPrice = $itemDetail->item->item_price;
            // Get current specs IDs for multi-select
            $currentSpecIds = $itemDetail->specs->pluck('id')->toArray();
        @endphp
        
        {{-- FIX: Add x-data for local state and update logic --}}
        <tr class="hover:bg-gray-50" id="line-item-{{ $id }}" 
            x-data="{ 
                qty: {{ $quantity }}, 
                unitPrice: {{ $unitPrice }},
                subTotal: {{ $subtotal }}, 
                updateSubtotal() {
                    // Recalculate subtotal (Quantity * Price)
                    this.subTotal = (this.qty * this.unitPrice); 
                }
            }">
            
            <td class="px-6 py-4 text-sm font-medium text-gray-900 space-y-2">
                {{-- Item Name (Select/Display) --}}
                <div class="space-y-1">
                    <x-input-label value="Item Name" class="!text-xs font-bold" />
                    <p x-show="!isEditing" class="text-sm text-gray-900 font-medium p-1">
                        {{ optional($itemDetail->item)->item_name ?? 'N/A' }}
                    </p>
                    
                    <select x-show="isEditing" 
                        name="line_items[{{ $id }}][item_id]"
                        class="block w-full text-sm rounded-md shadow-sm p-1 transition duration-150 bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400" required>
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

                {{-- Specs (Multi-select/Display) --}}
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
                        name="line_items[{{ $id }}][specs][]"
                        multiple
                        class="block w-full text-xs rounded-md shadow-sm p-1 transition duration-150 bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400 h-20">
                        
                        @php
                            $availableSpecs = $allItemSpecs->get(optional($itemDetail->item)->id, collect());
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
                <span class="font-semibold">{{ $order->cur . ' ' . number_format($unitPrice, 2, ',', '.') }}</span>
            </td>
            
            {{-- Editable Quantity (FIX: Added x-model & x-on:input) --}}
            <td class="px-6 py-2 text-sm text-gray-500 text-center">
                {{-- FIX: Use x-text for reactive display --}}
                <span x-show="!isEditing" x-text="qty">{{ $itemDetail->quantity }}</span>
                <input x-show="isEditing" 
                    type="number" 
                    name="line_items[{{ $id }}][quantity]" 
                    x-model.number="qty"
                    x-on:input.debounce.150ms="updateSubtotal()"
                    min="0"
                    step="1"
                    required
                    class="block w-20 text-sm rounded-md shadow-sm p-1 text-center bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400 mx-auto">
                @error('line_items.'.$id.'.quantity')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </td>

            {{-- Editable Subtotal (FIX: Added x-model and x-text) --}}
            <td class="px-6 py-2 text-sm text-right font-medium text-gray-700">
                {{-- FIX: Use x-text for reactive display, using parent's formatCurrencyCustom function --}}
                <span x-show="!isEditing" x-text="'{{ $order->cur }} ' + formatCurrencyCustom(subTotal)">{{ number_format($subtotal, 2, ',', '.') }}</span>
                <input x-show="isEditing" 
                    type="number" 
                    name="line_items[{{ $id }}][subtotal]" 
                    x-model.number="subTotal"
                    min="0"
                    step="0.01" {{-- Changed from 100000 to 0.01 for better generic currency input --}}
                    required
                    class="block w-32 ml-auto text-sm rounded-md shadow-sm p-1 text-right bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400">
                @error('line_items.'.$id.'.subtotal')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </td>
            
            {{-- Action (Delete Button) --}}
            <td x-show="isEditing" class="px-6 py-4 text-sm text-gray-500 text-center">
                <button type="button" @click="removeLineItem({{ $id }})" class="text-red-600 hover:text-red-900 text-xs">
                    Remove
                </button>
                {{-- Hidden input for deletion marking (will be added by JS) --}}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                No available Data. Please click "Add New Item Row" to start.
            </td>
        </tr>
    @endforelse
    
    {{-- 2. NEW LINE ITEMS (ALPINE LOOP) --}}
    <template x-for="(item, index) in newLineItems" :key="item.id">
        @include('partials.line_item_new_row_template', [
            'items' => $items,
            'currency' => $order->cur,
        ])
    </template>
    
</tbody>
                    </table>
                </div>

            </form>

            <hr class="my-6">
            
            {{-- FINANCIAL SUMMARY SECTION (Now permanently outside the form as it is read-only) --}}
            <div class="bg-gray-50 border p-4 rounded-lg">
                <h3 class="text-xl font-bold mb-4 text-green-700">Financial Summary</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    {{-- Revenue --}}
                    <x-detail-field label="Total Revenue (Outgoing)" 
                        :value="$order->cur . ' ' . number_format(optional($outgoingInvoice)->amount ?? 0, 2, ',', '.')" />
                    
                    {{-- Cost --}}
                    <x-detail-field label="Total Cost (Incoming)" 
                        :value="$order->cur . ' ' . number_format(optional($incomingInvoice)->amount ?? 0, 2, ',', '.')" />
                    
                    {{-- Profit --}}
                    <div class="p-2 border-b bg-green-100 border-green-400 rounded-md">
                        <dt class="text-sm font-extrabold text-green-800">Total Profit (Revenue - Cost)</dt>
                        <dd class="mt-1 text-lg text-green-900 font-extrabold">{{ $order->cur . ' ' . number_format($profit, 2, ',', '.') }}</dd>
                    </div>

                    {{-- Profit Percentage --}}
                    <x-detail-field label="Agreed Profit Percentage" 
                        value="{{ (optional($incomingInvoice)->profit_percentage ?? 0) . '%' }}" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>