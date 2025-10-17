<x-app-layout>
    <x-pages.form resource="orders" action="store" :item="null">

        <div class="space-y-6 max-w-4xl mx-auto">

            {{-- SECTION 1: CORE ORDER & PO DETAILS --}}
            <div class="p-6 bg-white shadow-xl rounded-lg border-t-4 border-indigo-500">
                <h2 class="text-2xl font-bold mb-4 text-indigo-800 border-b pb-2">Core Order Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Core Order Inputs --}}
                    <div>
                        <x-input-label for="client_id" :value="__('Client')" />
                        <select id="client_id" name="client_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                            @endforeach
                        </select>
                    </div>

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

                    {{-- Revenue Input (Base for all calculations) --}}
                    <div>
                        <x-input-label for="total_customer_payment" :value="__('Payment (Revenue)')" />
                        <x-text-input id="total_customer_payment" name="total_customer_payment" type="text"
                            step="0.01" class="mt-1 block w-full bg-green-100 font-semibold" :value="old('total_customer_payment')"
                            required />
                    </div>

                    {{-- Agreement Fee Input --}}
                    <div>
                        <x-input-label for="agreement_percentage" :value="__('Agreement Fee (%)')" />
                        <x-text-input id="agreement_percentage" name="agreement_percentage" type="number"
                            step="0.01" class="mt-1 block w-full" :value="old('agreement_percentage', 4.0)" required />
                    </div>

                    {{-- Currency Input --}}
                    <div>
                        <x-input-label for="cur" :value="__('Currency')" />
                        <x-text-input id="cur" name="cur" type="text" class="mt-1 block w-full"
                            :value="old('cur', 'IDR')" required />
                    </div>

                    {{-- Vendor Selection --}}
                    <div>
                        <x-input-label for="vendor_id" :value="__('Vendor (Cost Side)')" />
                        <select id="vendor_id" name="vendor_id" class="form-select mt-1 block w-full">
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department (D-Code) --}}
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

            {{-- SECTION 3: TAX SELECTION AND VENDOR AMOUNT DISPLAY (THE CRUCIAL PART) --}}
            <div class="p-6 bg-yellow-100 shadow-xl rounded-lg border-t-4 border-red-500">
                <h3 class="text-xl font-semibold mb-4 text-red-800 border-b pb-2">Taxes & Final Vendor Amount</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Outgoing Taxes (For Documentation) --}}
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

                    {{-- Incoming Taxes (For Cost Calculation) --}}
                    <div>
                        <x-input-label :value="__('Incoming Taxes (Used for Cost Calculation)')" />
                        <p class="text-xs text-gray-500 mb-2">Select taxes to apply the multiplicative reduction.</p>
                        @foreach ($taxes as $tax)
                            <label for="in-tax-{{ $tax->id }}"
                                class="flex items-center mt-2 p-1 rounded hover:bg-yellow-200 transition">
                                <input type="checkbox" name="incoming_tax_ids[]" value="{{ $tax->id }}"
                                    id="in-tax-{{ $tax->id }}" {{-- CRUCIAL: Pass the percentage to the JavaScript --}}
                                    data-percentage="{{ $tax->tax_percentage }}"
                                    class="form-checkbox calculation-input">
                                <span class="ml-2 text-sm font-medium text-gray-700">{{ $tax->tax_name }}
                                    ({{ number_format($tax->tax_percentage, 2) }}% Reduction)
                                </span>
                            </label>
                        @endforeach
                    </div>

                    {{-- Final Vendor Amount (The Requested Output) --}}
                    <div class="md:border-l md:pl-6 pt-4 md:pt-0">
                        <x-input-label :value="__('Final Vendor Amount (Cost)')" />
                        <div class="mt-2 p-5 bg-red-100 border-4 border-red-500 rounded-lg shadow-2xl">
                            <p id="vendor-amount-display"
                                class="mt-1 text-xl font-extrabold text-red-700 tracking-tight">
                                0.00
                            </p>

                            {{-- This hidden field carries the calculated value for the store function --}}
                            <input type="hidden" id="amount" name="amount" value="0.00">
                            <p class="text-xs text-red-900 mt-2">Value submitted to `incoming_invoices.amount`</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: DYNAMIC INVOICE ITEMS --}}
            <div class="p-6 bg-gray-50 shadow-xl rounded-lg border-t-4 border-purple-500">
                <h3 class="text-xl font-bold mb-4 text-purple-800 border-b pb-2">Invoice Items & Specifications</h3>

                {{-- BUTTONS FOR SHORTCUTS --}}
                <div class="flex space-x-3 mb-4">
                    <x-secondary-button type="button" onclick="showItemModal()">
                        {{ __('Quick Add Item') }}
                    </x-secondary-button>
                    <x-secondary-button type="button" onclick="showSpecModal()">
                        {{ __('Quick Add Specification') }}
                    </x-secondary-button>
                </div>

                <table class="min-w-full divide-y divide-gray-200" id="invoice-items-table">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">
                            <th class="px-3 py-3">Item</th>
                            <th class="px-3 py-3 w-20">Quantity</th>
                            <th class="px-3 py-3">Unit Price</th>
                            <th class="px-3 py-3">
                                Subtotal (Revenue Base)
                                <label class="block font-normal normal-case text-gray-600 text-[10px] mt-1">
                                    <input type="checkbox" id="subtotal-override-toggle"
                                        onclick="toggleSubtotalOverride(this.checked)">
                                    Override?
                                </label>
                            </th>
                            <th class="px-3 py-3">Specs (Optional)</th>
                            <th class="px-3 py-3 w-10">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="invoice-items-body">
                        {{-- ITEM TEMPLATE ROW (Hidden, will be cloned by JS) --}}
                        <tr id="item-row-template" class="item-row" style="display: none;">
                            <td class="px-3 py-3">
                                <select name="items[_INDEX_][item_id]"
                                    onchange="updateSubtotal(this.closest('tr')); filterSpecs(this);"
                                    class="mt-1 block w-full rounded-md border-gray-300 item-select" required disabled>
                                    <option value="">Select Item</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" name="items[_INDEX_][quantity]" min="1" value="1"
                                    oninput="updateSubtotal(this.closest('tr'))"
                                    class="mt-1 block w-full rounded-md border-gray-300 quantity-input" required disabled>
                            </td>

                            {{-- UNIT PRICE COLUMN (Readonly, just shows item price) --}}
                            <td class="px-3 py-3">
                                <input type="text" name="items[_INDEX_][unit_price]" step="0.01"
                                    min="0" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 unit-price-input"
                                    value="0.00">
                            </td>

                            {{-- SUBTOTAL COLUMN (Editable, shows calculated price * qty by default) --}}
                            <td class="px-3 py-3">
                                <input type="text" name="items[_INDEX_][subtotal]" step="0.01" min="0"
                                    readonly style="background-color: #f7f7f7;" oninput="calculateVendorCost()"
                                    class="mt-1 block w-full rounded-md border-gray-300 subtotal-input" required disabled>
                            </td>
                            <td class="px-3 py-3">
                                <select name="items[_INDEX_][item_spec_ids][]"
                                    class="mt-1 block w-full rounded-md border-gray-300 spec-select select2-enabled"
                                    multiple disabled>
                                </select>
                            </td>
                            <td class="px-3 py-3">
                                <button type="button" onclick="removeItem(this)"
                                    class="text-red-600 hover:text-red-900 font-semibold text-sm">Remove</button>
                            </td>
                        </tr>
                        {{-- ACTUAL ITEM ROWS WILL BE INSERTED HERE BY JS --}}
                    </tbody>
                </table>

                <div class="mt-4">
                    <x-secondary-button type="button"
                        onclick="addItem()">{{ __('Add Item Row') }}</x-secondary-button>
                </div>
            </div>
        </div>

        {{-- JAVASCRIPT FOR LIVE CALCULATION (TIDIED) --}}
        <script>
            const itemSpecsMap = @json($items);
            let itemIndex = 0; // Global counter to track the item index
            let isSubtotalOverridden = false;

            // ------------------------------------------------------------------
            // HELPER FUNCTIONS FOR FORMATTING (FIXED: Manual 'Rp' prepending)
            // ------------------------------------------------------------------

            /**
             * Formats a raw number for display in the input fields (e.g., 10000000 -> 10,000,000.00).
             * If isCurrency is true, it prepends the "Rp" symbol WITHOUT A SPACE to prevent overflow.
             */
            function formatForInput(value, isCurrency = false) {
                // Ensure the fallback value is "Rp0.00" (no space) for consistency
                if (isNaN(value) || value === null) return isCurrency ? 'Rp0.00' : '0.00';

                const options = {
                    // Use US formatting style (comma for thousands, dot for decimals) for consistency
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                };

                // Use standard number format (this generates '960,000,000.00')
                const formatter = new Intl.NumberFormat('en-US', options);
                let formatted = formatter.format(value);

                if (isCurrency) {
                    // Manually prepend 'Rp' without a space to create the desired format (e.g., Rp960,000,000.00)
                    formatted = 'Rp' + formatted;
                }

                return formatted;
            }

            /**
             * Strips formatting to get the raw numeric value (e.g., 10,000,000.00 -> 10000000).
             */
            function parseForCalculation(formattedValue) {
                // Remove all commas and the 'Rp' prefix if present.
                const cleanString = String(formattedValue).replace(/Rp/g, '').replace(/,/g, '').trim();
                return parseFloat(cleanString) || 0;
            }

            // ------------------------------------------------------------------
            // UI STATE HELPER
            // ------------------------------------------------------------------

            /**
             * Sets the readonly state and background style for a subtotal input.
             * @param {HTMLElement} input - The subtotal input element.
             * @param {boolean} isChecked - Whether the override is enabled.
             */
            function setSubtotalInputState(input, isChecked) {
                if (isChecked) {
                    input.removeAttribute('readonly');
                    input.style.backgroundColor = '';
                } else {
                    input.setAttribute('readonly', 'readonly');
                    input.style.backgroundColor = '#f7f7f7';
                }
            }

            // ------------------------------------------------------------------
            // SUBTOTAL OVERRIDE LOGIC (TIDIED)
            // ------------------------------------------------------------------
            function toggleSubtotalOverride(isChecked) {
                isSubtotalOverridden = isChecked;
                document.querySelectorAll('.subtotal-input').forEach(input => {
                    setSubtotalInputState(input, isChecked); // Use the new helper
                    if (!isChecked) {
                        updateSubtotal(input.closest('tr')); // Recalculate when override is disabled
                    }
                });
            }

            // ------------------------------------------------------------------
            // MODAL FUNCTIONS (CLEANED UP)
            // ------------------------------------------------------------------
            function showItemModal() {
                // NOTE: Replace alert() with a custom modal UI as per guidelines.
                document.getElementById('item-modal').style.display = 'flex';
            }

            function hideItemModal() {
                document.getElementById('item-modal').style.display = 'none';
            }

            /**
             * The canonical and only definition of showSpecModal.
             * Ensures at least one input field is present when the modal opens.
             */
            function showSpecModal() {
                document.getElementById('spec-modal').style.display = 'flex';
                const container = document.getElementById('spec-inputs-container');
                if (container.querySelectorAll('.flex:not([style*="none"])').length === 0) {
                    addSpecInput();
                }
            }

            function hideSpecModal() {
                document.getElementById('spec-modal').style.display = 'none';
            }

            // ------------------------------------------------------------------
            // CORE CALCULATION LOGIC
            // ------------------------------------------------------------------
            function updateSubtotal(row) {
                const itemSelect = row.querySelector('.item-select');
                const quantityInput = row.querySelector('.quantity-input');
                const unitPriceInput = row.querySelector('.unit-price-input');
                const subtotalInput = row.querySelector('.subtotal-input');

                const selectedItemId = itemSelect.value;
                // NOTE: quantity input value does not need parseForCalculation if it's type="number"
                const quantity = parseFloat(quantityInput.value) || 0;

                const itemData = itemSpecsMap.find(item => item.id == selectedItemId);
                // Use itemData.item_price as the unit price source
                const unitPrice = itemData ? (parseFloat(itemData.item_price) || 0) : 0;
                const calculatedSubtotal = unitPrice * quantity;

                // Unit Price is displayed and formatted (not currency, just number)
                unitPriceInput.value = formatForInput(unitPrice, false);

                if (!isSubtotalOverridden) {
                    // Only update the subtotal if it hasn't been manually overridden
                    // Subtotal should also be just number formatting, not currency ('Rp')
                    subtotalInput.value = formatForInput(calculatedSubtotal, false);
                }

                calculateVendorCost();
            }

            function calculateVendorCost() {
                const totalPaymentInput = document.getElementById('total_customer_payment');
                const agreementPercentInput = document.getElementById('agreement_percentage');
                const incomingTaxCheckboxes = document.querySelectorAll('input[name="incoming_tax_ids[]"]');
                const vendorAmountDisplay = document.getElementById('vendor-amount-display');
                const amountHiddenInput = document.getElementById('amount');

                // Parse the display value of the input, which might have commas
                const totalPayment = parseForCalculation(totalPaymentInput.value);
                const agreementPercent = parseFloat(agreementPercentInput.value) || 0;

                if (totalPayment <= 0) {
                    // Display 0 as currency
                    vendorAmountDisplay.textContent = formatForInput(0, true);
                    amountHiddenInput.value = 0.00;
                    return;
                }

                const agreementRate = agreementPercent / 100;
                let totalDeductionRate = agreementRate;

                incomingTaxCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const taxPercentage = parseFloat(checkbox.dataset.percentage) || 0;
                        totalDeductionRate += (taxPercentage / 100);
                    }
                });

                // Apply the Combined Deduction Factor (ADDITIVE LOGIC)
                let finalAmount = totalPayment * (1 - totalDeductionRate);

                const finalAmountRounded = parseFloat(finalAmount.toFixed(2));
                // Display the final amount as currency (prepends 'Rp')
                vendorAmountDisplay.textContent = formatForInput(finalAmountRounded, true);
                amountHiddenInput.value = finalAmountRounded;
            }

            // ------------------------------------------------------------------
            // SPECIFICATION FILTERING & ROW MANAGEMENT
            // ------------------------------------------------------------------
            function filterSpecs(itemDropdown) {
                const selectedItemId = itemDropdown.value;
                const row = itemDropdown.closest('tr');
                const specDropdown = row.querySelector('.spec-select');

                specDropdown.innerHTML = '';
                if (!selectedItemId) {
                    return;
                }

                const selectedItemData = itemSpecsMap.find(item => item.id == selectedItemId);

                if (selectedItemData && selectedItemData.item_specs) {
                    selectedItemData.item_specs.forEach(spec => {
                        const option = document.createElement('option');
                        option.value = spec.id;
                        option.textContent = spec.item_description;
                        specDropdown.appendChild(option);
                    });
                }
            }

            function addItem() {
                const templateRow = document.getElementById('item-row-template');
                const newRow = templateRow.cloneNode(true);

                newRow.style.display = 'table-row';
                newRow.removeAttribute('id');

                const htmlWithIndex = newRow.innerHTML.replace(/_INDEX_/g, itemIndex);
                newRow.innerHTML = htmlWithIndex;

                document.getElementById('invoice-items-body').appendChild(newRow);

                newRow.querySelectorAll('input, select').forEach(input => {
                    input.removeAttribute('disabled');
                    if (input.tagName === 'INPUT' && input.type === 'text') {
                        const isPriceField = input.classList.contains('unit-price-input') || input.classList.contains(
                            'subtotal-input');
                        if (isPriceField) {
                            input.value = formatForInput(0.00, false);
                        } else {
                            input.value = input.classList.contains('quantity-input') ? 1 : '';
                        }
                    } else if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    }

                    if (input.classList.contains('subtotal-input')) {
                        // Use the helper to set the initial state based on the global flag
                        setSubtotalInputState(input, isSubtotalOverridden);
                    }
                });

                newRow.querySelector('.spec-select').innerHTML = '';
                // Initialize subtotal, which also calls calculateVendorCost
                updateSubtotal(newRow);
                filterSpecs(newRow.querySelector('.item-select'));

                itemIndex++;

                if (typeof InitializeSelect2 === 'function') {
                    InitializeSelect2(newRow.querySelector('.spec-select'));
                }
            }

            function removeItem(button) {
                const row = button.closest('tr');
                row.remove();
                calculateVendorCost();
            }

            function quickCreateItem(event) {
                event.preventDefault();

                const form = document.getElementById('quick-item-form');
                const formData = new FormData(form);

                // NOTE: Removed `alert()` as per system instructions
                fetch('{{ route('items.quickStore') }}', { // Use the defined route name
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 1. Add the new item to the global map for filtering/pricing
                            itemSpecsMap.push(data.item);

                            // 2. Add the new item as an option to ALL existing item select boxes
                            const newOption = new Option(data.item.item_name, data.item.id);
                            document.querySelectorAll('.item-select').forEach(select => {
                                select.add(newOption.cloneNode(true));
                            });

                            // 💡 NEW LOGIC: Update the Spec Modal's "Associate with Item" dropdown
                            const specModalSelect = document.getElementById('spec_for_item_id');
                            if (specModalSelect) {
                                specModalSelect.add(newOption.cloneNode(true));
                            }

                            // 4. Close the modal and reset the form
                            console.log('New Item "' + data.item.item_name + '" created successfully!');
                            hideItemModal();
                            form.reset();
                        } else {
                            console.error('Error saving item: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        // NOTE: Removed alert()
                    });
            }

            function addSpecInput() {
                const template = document.getElementById('spec-input-template');
                const container = document.getElementById('spec-inputs-container');

                // Clone the template, make it visible, and append it
                const newSpecInput = template.cloneNode(true);
                newSpecInput.removeAttribute('id');
                newSpecInput.style.display = 'flex';

                // Reset the input value (optional, but good practice)
                newSpecInput.querySelector('input').value = '';

                container.appendChild(newSpecInput);
            }

            function removeSpecInput(button) {
                const container = document.getElementById('spec-inputs-container');
                const inputDiv = button.closest('.flex');

                // Ensure we always keep at least one input field
                if (container.querySelectorAll('.flex:not([style*="none"])').length > 1) {
                    inputDiv.remove();
                } else {
                    // NOTE: Removed alert()
                    console.warn("You must have at least one specification detail field.");
                }
            }

            function quickCreateSpec(event) {
                event.preventDefault(); // Stop the form's default submission

                const form = document.getElementById('quick-spec-form');
                const formData = new FormData();

                // 1. Get the item_id and check for validity
                const itemId = document.getElementById('spec_for_item_id').value;
                if (!itemId) {
                    // NOTE: Removed alert()
                    console.error("Please select an item to associate the specification with.");
                    return;
                }
                formData.append('item_id', itemId);

                // 2. Add CSRF token
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                // 3. Collect and filter description inputs
                const descriptionInputs = form.querySelectorAll('input[name="descriptions[]"]');
                let validSpecCount = 0;

                descriptionInputs.forEach(input => {
                    const value = input.value.trim();
                    if (value !== '') {
                        // Append only non-empty values
                        formData.append('descriptions[]', value);
                        validSpecCount++;
                    }
                });

                // IMPORTANT: Check if any valid specifications were entered
                if (validSpecCount === 0) {
                    // NOTE: Removed alert()
                    console.error("Please enter at least one specification detail.");
                    return;
                }

                fetch('{{ route('item-specs.quickStore') }}', { // Use the defined route name
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        // 💡 IMPORTANT: Check for non-200 status code errors here
                        if (!response.ok) {
                            // If it's a validation error (422) or server error, throw the response for the catch block
                            return response.json().then(errorData => {
                                // Throw the error data so it can be handled below
                                throw errorData;
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const itemId = document.getElementById('spec_for_item_id').value;
                            const itemToUpdate = itemSpecsMap.find(item => item.id == itemId);

                            // 1. Get the specs array (it's now definitely data.specs)
                            const newSpecs = Array.isArray(data.specs) ? data.specs : [];

                            if (itemToUpdate && newSpecs.length > 0) {
                                if (!itemToUpdate.item_specs) {
                                    itemToUpdate.item_specs = [];
                                }

                                // Push all new specs into the local map
                                newSpecs.forEach(spec => {
                                    itemToUpdate.item_specs.push(spec);
                                });

                                // Update the spec dropdowns
                                document.querySelectorAll('.item-select').forEach(select => {
                                    if (select.value == itemId) {
                                        filterSpecs(select);
                                    }
                                });

                                // 2. Change the alert message to use the count and a generic message
                                console.log(newSpecs.length + ' New Specification(s) created and linked successfully!');
                            }

                            // 3. Clean up the modal
                            hideSpecModal();
                            form.reset();

                            // 4. Reset the dynamic input container (Clean slate for next use)
                            const template = document.getElementById('spec-input-template');
                            document.getElementById('spec-inputs-container').innerHTML = template.outerHTML;

                        } else {
                            console.error('Error saving specification: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);

                        // Handle Validation errors (if the response was JSON with an 'errors' structure)
                        if (error.errors) {
                            const errorMessages = Object.values(error.errors).map(arr => arr.join('\n')).join('\n');
                            // NOTE: Removed alert()
                            console.error('Validation Error:\n' + errorMessages);
                        }
                        // Handle the explicit check in the controller
                        else if (error.message && error.message.includes('at least one specification detail')) {
                            // NOTE: Removed alert()
                            console.error(error.message);
                        } else {
                            // Generic fallback error
                            // NOTE: Removed alert()
                            console.error('An unexpected error occurred during specification creation.');
                        }
                    });
            }

            // ------------------------------------------------------------------
            // DOM READY LISTENERS
            // ------------------------------------------------------------------
            document.addEventListener('DOMContentLoaded', function() {
                const totalPaymentInput = document.getElementById('total_customer_payment');
                const agreementPercentInput = document.getElementById('agreement_percentage');
                const incomingTaxCheckboxes = document.querySelectorAll('input[name="incoming_tax_ids[]"]');

                // Attach calculation listeners to the correct global function
                totalPaymentInput.addEventListener('input', calculateVendorCost);
                agreementPercentInput.addEventListener('input', calculateVendorCost);
                incomingTaxCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', calculateVendorCost);
                });

                // Input Formatting: Parse on blur and re-format
                totalPaymentInput.addEventListener('blur', function(e) {
                    // Format as number (no currency symbol in the input field)
                    let rawValue = parseForCalculation(e.target.value);
                    e.target.value = formatForInput(rawValue, false);
                });

                // Apply formatting to existing total payment input on load (Format as number)
                totalPaymentInput.value = formatForInput(parseForCalculation(totalPaymentInput.value || 0), false);

                // Input Formatting: Apply to subtotal inputs using event delegation on the body (safer for dynamic rows)
                document.getElementById('invoice-items-body').addEventListener('blur', function(e) {
                    if (e.target.classList.contains('subtotal-input')) {
                        let rawValue = parseForCalculation(e.target.value);
                        e.target.value = formatForInput(rawValue, false); // Format as number
                        calculateVendorCost(); // Recalculate if subtotal is manually changed
                    }
                }, true);

                // Final initial run
                calculateVendorCost();

                // Add a starter item when the page loads
                // Check if only the template row is present (assuming an initial load state check)
                if (document.querySelectorAll('.item-row').length === 1 && !document.querySelectorAll(
                        '.item-row:not([id="item-row-template"])').length) {
                    addItem();
                }

                // Initialize Select2 if available
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                    $('.select2-enabled').select2({
                        placeholder: "Select specifications",
                        allowClear: true
                    });
                }
            });
        </script>
    </x-pages.form>

    {{-- 💡 ITEM QUICK-CREATE MODAL PLACEHOLDER --}}
    <div id="item-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h4 class="text-lg font-bold mb-4">Quick Add New Item</h4>
            {{-- THIS FORM WOULD POST TO A NEW CONTROLLER METHOD VIA AJAX/LIVEWIRE/ETC. --}}
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
                        onclick="hideItemModal()">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Save New Item') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- 💡 SPEC QUICK-CREATE MODAL PLACEHOLDER --}}
    <div id="spec-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h4 class="text-lg font-bold mb-4">Quick Add New Specification</h4>
            <form id="quick-spec-form" onsubmit="quickCreateSpec(event); return false;">
                @csrf
                <div>
                    <x-input-label for="spec_for_item_id" value="{{ __('Associate with Item') }}" />
                    <select id="spec_for_item_id" name="item_id" class="mt-1 block w-full rounded-md border-gray-300"
                        required>
                        <option value="">Select Item</option>
                        {{-- Options populated by JS on DOMContentLoaded --}}
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

                        {{-- ACTUAL INPUTS WILL BE ADDED HERE --}}
                    </div>

                    <div class="mt-3">
                        <x-secondary-button type="button" onclick="addSpecInput()">
                            {{ __('Add Another Spec Field') }}
                        </x-secondary-button>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <x-secondary-button type="button"
                        onclick="hideSpecModal()">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Save New Specs') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>