<x-app-layout>
    <x-pages.form resource="orders" action="store" :item="null">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
        </div>
        @endif

        {{-- Order Reference --}}
        <div class="p-6 bg-blue-50 shadow-xl rounded-lg border-t-4 border-blue-500 mb-6">
            <h2 class="text-2xl font-bold mb-4 text-blue-800 border-b pb-2">Order Reference</h2>
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="based_on_order_checkbox" class="form-checkbox text-blue-600">
                    <span class="ml-2 text-gray-700 font-medium">Based on Order:</span>
                </label>
                <select id="based_on_order_select"
                    class="hidden mt-1 block w-80 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    style="padding: 0 .75rem">
                    <option value="">Select Order Number</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}">{{ $order->ord_number }} | {{ $order->project_name }}</option>
                    @endforeach
                </select>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                If checked, selecting an order will auto-fill client, department, currency, and project name fields.
            </p>
        </div>

        {{-- Core Order Details --}}
        <div class="p-6 bg-white shadow-xl rounded-xl border-t-4 border-indigo-500">
            <h2 class="text-2xl font-bold text-indigo-800 pb-3 border-b">Core Order Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <x-input-label for="client_id" value="Client" />
                    <select id="client_id" name="client_id"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-300"
                        style="padding: .5rem .75rem">
                        <option value="">Select Client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->client_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="ord_number" value="MCN Order Number" />
                    <x-text-input id="ord_number" name="ord_number" type="text" class="mt-1 block w-full"
                        :value="old('ord_number', $suggestedOrderNumber)" required />
                </div>

                <div>
                    <x-input-label for="ord_date" value="Order Date" />
                    <x-text-input id="ord_date" name="ord_date" type="date" class="mt-1 block w-full"
                        :value="old('ord_date', now()->format('Y-m-d'))" required />
                </div>

                <div>
                    <x-input-label for="project_name" value="Project Name" />
                    <x-text-input id="project_name" name="project_name" type="text" class="mt-1 block w-full"
                        :value="old('project_name')" />
                </div>

                <div>
                    <x-input-label for="po_number" value="Customer PO Number" />
                    <x-text-input id="po_number" name="po_number" type="text" class="mt-1 block w-full"
                        :value="old('po_number')" required />
                </div>

                <div>
                    <x-input-label for="po_date" value="PO Date" />
                    <x-text-input id="po_date" name="po_date" type="date" class="mt-1 block w-full"
                        :value="old('po_date')" required />
                </div>

                <div>
                    <x-input-label for="department_id" value="D-Code" />
                    <select id="department_id" name="department_id"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-300"
                        style="padding: .5rem .75rem">
                        <option value="">Select Dept.</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}"
                                {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->department_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="cur" value="Currency" />
                    <x-text-input id="cur" name="cur" type="text" class="mt-1 block w-full"
                        :value="old('cur', 'IDR')" required />
                </div>

                <div class="md:col-span-3">
                    <x-input-label for="remark" value="Remarks" />
                    <x-text-input id="remark" name="remark" type="text" class="mt-1 block w-full"
                        :value="old('remark')" />
                </div>
            </div>
        </div>

        {{-- Ordered Items & Specifications --}}
        <div class="p-6 bg-gray-50 shadow-xl rounded-xl border-t-4 border-purple-500 mb-10">
            <h2 class="text-2xl font-bold text-purple-800 border-b pb-3 mb-6">
                Ordered Items & Specifications
            </h2>

            <div class="flex space-x-3 mb-6">
                <x-secondary-button type="button" onclick="showItemModal()">
                    {{ __('Quick Add Item') }}
                </x-secondary-button>
                <x-secondary-button type="button" onclick="showSpecModal()">
                    {{ __('Quick Add Specification') }}
                </x-secondary-button>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full text-sm" id="invoice-items-table">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wide">
                        <tr class="text-left">
                            <th class="px-4 py-3 min-w-[150px] font-medium">Item</th>
                            <th class="px-4 py-3 w-20 font-medium">Quantity</th>
                            <th class="px-4 py-3 w-32 font-medium">Unit Price</th>
                            <th class="px-4 py-3 w-32 font-medium">
                                Subtotal
                            </th>
                            <th class="px-4 py-3 min-w-[270px] font-medium">
                                <div>
                                    Specs
                                    <span class="mt-0.5 block text-[9px] font-medium text-red-500 leading-tight">Please
                                        Select</span>
                                </div>
                            </th>
                            <th class="px-2 py-2 w-10 font-medium text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody id="invoice-items-body" class="divide-y divide-gray-100 bg-white">
                        <tr id="item-row-template" class="item-row hidden">
                            <td class="px-4 py-3">
                                <select name="items[_INDEX_][item_id]"
                                    onchange="updateSubtotal(this.closest('tr')); filterSpecs(this);"
                                    class="w-full rounded-md border-gray-300 shadow-sm item-select focus:ring-purple-300 focus:border-purple-400 py-2.5 px-3"
                                    required disabled>
                                    <option value="">Select Item</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="number" name="items[_INDEX_][quantity]" min="1" value="1"
                                    oninput="updateSubtotal(this.closest('tr'))"
                                    class="w-full rounded-md border-gray-300 shadow-sm quantity-input bg-gray-50 focus:ring-purple-300 focus:border-purple-400 py-2 px-2 text-center"
                                    required disabled>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <input type="text" name="items[_INDEX_][unit_price]" step="0.01"
                                    min="0" readonly
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm unit-price-input py-2 px-3 text-right"
                                    value="0.00">
                            </td>
                            <td class="px-4 py-3 text-right">
                                <input type="text" name="items[_INDEX_][subtotal]" step="0.01" min="0"
                                    readonly oninput="calculateVendorCost()"
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm subtotal-input py-2 px-3 text-right"
                                    required disabled>
                            </td>
                            <td class="px-4 py-3">
                                <select name="items[_INDEX_][item_spec_ids][]" multiple disabled
                                    class="w-full rounded-md border-gray-300 shadow-sm spec-select select2-enabled focus:ring-purple-300 focus:border-purple-400 py-2 px-3">
                                </select>
                            </td>
                            <td class="px-2 py-2 text-center">
                                <button type="button" onclick="removeItem(this)"
                                    class="text-red-600 hover:text-red-800 font-semibold text-xs p-1">
                                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <input type="hidden" id="total_customer_payment" name="total_customer_payment"
                value="{{ old('total_customer_payment', '0') }}">
            <input type="hidden" id="amount" name="amount" value="{{ old('amount', '0') }}">

            <div class="mt-5">
                <x-secondary-button type="button" onclick="addItem()" class="gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Add Item Row') }}
                </x-secondary-button>

                <div class="mt-5">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_pph23_prepaid" value="1"
                            {{ old('is_pph23_prepaid') ? 'checked' : '' }} class="form-checkbox">
                        <span class="ml-3 text-sm font-medium text-gray-700">Prepaid PPh23 (2%)</span>
                    </label>
                </div>
            </div>
        </div>
        </div>

        <script>
            const itemSpecsMap = @json($items);
            let itemIndex = 0;
            let isSubtotalOverridden = false;
            let toastTimeout;

            function initializeSelect2IfAvailable(element) {
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2 && !element.hasAttribute('data-select2-id')) {
                    $(element).select2({
                        placeholder: "Select specifications",
                        allowClear: true
                    });
                }
            }

            // Formatting
            function formatForInput(value, isCurrency = false) {
                if (isNaN(value) || value === null) return isCurrency ? 'Rp0.00' : '0.00';
                const formatter = new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                let formatted = formatter.format(value);
                if (isCurrency) formatted = 'Rp' + formatted;
                return formatted;
            }

            function parseForCalculation(formattedValue) {
                const cleanString = String(formattedValue).replace(/Rp/g, '').replace(/,/g, '').trim();
                return parseFloat(cleanString) || 0;
            }

            // UI
            function setSubtotalInputState(input, isChecked) {
                if (isChecked) {
                    input.removeAttribute('readonly');
                    input.style.backgroundColor = '';
                } else {
                    input.setAttribute('readonly', 'readonly');
                    input.style.backgroundColor = '#f7f7f7';
                }
            }

            function toggleSubtotalOverride(isChecked) {
                isSubtotalOverridden = isChecked;
                document.querySelectorAll('.subtotal-input').forEach(input => {
                    setSubtotalInputState(input, isChecked);
                    if (isChecked) {
                        input.classList.remove('bg-gray-100');
                        input.classList.add('focus:ring-purple-300', 'focus:border-purple-400');
                    } else {
                        input.classList.add('bg-gray-100');
                        input.classList.remove('focus:ring-purple-300', 'focus:border-purple-400');
                        updateSubtotal(input.closest('tr'));
                    }
                });
            }

            // Modals
            function showItemModal() {
                document.getElementById('item-modal').style.display = 'flex';
            }

            function hideItemModal() {
                document.getElementById('item-modal').style.display = 'none';
            }

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

            function showSuccessToast(message) {
                const toast = document.getElementById('success-toast');
                const text = document.getElementById('success-toast-message');

                text.textContent = message;
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');

                // Hide after delay
                toastTimeout = setTimeout(() => {
                    toast.classList.remove('translate-x-0', 'opacity-100');
                    toast.classList.add('translate-x-full', 'opacity-0');
                }, 2500);
            }

            // Calculations
            function updateSubtotal(row) {
                const itemSelect = row.querySelector('.item-select');
                const quantityInput = row.querySelector('.quantity-input');
                const unitPriceInput = row.querySelector('.unit-price-input');
                const subtotalInput = row.querySelector('.subtotal-input');
                const selectedItemId = itemSelect.value;
                const quantity = parseFloat(quantityInput.value) || 0;
                const itemData = itemSpecsMap.find(item => item.id == selectedItemId);
                const unitPrice = itemData ? (parseFloat(itemData.item_price) || 0) : 0;
                const calculatedSubtotal = unitPrice * quantity;

                unitPriceInput.value = formatForInput(unitPrice, false);
                if (!isSubtotalOverridden) {
                    subtotalInput.value = formatForInput(calculatedSubtotal, false);
                }
            }

            function calculateVendorCost() {
                const totalPaymentInput = document.getElementById('total_customer_payment');
                const amountHiddenInput = document.getElementById('amount');
                const totalPayment = parseForCalculation(totalPaymentInput.value);
                if (totalPayment <= 0) {
                    amountHiddenInput.value = 0.00;
                    return;
                }
                amountHiddenInput.value = parseFloat((totalPayment * 1).toFixed(2));
            }

            // Items & Specs
            function filterSpecs(itemDropdown) {
                const selectedItemId = itemDropdown.value;
                const row = itemDropdown.closest('tr');
                const specDropdown = row.querySelector('.spec-select');
                specDropdown.innerHTML = '';
                if (!selectedItemId) return;
                const selectedItemData = itemSpecsMap.find(item => item.id == selectedItemId);
                if (selectedItemData?.item_specs) {
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
                newRow.innerHTML = newRow.innerHTML.replace(/_INDEX_/g, itemIndex);
                document.getElementById('invoice-items-body').appendChild(newRow);

                newRow.querySelectorAll('input, select').forEach(input => {
                    input.removeAttribute('disabled');
                    if (input.type === 'text') {
                        if (input.classList.contains('unit-price-input') || input.classList.contains(
                                'subtotal-input')) {
                            input.value = formatForInput(0.00, false);
                        } else if (input.classList.contains('quantity-input')) {
                            input.value = 1;
                        }
                    } else if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    }
                    if (input.classList.contains('subtotal-input')) {
                        setSubtotalInputState(input, isSubtotalOverridden);
                    }
                });

                newRow.querySelector('.spec-select').innerHTML = '';
                updateSubtotal(newRow);
                filterSpecs(newRow.querySelector('.item-select'));
                itemIndex++;

                initializeSelect2IfAvailable(newRow.querySelector('.spec-select'));
            }

            function removeItem(button) {
                button.closest('tr').remove();
                calculateVendorCost();
            }

            // AJAX
            function quickCreateItem(event) {
                event.preventDefault();
                const form = document.getElementById('quick-item-form');
                const formData = new FormData(form);
                fetch('{{ route('items.quickStore') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            itemSpecsMap.push(data.item);
                            const newOption = new Option(data.item.item_name, data.item.id);
                            document.querySelectorAll('.item-select').forEach(select => {
                                select.add(newOption.cloneNode(true));
                            });
                            const specModalSelect = document.getElementById('spec_for_item_id');
                            if (specModalSelect) {
                                specModalSelect.add(newOption.cloneNode(true));
                            }
                            hideItemModal();
                            form.reset();
                            showSuccessToast('Item created successfully');
                        }
                    })
                    .catch(() => {
                        alert('Failed to create item. Please try again.');
                    });
            }

            function addSpecInput() {
                const template = document.getElementById('spec-input-template');
                const container = document.getElementById('spec-inputs-container');
                const newSpecInput = template.cloneNode(true);
                newSpecInput.removeAttribute('id');
                newSpecInput.style.display = 'flex';
                newSpecInput.querySelector('input').value = '';
                container.appendChild(newSpecInput);
                container.scrollTop = container.scrollHeight;
            }

            function removeSpecInput(button) {
                const container = document.getElementById('spec-inputs-container');
                const inputDiv = button.closest('.flex');
                if (container.querySelectorAll('.flex:not([style*="none"])').length > 1) {
                    inputDiv.remove();
                }
            }

            function quickCreateSpec(event) {
                event.preventDefault();
                const form = document.getElementById('quick-spec-form');
                const formData = new FormData();
                const itemId = document.getElementById('spec_for_item_id').value;
                if (!itemId) return;

                formData.append('item_id', itemId);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const descriptionInputs = form.querySelectorAll('input[name="descriptions[]"]');
                let validSpecCount = 0;
                descriptionInputs.forEach(input => {
                    const value = input.value.trim();
                    if (value !== '') {
                        formData.append('descriptions[]', value);
                        validSpecCount++;
                    }
                });
                if (validSpecCount === 0) return;

                fetch('{{ route('item-specs.quickStore') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.ok ? response.json() : response.json().then(err => Promise.reject(err)))
                    .then(data => {
                        if (data.success) {
                            const itemId = document.getElementById('spec_for_item_id').value;
                            const itemToUpdate = itemSpecsMap.find(item => item.id == itemId);
                            const newSpecs = Array.isArray(data.specs) ? data.specs : [];
                            if (itemToUpdate && newSpecs.length > 0) {
                                if (!itemToUpdate.item_specs) itemToUpdate.item_specs = [];
                                newSpecs.forEach(spec => itemToUpdate.item_specs.push(spec));
                                document.querySelectorAll('.item-select').forEach(select => {
                                    if (select.value == itemId) filterSpecs(select);
                                });
                            }
                            hideSpecModal();
                            form.reset();
                            showSuccessToast('Specifications saved successfully');
                            document.getElementById('spec-inputs-container').innerHTML = document.getElementById(
                                'spec-input-template').outerHTML;
                        }
                    })
                    .catch(() => {
                        alert('Failed to create spec. Please try again.');
                    });
            }

            // Initialize
            document.addEventListener('DOMContentLoaded', () => {
                const totalPaymentInput = document.getElementById('total_customer_payment');
                totalPaymentInput.addEventListener('blur', e => {
                    e.target.value = formatForInput(parseForCalculation(e.target.value), false);
                });
                totalPaymentInput.value = formatForInput(parseForCalculation(totalPaymentInput.value || 0), false);

                document.getElementById('invoice-items-body').addEventListener('blur', e => {
                    if (e.target.classList.contains('subtotal-input')) {
                        e.target.value = formatForInput(parseForCalculation(e.target.value), false);
                        calculateVendorCost();
                    }
                }, true);

                calculateVendorCost();

                function restoreOldItems() {
                    const oldItems = @json(old('items', []));
                    const tbody = document.getElementById('invoice-items-body');
                    const initialRows = tbody.querySelectorAll('tr:not(#item-row-template)').length;

                    tbody.querySelectorAll('tr:not(#item-row-template)').forEach(tr => tr.remove());

                    let itemsArray = [];
                    if (Array.isArray(oldItems)) {
                        itemsArray = oldItems;
                    } else if (oldItems && typeof oldItems === 'object') {
                        itemsArray = Object.entries(oldItems)
                            .filter(([key, item]) => {
                                // Skip template placeholder and invalid items
                                return key !== '_INDEX_' &&
                                    item &&
                                    typeof item === 'object' &&
                                    item.item_id != null; // only real items have item_id
                            })
                            .map(([key, item]) => item);
                    }

                    if (itemsArray.length > 0) {
                        itemsArray.forEach((item, idx) => {
                            addItem();
                            const row = tbody.querySelector('tr:last-child');
                            const {
                                itemSelect,
                                quantityInput,
                                subtotalInput,
                                specSelect
                            } = {
                                itemSelect: row.querySelector('.item-select'),
                                quantityInput: row.querySelector('.quantity-input'),
                                subtotalInput: row.querySelector('.subtotal-input'),
                                specSelect: row.querySelector('.spec-select')
                            };

                            if (item.item_id != null) itemSelect.value = item.item_id;
                            if (item.quantity != null) quantityInput.value = item.quantity;
                            if (item.subtotal != null) subtotalInput.value = formatForInput(item.subtotal,
                                false);

                            specSelect.innerHTML = '';
                            const allSpecsForItem = itemSpecsMap.find(i => i.id == item.item_id)?.item_specs ||
                                [];
                            allSpecsForItem.forEach(spec => {
                                const opt = document.createElement('option');
                                opt.value = spec.id;
                                opt.textContent = spec.item_description;
                                if (Array.isArray(item.item_spec_ids)) {
                                    if (item.item_spec_ids.includes(String(spec.id)) || item
                                        .item_spec_ids.includes(Number(spec.id))) {
                                        opt.selected = true;
                                    }
                                }
                                specSelect.appendChild(opt);
                            });

                            updateSubtotal(row);
                            initializeSelect2IfAvailable(row.querySelector('.spec-select'));
                        });
                    }

                    const finalRows = tbody.querySelectorAll('tr:not(#item-row-template)').length;
                    if (finalRows === 0) {
                        addItem();
                        const newFinalRows = tbody.querySelectorAll('tr:not(#item-row-template)').length;
                    }
                }

                // Restore old input
                restoreOldItems();

                // Based-on-order logic
                const checkbox = document.getElementById('based_on_order_checkbox');
                const dropdown = document.getElementById('based_on_order_select');
                if (!checkbox || !dropdown) return;

                checkbox.addEventListener('change', () => {
                    dropdown.classList.toggle('hidden', !checkbox.checked);
                    if (!checkbox.checked) dropdown.value = '';
                });

                dropdown.addEventListener('change', async () => {
                    const orderId = dropdown.value;
                    if (!orderId) return;
                    try {
                        const res = await fetch(`/orders/${orderId}/template`);
                        const data = await res.json();
                        if (!data?.id) return;

                        alert(`Order ${data.ord_number} loaded!`);

                        document.getElementById('client_id').value = data.client_id ?? '';
                        document.getElementById('department_id').value = data.department_id ?? '';
                        document.getElementById('cur').value = data.cur ?? 'IDR';
                        document.getElementById('project_name').value = data.project_name ?? '';
                        document.getElementById('ord_number').value = data.ord_number ?? '';
                        document.getElementById('ord_date').value = new Date().toISOString().split('T')[0];
                        document.getElementById('po_number').value = data.po_number ?
                            `${data.po_number}-COPY` : '';
                        document.getElementById('po_date').value = new Date().toISOString().split('T')[0];
                        document.getElementById('remark').value = data.remark ?? '';

                        const pph23Checkbox = document.querySelector('input[name="is_pph23_prepaid"]');
                        if (pph23Checkbox) {
                            pph23Checkbox.checked = Boolean(data.is_pph23_prepaid);
                        }

                        const tbody = document.getElementById('invoice-items-body');
                        tbody.querySelectorAll('tr:not(#item-row-template)').forEach(tr => tr.remove());

                        if (Array.isArray(data.items)) {
                            data.items.forEach(item => {
                                addItem();
                                const row = tbody.querySelector('tr:last-child');
                                const itemSelect = row.querySelector('.item-select');
                                const qtyInput = row.querySelector('.quantity-input');
                                const subInput = row.querySelector('.subtotal-input');
                                const specSelect = row.querySelector('.spec-select');

                                itemSelect.value = item.item_id;
                                qtyInput.value = item.quantity;
                                subInput.value = formatForInput(item.subtotal, false);

                                const allSpecsForItem = itemSpecsMap.find(i => i.id == item.item_id)
                                    ?.item_specs || [];
                                specSelect.innerHTML = '';
                                allSpecsForItem.forEach(spec => {
                                    const opt = document.createElement('option');
                                    opt.value = spec.id;
                                    opt.textContent = spec.item_description;
                                    const isSelected = item.specs?.some(s => s.id == spec
                                        .id) ?? false;
                                    if (isSelected) opt.selected = true;
                                    specSelect.appendChild(opt);
                                });

                                updateSubtotal(row);
                                initializeSelect2IfAvailable(specSelect);
                            });
                        }
                        calculateVendorCost();
                    } catch (err) {
                        alert('Failed to load template.');
                    }
                });
            });
        </script>
    </x-pages.form>

    {{-- Quick Add Item Modal --}}
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
                        onclick="hideItemModal()">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Save New Item') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Add Specification Modal --}}
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
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4 border-t pt-4">
                    <x-input-label value="{{ __('Specification Details') }}" class="mb-2" />
                    <div id="spec-inputs-container" class="space-y-2 max-h-60 overflow-y-auto pr-2">
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
                        onclick="hideSpecModal()">{{ __('Cancel') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Save New Specs') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Success Toast --}}
    <div id="success-toast"
        class="fixed top-5 right-5 z-50
                transform translate-x-full opacity-0
                transition-all duration-300 ease-out
                bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 13l4 4L19 7"/>
        </svg>
        <span id="success-toast-message">Saved successfully</span>
    </div>
</x-app-layout>
