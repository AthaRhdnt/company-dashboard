<x-app-layout>
    {{-- Alpine Component to manage all state and logic --}}
    <div x-data="orderForm({{ json_encode($items) }}, {{ json_encode($taxes) }})" x-init="init()">

        <x-pages.form resource="orders" action="store" :item="null">

            <div class="space-y-6 max-w-4xl mx-auto">

                {{-- SECTION 1: CORE ORDER & PO DETAILS --}}
                <div class="p-6 bg-white shadow-xl rounded-lg border-t-4 border-indigo-500">
                    <h2 class="text-2xl font-bold mb-4 text-indigo-800 border-b pb-2">Core Order Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Client Input --}}
                        <div>
                            <x-input-label for="client_id" :value="__('Client')" />
                            {{-- Consider making the SELECT a reusable component (x-form.select) --}}
                            <select id="client_id" name="client_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Other Core Inputs (No change needed) --}}
                        <div>
                            <x-input-label for="order_no" :value="__('MCN Order Number')" />
                            <x-text-input id="order_no" name="order_no" type="text" class="mt-1 block w-full"
                                :value="old('order_no')" required />
                        </div>
                        <div>
                            <x-input-label for="ord_date" :value="__('Order Date')" />
                            <x-text-input id="ord_date" name="ord_date" type="date" class="mt-1 block w-full"
                                :value="old('ord_date', now()->format('Y-m-d'))" required />
                        </div>
                        <div class="md:col-span-3">
                            <x-input-label for="project_name" :value="__('Project Name')" />
                            <x-text-input id="project_name" name="project_name" type="text" class="mt-1 block w-full"
                                :value="old('project_name')" />
                        </div>

                        {{-- PO Details --}}
                        <div>
                            <x-input-label for="po_number" :value="__('Customer PO Number')" />
                            <x-text-input id="po_number" name="po_number" type="text" class="mt-1 block w-full"
                                :value="old('po_number')" required />
                        </div>
                        <div>
                            <x-input-label for="po_date" :value="__('PO Date')" />
                            <x-text-input id="po_date" name="po_date" type="date" class="mt-1 block w-full"
                                :value="old('po_date')" required />
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: FINANCIALS & COST CALCULATION (INPUTS BEFORE TAXES) --}}
                <div class="p-6 bg-yellow-50 shadow-xl rounded-lg border-t-4 border-yellow-500">
                    <h2 class="text-2xl font-bold mb-4 text-yellow-800 border-b pb-2">Financials & Cost Calculation</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                        {{-- Revenue Input (Now uses @input to update Alpine state) --}}
                        <div>
                            <x-input-label for="total_customer_payment" :value="__('Payment (Revenue)')" />
                            <x-text-input id="total_customer_payment" name="total_customer_payment" type="text"
                                step="0.01" class="mt-1 block w-full bg-green-100 font-semibold"
                                :value="old('total_customer_payment')" required
                                {{-- Alpine logic replaces old JS event listeners --}}
                                @input="totalPayment = parseForCalculation($event.target.value)"
                                @blur="formatTotalPayment($event)"
                            />
                        </div>

                        {{-- Agreement Fee Input (Now uses x-model) --}}
                        <div>
                            <x-input-label for="agreement_percentage" :value="__('Agreement Fee (%)')" />
                            <x-text-input id="agreement_percentage" name="agreement_percentage" type="number"
                                step="0.01" class="mt-1 block w-full" :value="old('agreement_percentage', 4.0)" required
                                x-model.number="agreementPercentage"
                            />
                        </div>

                        {{-- Currency, Vendor, and Department Inputs (No change needed) --}}
                        <div>
                            <x-input-label for="cur" :value="__('Currency')" />
                            <x-text-input id="cur" name="cur" type="text" class="mt-1 block w-full"
                                :value="old('cur', 'IDR')" required />
                        </div>

                        <div>
                            <x-input-label for="vendor_id" :value="__('Vendor (Cost Side)')" />
                            <select id="vendor_id" name="vendor_id" class="form-select mt-1 block w-full">
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <x-input-label for="department_id" :value="__('Department (D-Code)')" />
                            <select id="department_id" name="department_id" class="form-select mt-1 block w-full">
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: TAX SELECTION AND VENDOR AMOUNT DISPLAY --}}
                <div class="p-6 bg-yellow-100 shadow-xl rounded-lg border-t-4 border-red-500">
                    <h3 class="text-xl font-semibold mb-4 text-red-800 border-b pb-2">Taxes & Final Vendor Amount</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Outgoing Taxes (Documentation Only) --}}
                        <div>
                            <x-input-label :value="__('Outgoing Tax Toggles (Documentation)')" />
                            @foreach ($taxes as $tax)
                                <label for="out-tax-{{ $tax->id }}" class="flex items-center mt-2">
                                    <input type="checkbox" name="outgoing_tax_ids[]" value="{{ $tax->id }}"
                                        id="out-tax-{{ $tax->id }}" class="form-checkbox">
                                    <span class="ml-2 text-sm text-gray-600">{{ $tax->tax_name }}</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Incoming Taxes (Now uses x-for and x-model) --}}
                        <div>
                            <x-input-label :value="__('Incoming Taxes (Used for Cost Calculation)')" />
                            <p class="text-xs text-gray-500 mb-2">Select taxes to apply the multiplicative reduction.</p>
                            
                            {{-- Loop over the Alpine state array --}}
                            <template x-for="tax in incomingTaxes" :key="tax.id">
                                <label :for="'in-tax-' + tax.id"
                                    class="flex items-center mt-2 p-1 rounded hover:bg-yellow-200 transition">
                                    <input type="checkbox" name="incoming_tax_ids[]" :value="tax.id"
                                        :id="'in-tax-' + tax.id"
                                        x-model="tax.checked"
                                        :data-percentage="tax.percentage"
                                        class="form-checkbox calculation-input">
                                    <span class="ml-2 text-sm font-medium text-gray-700" 
                                          x-text="tax.tax_name + ' (' + tax.percentage.toFixed(2) + '% Reduction)'"></span>
                                </label>
                            </template>
                        </div>

                        {{-- Final Vendor Amount (Display) --}}
                        <div class="md:border-l md:pl-6 pt-4 md:pt-0">
                            <x-input-label :value="__('Final Vendor Amount (Cost)')" />
                            <div class="mt-2 p-5 bg-red-100 border-4 border-red-500 rounded-lg shadow-2xl">
                                <p id="vendor-amount-display"
                                    class="mt-1 text-xl font-extrabold text-red-700 tracking-tight"
                                    {{-- Use computed property for live display --}}
                                    x-text="formatForInput(finalVendorAmount, true)">
                                    Rp0.00
                                </p>

                                {{-- Hidden field carries the calculated value --}}
                                <input type="hidden" id="amount" name="amount" :value="finalVendorAmount">
                                <p class="text-xs text-red-900 mt-2">Value submitted to `incoming_invoices.amount`</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 4: DYNAMIC INVOICE ITEMS (THE BIGGEST CHANGE) --}}
                <div class="p-6 bg-gray-50 shadow-xl rounded-lg border-t-4 border-purple-500">
                    <h3 class="text-xl font-bold mb-4 text-purple-800 border-b pb-2">Invoice Items & Specifications</h3>

                    {{-- BUTTONS FOR SHORTCUTS --}}
                    <div class="flex space-x-3 mb-4">
                        <x-secondary-button type="button" @click="showItemModal()">
                            {{ __('Quick Add Item') }}
                        </x-secondary-button>
                        <x-secondary-button type="button" @click="showSpecModal()">
                            {{ __('Quick Add Specification') }}
                        </x-secondary-button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="invoice-items-table">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">
                                    <th class="px-3 py-3 w-5/12">Item</th>
                                    <th class="px-3 py-3 w-20">Qty</th>
                                    <th class="px-3 py-3">Unit Price</th>
                                    <th class="px-3 py-3">
                                        Subtotal (Revenue Base)
                                        <label class="block font-normal normal-case text-gray-600 text-[10px] mt-1">
                                            <input type="checkbox" id="subtotal-override-toggle"
                                                @change="toggleSubtotalOverride()">
                                            Override?
                                        </label>
                                    </th>
                                    <th class="px-3 py-3 w-3/12">Specs (Optional)</th>
                                    <th class="px-3 py-3 w-10">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="invoice-items-body">
                                
                                {{-- Use Alpine x-for to render dynamic rows --}}
                                <template x-for="(item, index) in orderItems" :key="item.id">
                                    <tr class="item-row">
                                        <td class="px-3 py-3" data-label="Item">
                                            {{-- Item Select: Updates state on change --}}
                                            <select :name="'items[' + index + '][item_id]'"
                                                x-model.number="item.item_id"
                                                @change="updateItemState(item)"
                                                class="mt-1 block w-full rounded-md border-gray-300 item-select" required>
                                                <option value="">Select Item</option>
                                                {{-- Blade loop remains here for static options --}}
                                                @foreach ($items as $optionItem)
                                                    <option value="{{ $optionItem->id }}">{{ $optionItem->item_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-3" data-label="Quantity">
                                            {{-- Quantity Input: Updates state on input --}}
                                            <input type="number" :name="'items[' + index + '][quantity]'" min="1"
                                                x-model.number="item.quantity"
                                                @input="updateItemState(item)"
                                                class="mt-1 block w-full rounded-md border-gray-300 quantity-input" required>
                                        </td>

                                        {{-- UNIT PRICE COLUMN (Readonly, shows item price) --}}
                                        <td class="px-3 py-3" data-label="Unit Price">
                                            <input type="text" :name="'items[' + index + '][unit_price]'" step="0.01"
                                                min="0" readonly
                                                :value="formatForInput(item.unit_price, false)"
                                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 unit-price-input">
                                        </td>

                                        {{-- SUBTOTAL COLUMN (Editable if overridden) --}}
                                        <td class="px-3 py-3" data-label="Subtotal">
                                            <input type="text" :name="'items[' + index + '][subtotal]'" step="0.01" min="0"
                                                x-model="item.subtotal"
                                                :readonly="!isSubtotalOverridden"
                                                :style="isSubtotalOverridden ? '' : 'background-color: #f7f7f7;'"
                                                @input="isSubtotalOverridden && updateItemState(item)"
                                                @blur="isSubtotalOverridden && (item.subtotal = formatForInput(parseForCalculation(item.subtotal), false))"
                                                class="mt-1 block w-full rounded-md border-gray-300 subtotal-input" required>
                                        </td>
                                        <td class="px-3 py-3" data-label="Specs">
                                            {{-- Specs Select: Uses item.availableSpecs for options --}}
                                            <select :name="'items[' + index + '][item_spec_ids][]'"
                                                x-model="item.item_spec_ids"
                                                class="mt-1 block w-full rounded-md border-gray-300 spec-select select2-enabled"
                                                multiple>
                                                <template x-for="spec in item.availableSpecs" :key="spec.id">
                                                    <option :value="spec.id" x-text="spec.item_description"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-3 py-3" data-label="Action">
                                            <button type="button" @click="removeItem(item.id)"
                                                class="text-red-600 hover:text-red-900 font-semibold text-sm">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div> {{-- End overflow-x-auto --}}

                    <div class="mt-4">
                        <x-secondary-button type="button"
                            @click="addItem()">{{ __('Add Item Row') }}</x-secondary-button>
                    </div>
                </div>

            </div>
        </x-pages.form>

        {{-- JAVASCRIPT (The new Alpine initialization script is here) --}}
        {{-- IMPORTANT: The <script> block from Section 1 goes here --}}
        @include('pages.transactions.orders.create-script') 

    </div>

    {{-- ITEM QUICK-CREATE MODAL PLACEHOLDER --}}
    <div id="item-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h4 class="text-lg font-bold mb-4">Quick Add New Item</h4>
            <form id="quick-item-form" onsubmit="quickCreateItem(event); return false;">
                @csrf
                <div>
                    <x-input-label for="new_item_name" value="{{ __('Item Name') }}" />
                    <x-text-input id="new_item_name" name="name" type="text" class="mt-1 block w-full"
                        required />
                </div>
                <div class="mt-4">
                    <x-input-label for="new_item_price" value="{{ __('Unit Price') }}" />
                    <x-text-input id="new_item_price" name="price" type="number" step="0.01" min="0"
                        class="mt-1 block w-full" required />
                </div>
                <div class="mt-6 flex justify-end space-x-2">
                    <x-secondary-button type="button"
                        @click="hideItemModal()">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Save New Item') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- SPEC QUICK-CREATE MODAL PLACEHOLDER --}}
    <div id="spec-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h4 class="text-lg font-bold mb-4">Quick Add New Specification</h4>
            <form id="quick-spec-form" onsubmit="quickCreateSpec(event); return false;">
                @csrf
                <div>
                    <x-input-label for="spec_for_item_id" value="{{ __('Associate with Item') }}" />
                    <select id="spec_for_item_id" name="item_id" class="mt-1 block w-full rounded-md border-gray-300 select2-enabled"
                        required>
                        <option value="">Select Item</option>
                        {{-- Options populated here by Blade/JS --}}
                        @foreach ($items as $item)
                             <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4 border-t pt-4">
                    <x-input-label value="{{ __('Specification Details') }}" class="mb-2" />

                    <div id="spec-inputs-container" class="space-y-2">
                        {{-- SPEC INPUT TEMPLATE --}}
                        <div id="spec-input-template" class="flex space-x-2 items-center" style="display: none;">
                            <x-text-input name="descriptions[]" type="text" class="block w-full"
                                placeholder="e.g., Color: Black" />
                            <button type="button" onclick="removeSpecInput(this)"
                                class="text-red-600 hover:text-red-800 text-lg">&times;</button>
                        </div>
                    </div>

                    <div class="mt-3">
                        <x-secondary-button type="button" onclick="addSpecInput()">
                            {{ __('Add Another Spec Field') }}
                        </x-secondary-button>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <x-secondary-button type="button"
                        @click="hideSpecModal()">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Save New Specs') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>

{{-- I recommend moving the <script> logic (Section 1) into a separate Blade include 
     file like 'resources/views/transactions/orders/create-script.blade.php' 
     and using @include('...') for better view hygiene. --}}