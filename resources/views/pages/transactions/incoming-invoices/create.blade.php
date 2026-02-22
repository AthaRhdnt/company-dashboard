<x-app-layout>
    <x-pages.form resource="incoming-invoices" action="store" :item="null">
        <div class="space-y-6 max-w-4xl mx-auto">
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
            @if (session('error') || $errors->any())
                <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                        class="p-4 mb-4 bg-red-100 text-red-700 rounded-md border border-red-200 flex justify-between items-start">
                        <span>{{ session('error') }}</span>
                        <button type="button" @click="showMessage = false"
                            class="ml-4 -mt-1 p-1 rounded-full text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-600/50">
                            {{-- SVG Cross Icon --}}
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
            {{-- SECTION: Invoice Reference --}}
            <div class="p-6 bg-blue-50 shadow-xl rounded-lg border-t-4 border-blue-500 mb-6">
                <h2 class="text-2xl font-bold mb-4 text-blue-800 border-b pb-2">Invoice Reference</h2>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="based_on_invoice_checkbox" class="form-checkbox text-blue-600">
                        <span class="ml-2 text-gray-700 font-medium">Based on Invoice:</span>
                    </label>
                    <select id="based_on_invoice_select"
                        class="hidden mt-1 block w-80 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        style="padding: 0 .75rem">
                        <option value="">Select Invoice Number</option>
                        @foreach ($invoices as $invoice)
                            <option value="{{ $invoice->id }}">{{ $invoice->inv_number }} | {{ $invoice->vendor->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="referenced_invoice_id" id="form_referenced_invoice_id"
                    value="{{ old('referenced_invoice_id') }}">
            </div>

            {{-- SECTION: MCN Order No --}}
            <div class="p-6 bg-green-50 shadow-xl rounded-lg border-t-4 border-green-500 mb-6">
                <h2 class="text-2xl font-bold mb-4 text-green-800 border-b pb-2">MCN Order No.</h2>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="based_on_order_checkbox" class="form-checkbox text-green-600">
                        <span class="ml-2 text-gray-700 font-medium">For Order No:</span>
                    </label>
                    <select id="based_on_order_select"
                        class="hidden mt-1 block w-80 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        style="padding: 0 .75rem">
                        <option value="">Select Order Number</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->ord_number }} | {{ $order->project_name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="order_id" id="form_order_id" value="{{ old('order_id') }}">
                </div>
            </div>

            {{-- SECTION: Core Invoice Details --}}
            <div class="p-6 bg-white shadow-xl rounded-lg border-t-4 border-indigo-500 mb-6">
                <h2 class="text-2xl font-bold mb-4 text-indigo-800 border-b pb-2">Core Invoice Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <x-input-label for="vendor_id" :value="__('Vendor')" />
                        <select id="vendor_id" name="vendor_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            style="padding: .5rem .75rem">
                            <option value="">Select Vendor</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="inv_number" :value="__('Vendor Inv. No.')" />
                        <x-text-input id="inv_number" name="inv_number" type="text" class="mt-1 block w-full"
                            :value="old('inv_number')" />
                    </div>
                    <div>
                        <x-input-label for="fp_number" :value="__('Faktur Pajak')" />
                        <x-text-input id="fp_number" name="fp_number" type="text" class="mt-1 block w-full"
                            :value="old('fp_number')" />
                    </div>
                    <div>
                        <x-input-label for="inv_received_date" :value="__('RCV. Date')" />
                        <x-text-input id="inv_received_date" name="inv_received_date" type="date"
                            class="mt-1 block w-full" :value="old('inv_received_date', now()->format('Y-m-d'))" required />
                    </div>
                    <div>
                        <x-input-label for="due_date" :value="__('Due Date')" />
                        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                            :value="old('due_date', now()->addDays(14)->format('Y-m-d'))" required />
                    </div>
                    <div>
                        <x-input-label for="fp_date" :value="__('INV/FP Date')" />
                        <x-text-input id="fp_date" name="fp_date" type="date" class="mt-1 block w-full"
                            :value="old('fp_date', now()->format('Y-m-d'))" />
                    </div>
                    <div>
                        <x-input-label for="department_id" value="D-Code" />
                        <select id="department_id" name="department_id"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-300"
                            style="padding: .5rem .75rem">
                            <option value="">Select Dept.</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->department_code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="cur" value="Currency" />
                        <x-text-input id="cur" name="cur" type="text" class="mt-1 block w-full"
                            :value="old('cur', 'IDR')" required />
                    </div>
                    <div id="usage-department-container" class="md:col-span-3">
                        <x-input-label for="usage_department_id" value="Usage D-Code" />
                        <select id="usage_department_id" name="usage_department_id"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-300"
                            style="padding: .5rem .75rem">
                            <option value="">Select Usage Dept. (Optional)</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->department_code }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Only used in "MCN ORDER NO" if no order is selected.
                        </p>
                    </div>
                    <div class="md:col-span-3">
                        <x-input-label for="remark" :value="__('Remark(s)')" />
                        <x-text-input id="remark" name="remark" type="text" class="mt-1 block w-full"
                            :value="old('remark')" />
                    </div>
                </div>
            </div>

            {{-- SECTION: Invoice Items and Totals --}}
            <div class="p-6 bg-gray-50 shadow-xl rounded-xl border-t-4 border-purple-500 mb-10">
                <h2 class="text-2xl font-bold text-purple-800 border-b pb-3 mb-6">Invoice Description</h2>
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full text-sm" id="invoice-items-table">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wide">
                            <tr class="text-left">
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody id="invoice-items-body" class="divide-y divide-gray-200">
                            <tr id="item-row-template" class="item-row hidden">
                                <td class="px-4 py-4" colspan="5">
                                    <label class="text-sm text-gray-500">Description</label>
                                    <textarea name="items[_INDEX_][description]"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-400 focus:ring-purple-300 text-sm p-3 resize-none h-16 mb-4"
                                        placeholder="Enter item description..."></textarea>
                                    <div class="grid grid-cols-4 gap-4">
                                        <div>
                                            <label class="text-xs text-gray-500">Qty</label>
                                            <input type="number" name="items[_INDEX_][quantity]" min="1"
                                                value="1" class="w-full rounded-md shadow-sm quantity-input"
                                                required>
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Unit Price</label>
                                            <input type="text"
                                                class="w-full rounded-md shadow-sm unit-price-input">
                                            <input type="hidden" name="items[_INDEX_][base_unit_price]"
                                                class="base-unit-price-input" value="0">
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Subtotal</label>
                                            <input type="text"
                                                class="w-full rounded-md shadow-sm subtotal-input bg-gray-100"
                                                readonly>
                                            <input type="hidden" name="items[_INDEX_][subtotal]"
                                                class="raw-subtotal-input" value="0.00">
                                        </div>
                                        <div class="flex items-end justify-end">
                                            <button type="button" onclick="removeItem(this)"
                                                class="text-red-600 hover:text-red-800 p-2 rounded-md transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6l-1 14H6L5 6"></path>
                                                    <path d="M10 11v6"></path>
                                                    <path d="M14 11v6"></path>
                                                    <path d="M9 6V4h6v2"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div id="total-container" class="p-4 hidden">
                        <x-input-label for="invoice_total" :value="__('Total')" />
                        <x-text-input id="invoice_total" readonly
                            class="mt-2 px-2 block w-full border-gray-300 bg-gray-100" />
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" onclick="addItem()"
                        class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-md transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Add Item Row
                    </button>
                </div>
                <div id="agreement-fee-container"
                    class="hidden flex items-start gap-8 mt-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <div class="flex-shrink-0">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="based_on_fee_checkbox" class="form-checkbox text-purple-600">
                            <x-input-label class="ml-3" for="agreement_percentage" :value="__('Agreement Fee (%)')" />
                        </label>
                        <input id="agreement_fee_input" type="number" step="0.5" min="0" max="100"
                            name="agreement_percentage" value="0.00"
                            class="mt-2 block w-60 rounded-md border-gray-300 shadow-sm focus:ring-purple-300 focus:border-purple-400">
                    </div>
                    <div class="flex-1"> <!-- Should only show when prepaid pph23 toggled or is_prepraid_pph23 true -->
                        <div class="flex items-center gap-3">
                            <x-input-label for="order_amount" :value="__('Order Amount :')" class="whitespace-nowrap" /> <!-- Should contain the orginal amount before deducted by 2% -->
                            <x-text-input id="order_amount" name="order_amount_display" readonly
                                class="px-2 block w-full border-gray-300 bg-gray-100" />
                        </div>
                        <div id="pph23-prepaid-notice" class="mt-2 p-2 bg-blue-100 border-l-4 border-blue-500 text-blue-700 text-sm rounded-md">
                            ℹ️ This order <strong id="pph23-status-text">is not</strong> subject to prepaid PPh23 (2%).
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="ppn" name="ppn" value="1"
                            {{ old('ppn') ? 'checked' : '' }} class="form-checkbox">
                        <span class="ml-3 text-sm font-medium text-gray-700">PPN + 11%</span>
                    </label>
                </div>
                <div class="mt-6">
                    <x-input-label :value="__('Taxes')" />
                    @foreach ($taxes as $tax)
                        <label for="in-tax-{{ $tax->id }}"
                            class="flex items-center mt-2 p-2 rounded hover:bg-purple-200 transition cursor-pointer">
                            <input type="checkbox" name="incoming_tax_ids[]" value="{{ $tax->id }}"
                                id="in-tax-{{ $tax->id }}" data-percentage="{{ $tax->tax_percentage }}"
                                class="form-checkbox calculation-input">
                            <span class="ml-3 text-sm font-medium text-gray-700">
                                {{ $tax->tax_name }} ({{ number_format($tax->tax_percentage, 2) }}%)
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-6">
                    <x-input-label for="invoice_amount" :value="__('Amount')" /> <!-- Should show the amount after deducted by prepaid pph23 and ofcourse if an of the tax are toggled -->
                    <x-text-input id="invoice_amount" name="amount_display" readonly
                        class="mt-2 px-2 block w-full border-gray-300 bg-gray-100" />
                </div>
            </div>
        </div>

        <script>
            // ------------------------------------------------------------------
            // Global State
            // ------------------------------------------------------------------
            let itemIndex = 0;
            let isContextInitializing = false;
            let lastAgreementFeePct = 0;
            window.orderIsPph23Prepaid = false;
            window.isPpnActive = false

            // ------------------------------------------------------------------
            // Formatting
            // ------------------------------------------------------------------
            function formatForInput(value, isCurrency = false) {
                if (isNaN(value) || value === null) {
                    return isCurrency ? 'Rp0.00' : '0.00';
                }
                const formatter = new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                let formatted = formatter.format(value);
                if (isCurrency) formatted = 'Rp' + formatted;
                return formatted;
            }

            function parseForCalculation(formattedValue) {
                const clean = String(formattedValue).replace(/Rp/g, '').replace(/,/g, '').trim();
                return parseFloat(clean) || 0;
            }

            // ------------------------------------------------------------------
            // Row Calculations
            // ------------------------------------------------------------------
            function recalcRowSubtotal(row) {
                const qtyInput = row.querySelector('.quantity-input');
                const basePriceInput = row.querySelector('.base-unit-price-input');
                const subtotalInput = row.querySelector('.subtotal-input');
                const rawSubtotalInput = row.querySelector('.raw-subtotal-input');
                const qty = parseFloat(qtyInput.value) || 0;
                const basePrice = parseFloat(basePriceInput.value) || 0;

                const feePct = getAgreementFeePct();
                const totalDeduction = getTotalDeductionPct();
                const adjustedUnitPrice = basePrice * (1 - totalDeduction);
                const subtotal = qty * adjustedUnitPrice;

                subtotalInput.value = formatForInput(subtotal, false);
                rawSubtotalInput.value = subtotal.toFixed(2);
                calculateTotals();
            }

            function updateRowFromBase(row) {
                const basePriceInput = row.querySelector('.base-unit-price-input');
                const unitInput = row.querySelector('.unit-price-input');
                const base = parseFloat(basePriceInput.value) || 0;
                const totalDeduction = getTotalDeductionPct();
                const adjusted = base * (1 - totalDeduction);
                unitInput.value = formatForInput(adjusted, false);
                recalcRowSubtotal(row);
            }

            function calculateTotals() {
                let total = 0;
                document.querySelectorAll('.raw-subtotal-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });

                const totalContainer = document.getElementById('total-container');
                const totalInput = document.getElementById('invoice_total');
                const visibleItems = [...document.querySelectorAll('#invoice-items-body .item-row')]
                    .filter(tr => !tr.id);

                if (visibleItems.length > 1) {
                    totalContainer.classList.remove('hidden');
                    totalInput.value = formatForInput(total, true);
                } else {
                    totalContainer.classList.add('hidden');
                }

                // Recover ORIGINAL base total (before ANY deductions)
                const totalDeduction = getTotalDeductionPct();
                const baseTotal = totalDeduction > 0 ? total / (1 - totalDeduction) : total;

                let finalAmount = total;

                // PPN (+11% on base)
                if (window.isPpnActive) {
                    finalAmount += baseTotal * 0.11;
                }

                // Other taxes (e.g., PPh4) — these are % of base
                let otherTaxPct = 0;
                document.querySelectorAll('input[name="incoming_tax_ids[]"]:checked')
                    .forEach(cb => otherTaxPct += (parseFloat(cb.dataset.percentage) / 100) || 0);
                
                finalAmount -= baseTotal * otherTaxPct;

                // 4. Final amount = adjusted subtotal + additive taxes
                // Ensure non-negative
                finalAmount = Math.max(0, finalAmount);

                // Update UI
                document.getElementById('invoice_amount').value = formatForInput(Math.max(0, finalAmount), true);

                const orderAmountDisplay = document.getElementById('order_amount');
                if (orderAmountDisplay) {
                    orderAmountDisplay.value = formatForInput(baseTotal, true);
                }
            }

            // ------------------------------------------------------------------
            // Item Row Management
            // ------------------------------------------------------------------
            function addItem() {
                const template = document.getElementById('item-row-template');
                const newRow = template.cloneNode(true);
                newRow.removeAttribute('id');
                newRow.style.display = 'table-row';
                newRow.innerHTML = newRow.innerHTML.replace(/_INDEX_/g, itemIndex);
                document.getElementById('invoice-items-body').appendChild(newRow);

                newRow.querySelectorAll('input, textarea, select').forEach(el => {
                    el.removeAttribute('disabled');
                    if (el.type === 'number' && el.classList.contains('quantity-input')) {
                        el.value = 1;
                    } else if (el.classList.contains('unit-price-input') || el.classList.contains('subtotal-input')) {
                        el.value = formatForInput(0.00, false);
                    } else if (el.tagName === 'TEXTAREA') {
                        el.value = '';
                    }
                });

                const rawSub = newRow.querySelector('.raw-subtotal-input');
                if (rawSub) rawSub.value = '0.00';
                recalcRowSubtotal(newRow);
                itemIndex++;
                updateUnitPriceReadonlyState(
                    document.getElementById('based_on_order_checkbox')?.checked
                );
            }

            function removeItem(button) {
                button.closest('tr').remove();
                calculateTotals();
            }

            // ------------------------------------------------------------------
            // Helpers
            // ------------------------------------------------------------------

            // Fee handling
            function getTotalDeductionPct() {
                const feePct = getAgreementFeePct() || 0;
                const pph23Pct = window.orderIsPph23Prepaid ? 0.02 : 0;
                return feePct + pph23Pct;
            }

            function getAgreementFeePct() {
                const checkbox = document.getElementById('based_on_fee_checkbox');
                if (!checkbox?.checked) return 0;
                const feeInput = document.getElementById('agreement_fee_input');
                return (parseFloat(feeInput?.value || 0) / 100) || 0;
            }

            // Unit price readonly logic
            function updateUnitPriceReadonlyState(isBasedOnOrder) {
                document.querySelectorAll('.unit-price-input').forEach(input => {
                    input.readOnly = isBasedOnOrder;
                    input.classList.toggle('bg-gray-100', isBasedOnOrder);
                });
            }

            function setPpnActive(isActive) {
                const checkbox = document.getElementById('ppn');
                if (checkbox) {
                    checkbox.checked = isActive;
                }
                window.isPpnActive = isActive;
                calculateTotals(); // optional: recalc immediately
            }

            // Context Initialization
            function initializeFromOrderContext(orderId) {
                if (isContextInitializing) return;
                isContextInitializing = true;

                const checkbox = document.getElementById('based_on_order_checkbox');
                const dropdown = document.getElementById('based_on_order_select');
                const orderIdInput = document.getElementById('form_order_id');

                if (!checkbox || !dropdown || !orderIdInput) {
                    isContextInitializing = false;
                    return;
                }

                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change', {
                    bubbles: true
                }));

                dropdown.classList.remove('hidden');
                dropdown.value = orderId;
                orderIdInput.value = orderId;
                dropdown.dispatchEvent(new Event('change', {
                    bubbles: true
                }));

                isContextInitializing = false;
            }

            // ------------------------------------------------------------------
            // "Based on Order" Logic
            // ------------------------------------------------------------------
            function setupBasedOnOrderSelector() {
                let isOrderLoading = false;

                const checkbox = document.getElementById('based_on_order_checkbox');
                const dropdown = document.getElementById('based_on_order_select');
                const orderIdInput = document.getElementById('form_order_id');
                const invoiceCheckbox = document.getElementById('based_on_invoice_checkbox');
                const usageDeptContainer = document.getElementById('usage-department-container');
                const agreementFeeContainer = document.getElementById('agreement-fee-container');

                if (!checkbox || !dropdown || !orderIdInput) return;

                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        updateUnitPriceReadonlyState(true);
                        if (invoiceCheckbox?.checked) {
                            invoiceCheckbox.checked = false;
                            document.getElementById('based_on_invoice_select')?.classList.add('hidden');
                            document.getElementById('form_referenced_invoice_id').value = '';
                        }
                        dropdown.classList.remove('hidden');
                        usageDeptContainer?.classList.add('hidden');
                        agreementFeeContainer?.classList.remove('hidden');
                    } else {
                        updateUnitPriceReadonlyState(false);
                        dropdown.classList.add('hidden');
                        dropdown.value = '';
                        orderIdInput.value = '';
                        usageDeptContainer?.classList.remove('hidden');
                        agreementFeeContainer?.classList.add('hidden');

                        const feeCheckbox = document.getElementById('based_on_fee_checkbox');
                        const feeInput = document.getElementById('agreement_fee_input');
                        if (feeCheckbox) feeCheckbox.checked = false;
                        if (feeInput) feeInput.value = '0.00';

                        document.querySelectorAll('#invoice-items-body .item-row:not(#item-row-template)')
                            .forEach(row => {
                                const unit = row.querySelector('.unit-price-input');
                                const base = row.querySelector('.base-unit-price-input');
                                base.value = parseForCalculation(unit.value);
                                recalcRowSubtotal(row);
                            });
                        window.orderIsPph23Prepaid = false;
                        const statusText = document.getElementById('pph23-status-text');
                        if (statusText) statusText.textContent = 'is not';
                        document.getElementById('order_amount').value = 'Rp0.00';
                    }
                    calculateTotals();
                });

                dropdown.addEventListener('change', async () => {
                    if (isOrderLoading) return;

                    const orderId = dropdown.value;
                    if (!orderId) return;

                    isOrderLoading = true;

                    itemIndex = 0;
                    orderIdInput.value = orderId;
                    document.getElementById('form_referenced_invoice_id').value = '';

                    try {
                        const res = await fetch(`/incoming-invoices/from-order/${orderId}/template`);
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        const data = await res.json();
                        alert(`Attached to Order ${data.ord_number}!`);

                        if (data.vendor_id) document.getElementById('vendor_id').value = data.vendor_id;
                        if (data.department_id) document.getElementById('department_id').value = data.department_id;
                        document.getElementById('cur').value = data.cur || 'IDR';
                        if (data.inv_received_date) document.getElementById('inv_received_date').value = data
                            .inv_received_date;
                        if (data.due_date) document.getElementById('due_date').value = data.due_date;
                        if (data.fp_date) document.getElementById('fp_date').value = data.fp_date;
                        if (data.inv_number) document.getElementById('inv_number').value = data.inv_number;

                        const feeCheckbox = document.getElementById('based_on_fee_checkbox');
                        const feeInput = document.getElementById('agreement_fee_input');
                        if (data.agreement_percentage) {
                            feeCheckbox.checked = true;
                            feeInput.classList.remove('hidden');
                            feeInput.value = data.agreement_percentage;
                        } else {
                            feeCheckbox.checked = false;
                            feeInput.classList.add('hidden');
                            feeInput.value = '0.00';
                        }

                        document.querySelectorAll('input[name="incoming_tax_ids[]"]').forEach(cb => cb.checked =
                            false);
                        (data.incoming_tax_ids || []).forEach(id => {
                            const cb = document.querySelector(`#in-tax-${id}`);
                            if (cb) cb.checked = true;
                        });
                        // togglePph23Tax(!!data.is_pph23_prepaid);

                        const statusText = document.getElementById('pph23-status-text');
                        if (statusText) {
                            statusText.textContent = data.is_pph23_prepaid ? 'is' : 'is not';
                        }
                        window.orderIsPph23Prepaid = !!data.is_pph23_prepaid;

                        const tbody = document.getElementById('invoice-items-body');
                        tbody.querySelectorAll('tr:not(#item-row-template)').forEach(tr => tr.remove());

                        if (Array.isArray(data.items) && data.items.length > 0) {
                            data.items.forEach(item => {
                                const template = document.getElementById('item-row-template');
                                const newRow = template.cloneNode(true);
                                newRow.removeAttribute('id');
                                newRow.style.display = 'table-row';
                                newRow.innerHTML = newRow.innerHTML.replace(/_INDEX_/g, itemIndex);
                                tbody.appendChild(newRow);

                                const desc = newRow.querySelector('textarea[name$="[description]"]');
                                const qty = newRow.querySelector('.quantity-input');
                                const base = newRow.querySelector('.base-unit-price-input');
                                const unit = newRow.querySelector('.unit-price-input');
                                if (desc) desc.value = item.description || '';
                                if (qty) {
                                    qty.removeAttribute('disabled');
                                    qty.value = item.quantity || 1;
                                }
                                if (base) base.value = item.unit_price || 0;
                                if (unit) unit.value = formatForInput(item.unit_price || 0, false);
                                updateRowFromBase(newRow);
                                itemIndex++;
                            });
                        } else {
                            addItem();
                        }
                    } catch (err) {
                        alert('Failed to load order data.');
                    } finally {
                        isOrderLoading = false;
                        calculateTotals();
                    }
                });
            }

            // ------------------------------------------------------------------
            // "Based on Invoice" Logic
            // ------------------------------------------------------------------
            function setupBasedOnInvoiceSelector() {
                let isInvoiceLoading = false;

                const checkbox = document.getElementById('based_on_invoice_checkbox');
                const dropdown = document.getElementById('based_on_invoice_select');
                const invoiceIdInput = document.getElementById('form_referenced_invoice_id');
                const orderCheckbox = document.getElementById('based_on_order_checkbox');
                const agreementFeeContainer = document.getElementById('agreement-fee-container');
                const usageDeptContainer = document.getElementById('usage-department-container');


                if (!checkbox || !dropdown || !invoiceIdInput) return;

                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        if (orderCheckbox?.checked) {
                            orderCheckbox.checked = false;
                            document.getElementById('based_on_order_select')?.classList.add('hidden');
                            document.getElementById('form_order_id').value = '';
                        }
                        agreementFeeContainer?.classList.add('hidden');
                        usageDeptContainer?.classList.remove('hidden');
                        updateUnitPriceReadonlyState(false);
                        dropdown.classList.remove('hidden');
                    } else {
                        dropdown.classList.add('hidden');
                        dropdown.value = '';
                        invoiceIdInput.value = '';

                        window.orderIsPph23Prepaid = false;
                        document.getElementById('pph23-prepaid-notice')?.classList.add('hidden');
                        document.getElementById('order_amount').value = 'Rp0.00';
                    }
                    calculateTotals();
                });

                dropdown.addEventListener('change', async () => {
                    let isOrderLoading = false;
                    
                    const invoiceId = dropdown.value;
                    if (!invoiceId) return;

                    isInvoiceLoading = true;

                    itemIndex = 0;
                    invoiceIdInput.value = invoiceId;
                    document.getElementById('form_order_id').value = '';

                    try {
                        const res = await fetch(`/incoming-invoices/${invoiceId}/template`);
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        const data = await res.json();
                        alert(`Invoice ${data.inv_number} loaded!`);

                        if (data.vendor_id) document.getElementById('vendor_id').value = data.vendor_id;
                        if (data.department_id) document.getElementById('department_id').value = data.department_id;
                        if (data.usage_department_id) document.getElementById('usage_department_id').value = data.usage_department_id;
                        document.getElementById('cur').value = data.cur || 'IDR';
                        document.getElementById('remark').value = data.remark || '';
                        setPpnActive(!!data.ppn); // !! ensures boolean

                        const feeCheckbox = document.getElementById('based_on_fee_checkbox');
                        const feeInput = document.getElementById('agreement_fee_input');
                        if (data.agreement_percentage) {
                            feeCheckbox.checked = true;
                            feeInput.classList.remove('hidden');
                            feeInput.value = data.agreement_percentage;
                        } else {
                            feeCheckbox.checked = false;
                            feeInput.classList.add('hidden');
                            feeInput.value = '0.00';
                        }

                        document.querySelectorAll('input[name="incoming_tax_ids[]"]').forEach(cb => cb.checked =
                            false);
                        (data.incoming_tax_ids || []).forEach(id => {
                            const cb = document.querySelector(`#in-tax-${id}`);
                            if (cb) cb.checked = true;
                        });

                        const tbody = document.getElementById('invoice-items-body');
                        tbody.querySelectorAll('tr:not(#item-row-template)').forEach(tr => tr.remove());

                        if (Array.isArray(data.items) && data.items.length > 0) {
                            data.items.forEach(item => {
                                const template = document.getElementById('item-row-template');
                                const newRow = template.cloneNode(true);
                                newRow.removeAttribute('id');
                                newRow.style.display = 'table-row';
                                newRow.innerHTML = newRow.innerHTML.replace(/_INDEX_/g, itemIndex);
                                tbody.appendChild(newRow);

                                const desc = newRow.querySelector('textarea[name$="[description]"]');
                                const qty = newRow.querySelector('.quantity-input');
                                const base = newRow.querySelector('.base-unit-price-input');
                                const unit = newRow.querySelector('.unit-price-input');
                                if (desc) desc.value = item.description || '';
                                if (qty) {
                                    qty.removeAttribute('disabled');
                                    qty.value = item.quantity || 1;
                                }
                                if (base) base.value = item.unit_price || 0;
                                if (unit) unit.value = formatForInput(item.unit_price || 0, false);
                                updateRowFromBase(newRow);
                                itemIndex++;
                            });
                        } else {
                            addItem();
                        }
                    } catch (err) {
                        alert('Failed to load invoice data.');
                    } finally {
                        isInvoiceLoading = false;
                        calculateTotals();
                    }
                });
            }

            // ------------------------------------------------------------------
            // Date sync logic
            // ------------------------------------------------------------------
            function setupDateSync() {
                const basedOnOrderCheckbox = document.getElementById('based_on_order_checkbox');
                const basedOnInvoiceCheckbox = document.getElementById('based_on_invoice_checkbox');
                const rcvDateInput = document.getElementById('inv_received_date');
                const fpDateInput = document.getElementById('fp_date');

                if (!basedOnOrderCheckbox || !rcvDateInput || !fpDateInput) return;

                function updateFpDateState() {
                    const isBasedOnOrder = basedOnOrderCheckbox.checked;
                    const isBasedOnInvoice = basedOnInvoiceCheckbox?.checked || false;
                    if (isBasedOnOrder && !isBasedOnInvoice) {
                        fpDateInput.value = rcvDateInput.value;
                        fpDateInput.readOnly = true;
                        fpDateInput.classList.add('readonly-look');
                    } else {
                        fpDateInput.readOnly = false;
                        fpDateInput.classList.remove('readonly-look');
                    }
                }

                rcvDateInput.addEventListener('change', () => {
                    if (basedOnOrderCheckbox.checked && !(basedOnInvoiceCheckbox?.checked || false)) {
                        fpDateInput.value = rcvDateInput.value;
                    }
                });

                basedOnOrderCheckbox.addEventListener('change', updateFpDateState);
                basedOnInvoiceCheckbox?.addEventListener('change', updateFpDateState);
                updateFpDateState();
            }

            // ------------------------------------------------------------------
            // Initialization
            // ------------------------------------------------------------------
            document.addEventListener('DOMContentLoaded', function() {
                setupBasedOnOrderSelector();
                setupBasedOnInvoiceSelector();
                updateUnitPriceReadonlyState(document.getElementById('based_on_order_checkbox')?.checked);

                const feeCheckbox = document.getElementById('based_on_fee_checkbox');
                const feeInput = document.getElementById('agreement_fee_input');
                if (feeCheckbox && feeInput) {
                    feeCheckbox.addEventListener('change', () => {
                        const isChecked = feeCheckbox.checked;
                        
                        if (!isChecked) {
                            // 🔐 remember last fee before turning off
                            lastAgreementFeePct = parseFloat(feeInput.value) || lastAgreementFeePct;
                            feeInput.value = '0.00';
                        } else {
                            // 🔁 restore previous fee
                            feeInput.value = lastAgreementFeePct || feeInput.value || '0.00';
                        }

                        feeInput.classList.toggle('hidden', !isChecked);

                        // 🔄 Recalculate all rows from BASE price
                        document.querySelectorAll('#invoice-items-body .item-row:not(#item-row-template)')
                            .forEach(updateRowFromBase);

                        calculateTotals();
                    });
                    feeInput.classList.add('hidden');
                }

                document.querySelectorAll('input[name="incoming_tax_ids[]"]').forEach(cb => {
                    cb.addEventListener('change', function() {
                        if (this.checked) {
                            document.querySelectorAll('input[name="incoming_tax_ids[]"]').forEach(
                                other => {
                                    if (other !== this) other.checked = false;
                                });
                        }
                        calculateTotals();
                    });
                });

                if (feeInput) {
                    feeInput.addEventListener('input', () => {
                        document.querySelectorAll('#invoice-items-body .item-row:not(#item-row-template)')
                            .forEach(updateRowFromBase);
                    });
                }

                document.addEventListener('input', function(e) {
                    if (!e.target.classList.contains('unit-price-input') &&
                        !e.target.classList.contains('quantity-input')) return;
                    const row = e.target.closest('tr');
                    const baseInput = row.querySelector('.base-unit-price-input');
                    if (e.target.classList.contains('unit-price-input') &&
                        !document.getElementById('based_on_order_checkbox')?.checked) {
                        baseInput.value = parseForCalculation(e.target.value);
                    }
                    recalcRowSubtotal(row);
                });

                document.addEventListener('blur', function(e) {
                    if (!e.target.classList.contains('unit-price-input')) return;
                    if (document.getElementById('based_on_order_checkbox')?.checked) return;
                    const row = e.target.closest('tr');
                    const baseInput = row.querySelector('.base-unit-price-input');
                    e.target.value = formatForInput(parseFloat(baseInput.value) || 0, false);
                }, true);

                const ppnCheckbox = document.getElementById('ppn');
                if (ppnCheckbox) {
                    ppnCheckbox.addEventListener('change', () => {
                        setPpnActive(ppnCheckbox.checked);
                    });
                    // Initialize from initial DOM state (e.g., old() value)
                    setPpnActive(ppnCheckbox.checked);
                }

                calculateTotals();
                setupDateSync();
            });
        </script>

        @if (isset($forOrderId))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    initializeFromOrderContext({{ $forOrderId }});
                });
            </script>
        @endif
    </x-pages.form>
</x-app-layout>
