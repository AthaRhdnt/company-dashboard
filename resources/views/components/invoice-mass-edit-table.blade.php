@props([
    'title',
    'invoices',
    'route',
    'themeColor',            // e.g., 'indigo' or 'red'
    'headerBgClass',         // e.g., 'bg-indigo-50' or 'bg-red-50'
    'subjectHeader',         // e.g., 'CHARGED TO' or 'SUPPLIER / SUBCON'
    'subjectProperty',       // e.g., 'client.client_name' or 'vendor.vendor_name'
    'date1Header',           // e.g., 'INV DATE' or 'INV RCVD DATE'
    'date1Property',         // e.g., 'inv_date' or 'inv_received_date'
    'date2Header',           // e.g., 'DUE DATE' or 'INV / FP DATE'
    'date2Property',         // e.g., 'due_date' or 'fp_date'
    'saveButtonColor' => 'green', // Default to green, used for Outgoing
    'exportRoute'
])

@php
    // CRITICAL: Ensure currentPageIds are STRINGS to match checkbox values for x-model
    $currentPageIds = $invoices->pluck('id')->map(fn($id) => (string)$id);
    $storageKey = $themeColor === 'indigo' ? 'selected_outgoing_invoices' : 'selected_incoming_invoices';

    // Tailwind classes for the input fields when in editing mode (DRY principle)
    $editInputClasses = 'w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50 ' .
                        'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400';
    $defaultInputClasses = 'w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50 ' .
                           'border-gray-300 focus:border-' . $themeColor . '-300 focus:ring-' . $themeColor . '-200';

    $isPaginator = $invoices instanceof \Illuminate\Contracts\Pagination\Paginator;

    // FIX: Calculate the starting number of the current page ONCE using the Paginator object
    $startNumber = 1;
    if ($isPaginator) {
        $startNumber = ($invoices->currentPage() - 1) * $invoices->perPage() + 1;
    }

    $incPmtDateHeader = $themeColor === 'indigo' ? 'INC DATE' : 'PMT DATE';
@endphp

<div class="p-6">
    <div class="bg-white p-6 rounded-xl shadow-2xl">

        {{-- ALPINE.JS COMPONENT FOR STATE MANAGEMENT --}}
        <div x-data="{
            isEditing: {{ count($errors) > 0 ? 'true' : 'false' }},
            currentPageIds: {{ $currentPageIds->toJson() }},
            selectedInvoices: [], // Array of selected IDs (strings)
            selectAll: false,
            storageKey: '{{ $storageKey }}',

            loadSelected() {
                const stored = localStorage.getItem(this.storageKey);
                this.selectedInvoices = stored ? JSON.parse(stored).map(String) : [];
            },

            saveSelected() {
                localStorage.setItem(this.storageKey, JSON.stringify(this.selectedInvoices));
            },

            clearSelection() {
                this.selectedInvoices = [];
            },

            // Toggles all checkboxes on the current page
            togglePageSelection() {
                const pageIds = this.currentPageIds;
                let currentSelection = this.selectedInvoices;

                if (this.selectAll) {
                    // Deselect: Remove all current page IDs
                    this.selectedInvoices = currentSelection.filter(id => !pageIds.includes(id));
                } else {
                    // Select: Add all current page IDs (using Set for unique values)
                    const merged = new Set([...currentSelection, ...pageIds]);
                    this.selectedInvoices = Array.from(merged);
                }
            },

            // Updates the header checkbox state
            updateSelectAllState() {
                const pageIds = this.currentPageIds;
                if (pageIds.length === 0) {
                    this.selectAll = false;
                    return;
                }
                this.selectAll = pageIds.every(id => this.selectedInvoices.includes(id));
            }
        }"
        x-init="
            loadSelected();
            $nextTick(() => { updateSelectAllState(); });

            // Watchers: Keep selection in sync and update the header checkbox
            $watch('selectedInvoices', () => {
                updateSelectAllState();
                saveSelected();
            });
        "
        class="space-y-6">

            {{-- Top Controls and Toggle/Save Button --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-4 border-gray-100">
                <h1 class="text-3xl font-extrabold text-gray-900 mb-3 md:mb-0">{{ $title }}</h1>

                <div class="flex space-x-3 items-center">
                    <a href="{{ route($exportRoute) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-green-600 hover:bg-green-700 transition duration-150 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Convert to Excel
                    </a>

                    {{-- CLEAR SELECTION BUTTON --}}
                    <button type="button"
                        @click="clearSelection()"
                        x-show="selectedInvoices.length > 0"
                        x-transition.opacity
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-100 transition duration-150 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Clear Selection (<span x-text="selectedInvoices.length"></span>)
                    </button>

                    {{-- Toggle Edit Mode Button --}}
                    <button type="button"
                        @click="isEditing = !isEditing"
                        x-bind:class="isEditing
                            ? 'bg-red-500 hover:bg-red-600 focus:ring-red-500'
                            : 'bg-{{ $themeColor }}-600 hover:bg-{{ $themeColor }}-700 focus:ring-{{ $themeColor }}-500'"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                        <span x-text="isEditing ? 'Exit Edit Mode' : 'Toggle Edit Mode'"></span>
                    </button>
                </div>
            </div>

            {{-- DOCUMENT GENERATION LOGIC (Only for Outgoing Invoices) --}}
            @if ($themeColor == 'indigo')
                {{-- Main Container, controlled by Alpine's selectedInvoices --}}
                <div class="flex justify-between items-center py-3 bg-{{ $themeColor }}-50/50 rounded-lg px-4 border border-{{ $themeColor }}-200"
                    x-show="selectedInvoices.length > 0" x-transition.opacity>
                    
                    <div class="text-sm font-medium text-gray-700" x-text="'Ready to generate documents for ' + selectedInvoices.length + ' selected invoice(s).'"></div>

                    {{-- SCENARIO 1: ONE SELECTED INVOICE (Triggers direct PDF download) --}}
                    <button type="button"
                        x-show="selectedInvoices.length === 1"
                        {{-- CRITICAL FIX: Use placeholder replacement to correctly build the URL in Alpine --}}
                        @click="window.location.href = '{{ route('outgoing-invoices.generate-single', ['outgoingInvoice' => ':id_placeholder']) }}'.replace(':id_placeholder', selectedInvoices[0])"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Download Single Document (PDF)
                    </button>

                    {{-- SCENARIO 2: MULTIPLE SELECTED INVOICES (Triggers mass generation/redirect) --}}
                    <form x-show="selectedInvoices.length > 1" method="POST" action="{{ route('outgoing-invoices.generate-documents') }}">
                        @csrf
                        {{-- Hidden fields to submit all selected IDs for mass generation --}}
                        <template x-for="id in selectedInvoices" :key="'doc-id-' + id">
                            <input type="hidden" name="invoice_ids[]" :value="id">
                        </template>
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-green-600 hover:bg-green-700 transition duration-150 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Generate Batch Documents (POST)
                        </button>
                    </form>

                </div>
            @endif

            {{-- Flash Messages (Consolidated Logic) --}}
            @if (session('success') || session('error'))
                @php
                    $isError = session('error') ? true : false;
                    $message = session('success') ?? session('error');
                    $bgClass = $isError ? 'bg-red-100 text-red-700 border-red-200' : 'bg-green-100 text-green-700 border-green-200';
                    $ringColor = $isError ? 'ring-red-600' : 'ring-green-600';
                @endphp
                <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                    class="p-4 {{ $bgClass }} rounded-lg border flex justify-between items-start shadow-sm">
                    <span>{{ $message }}</span>
                    <button type="button" @click="showMessage = false"
                        class="ml-4 -mt-1 p-1 rounded-full text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 {{ $ringColor }}/50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif

            {{-- Form for Mass Update --}}
            <form x-bind:action="'{{ route($route) }}'" method="POST" x-cloak>
                @csrf
                @method('PUT')

                <div class="shadow-xl rounded-lg overflow-hidden border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="{{ $headerBgClass }} sticky top-0">
                                <tr>
                                    {{-- MASTER CHECKBOX --}}
                                    <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-10">
                                        @if ($themeColor == 'indigo')
                                            <input type="checkbox"
                                                x-bind:checked="selectAll"
                                                @click="togglePageSelection()"
                                                class="rounded text-{{ $themeColor }}-600 shadow-sm focus:border-{{ $themeColor }}-300 focus:ring focus:ring-{{ $themeColor }}-200 focus:ring-opacity-50 cursor-pointer">
                                        @endif
                                    </th>
                                    <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-10">NO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-48">{{ $subjectHeader }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-20">D-CODE</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">INVOICE NO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">{{ $date1Header }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">{{ $date2Header }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">FP S/N</th>
                                    @if($themeColor == 'indigo')
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">PO NO</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">PO DATE</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">MCN ORDER NO</th>
                                    <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-16">CUR</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider min-w-32">AMOUNT (IDR)</th>
                                    {{-- <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">INC/PMT DATE</th> --}}
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">{{ $incPmtDateHeader }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-48">REMARKS</th>
                                    <th class="relative px-6 py-3 min-w-24">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($invoices as $index => $invoice)
                                    @php
                                        $id = (string) $invoice->id;
                                        $subjectName = data_get($invoice, $subjectProperty) ?? 'N/A';
                                        $dCode = data_get($invoice, 'order.department.department_code') ?? 'N/A';
                                        $paymentDateClass = $invoice->payment_date ? 'text-green-500' : 'text-red-500';

                                        // Helper to format date for HTML input[type=date]
                                        $getDateInput = fn($prop) => $invoice->$prop ? \Carbon\Carbon::parse($invoice->$prop)->format('Y-m-d') : null;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition duration-75">
                                        {{-- 1. CHECKBOX CELL --}}
                                        <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            @if ($themeColor == 'indigo')
                                                <input type="checkbox"
                                                    value="{{ $id }}"
                                                    x-model="selectedInvoices"
                                                    class="rounded text-{{ $themeColor }}-600 shadow-sm focus:border-{{ $themeColor }}-300 focus:ring focus:ring-{{ $themeColor }}-200 focus:ring-opacity-50 cursor-pointer">
                                            @endif
                                        </td>
                                        {{-- 2. NO --}}
                                        {{-- <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ $loop->iteration }}</td> --}}
                                        <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ $startNumber + $index }}</td>
                                        {{-- 3. SUBJECT NAME --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $subjectName }}</td>
                                        {{-- 4. D-CODE --}}
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">{{ $dCode }}</td>

                                        {{-- Hidden ID field for mass update (Always include this) --}}
                                        <input x-show="isEditing" type="hidden" name="invoices[{{ $id }}][id]" value="{{ $id }}">

                                        {{-- 5. INVOICE NO (Editable) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-{{ $themeColor }}-600">
                                            <span x-show="!isEditing">{{ $invoice->inv_number ?? 'PENDING' }}</span>
                                            <input x-show="isEditing" type="text"
                                                name="invoices[{{ $id }}][inv_number]"
                                                value="{{ old('invoices.' . $id . '.inv_number', $invoice->inv_number) }}"
                                                placeholder="INV Number"
                                                :required="isEditing"
                                                x-bind:class="isEditing ? '{{ $editInputClasses }}' : '{{ $defaultInputClasses }}'">
                                            @error('invoices.' . $id . '.inv_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </td>

                                        {{-- 6. DATE 1 (Editable) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                            <span x-show="!isEditing">{{ data_get($invoice, $date1Property . '_formatted') }}</span>
                                            <input x-show="isEditing" type="date"
                                                name="invoices[{{ $id }}][{{ $date1Property }}]"
                                                value="{{ old('invoices.' . $id . '.' . $date1Property, $getDateInput($date1Property)) }}"
                                                :required="isEditing"
                                                x-bind:class="isEditing ? '{{ $editInputClasses }}' : '{{ $defaultInputClasses }}'">
                                            @error('invoices.' . $id . '.' . $date1Property) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </td>

                                        {{-- 7. DATE 2 (Editable) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                            <span x-show="!isEditing">{{ data_get($invoice, $date2Property . '_formatted') }}</span>
                                            <input x-show="isEditing" type="date"
                                                name="invoices[{{ $id }}][{{ $date2Property }}]"
                                                value="{{ old('invoices.' . $id . '.' . $date2Property, $getDateInput($date2Property)) }}"
                                                x-bind:class="isEditing ? '{{ $editInputClasses }}' : '{{ $defaultInputClasses }}'">
                                            @error('invoices.' . $id . '.' . $date2Property) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </td>

                                        {{-- 8. FP S/N (Editable) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-700">
                                            <span x-show="!isEditing">{{ $invoice->fp_number ?? 'N/A' }}</span>
                                            <input x-show="isEditing" type="text"
                                                name="invoices[{{ $id }}][fp_number]"
                                                value="{{ old('invoices.' . $id . '.fp_number', $invoice->fp_number) }}"
                                                placeholder="FP S/N"
                                                x-bind:class="isEditing ? '{{ $editInputClasses }}' : '{{ $defaultInputClasses }}'">
                                            @error('invoices.' . $id . '.fp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </td>

                                        {{-- Static Fields (PO NO/DATE for Outgoing) --}}
                                        @if($themeColor == 'indigo')
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $invoice->order->purchaseOrder->po_number ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->po_date_formatted }}</td>
                                        @endif

                                        {{-- 9. MCN ORDER NO --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-{{ $themeColor }}-700">{{ $invoice->order->ord_number ?? 'N/A' }}</td>
                                        {{-- 10. CUR --}}
                                        <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-700">{{ $invoice->cur ?? 'IDR' }}</td>
                                        {{-- 11. AMOUNT (IDR) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-{{ $themeColor == 'indigo' ? 'green' : 'red' }}-600">{{ $invoice->formatted_amount }}</td>
                                        {{-- 12. PMT DATE --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $paymentDateClass }}">{{ $invoice->payment_date_formatted }}</td>
                                        {{-- 13. REMARKS --}}
                                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-500 max-w-xs truncate">{{ $invoice->remark ?? '' }}</td>

                                        {{-- 14. ACTIONS --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div x-show="!isEditing">
                                                <a href="{{ route($themeColor == 'indigo' ? 'outgoing-invoices.show' : 'incoming-invoices.show', $invoice) }}"
                                                    class="text-{{ $themeColor }}-600 hover:text-{{ $themeColor }}-900 transition duration-150 ease-in-out font-bold">
                                                    View Document
                                                </a>
                                            </div>
                                            <div x-show="isEditing">
                                                <span class="text-gray-400">In Mass Edit</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Save Button (Only visible in Edit Mode) --}}
                <div x-show="isEditing" class="mt-6 flex justify-end" x-cloak>
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-{{ $saveButtonColor }}-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-md hover:bg-{{ $saveButtonColor }}-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $saveButtonColor }}-500 transition duration-150 ease-in-out">
                        Save All Finalized Invoices
                    </button>
                </div>

            </form>
        </div>

        @if ($isPaginator)
            <div class="mt-6">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
