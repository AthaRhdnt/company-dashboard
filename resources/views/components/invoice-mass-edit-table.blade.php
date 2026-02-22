@props([
    'title',
    'invoices',
    'route', // mass update route
    'invoiceType', // 'outgoing' or 'incoming' (replaces themeColor)
    'exportRoute',
])

@php
    // Validate and normalize invoiceType
    if (!in_array($invoiceType, ['outgoing', 'incoming'])) {
        throw new InvalidArgumentException('invoiceType must be "outgoing" or "incoming"');
    }

    // === CONFIG: Decoupled business logic from UI ===
    $config = [
        'outgoing' => [
            'primaryColor' => 'indigo',
            'headerBgClass' => 'bg-indigo-50',
            'subjectHeader' => 'Client Name',
            'subjectProperty' => 'client.client_name',
            'date1Header' => 'Invoice Date',
            'date1Property' => 'inv_date',
            'date2Header' => 'Due Date',
            'date2Property' => 'due_date',
            'incPmtDateHeader' => 'INC DATE',
            'saveButtonColor' => 'green',
            'showPdfActions' => true,
            'showNewButton' => false,
            'newButtonRoute' => null,
            'editRouteName' => 'outgoing-invoices.edit',
            'showRouteName' => 'outgoing-invoices.show',
            'generateSingleRoute' => 'outgoing-invoices.generate-single',
            'generateDocumentsRoute' => 'outgoing-invoices.generate-documents',
            'amountColorClass' => 'text-green-600',
        ],
        'incoming' => [
            'primaryColor' => 'red',
            'headerBgClass' => 'bg-red-50',
            'subjectHeader' => 'Vendor Name',
            'subjectProperty' => 'vendor.vendor_name',
            'date1Header' => 'INV RCVD Date',
            'date1Property' => 'inv_received_date',
            'date2Header' => 'FP Date',
            'date2Property' => 'fp_date',
            'incPmtDateHeader' => 'PMT DATE',
            'saveButtonColor' => 'green', // still green for save
            'showPdfActions' => false,
            'showNewButton' => true,
            'newButtonRoute' => 'incoming-invoices.create',
            'editRouteName' => 'incoming-invoices.edit',
            'showRouteName' => 'incoming-invoices.show',
            'generateSingleRoute' => null,
            'generateDocumentsRoute' => null,
            'amountColorClass' => 'text-red-600',
        ],
    ][$invoiceType];

    // Extract config for easy access
    extract($config);

    // Storage key based on type (not color!)
    $storageKey = $invoiceType === 'outgoing' ? 'selected_outgoing_invoices' : 'selected_incoming_invoices';

    // Pagination & numbering
    $isPaginator = $invoices instanceof \Illuminate\Contracts\Pagination\Paginator;
    $startNumber = $isPaginator ? ($invoices->currentPage() - 1) * $invoices->perPage() + 1 : 1;

    // IDs as strings (for Alpine checkbox binding)
    $currentPageIds = $invoices->pluck('id')->map(fn($id) => (string) $id);

    // Input classes (JIT-safe: all variants pre-written)
    // We define them statically since only two modes exist
    $editInputClasses = 'w-full text-sm rounded-md shadow-sm p-1 transition duration-300 focus:ring focus:ring-opacity-50 bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400';
    $defaultInputClasses = 'w-full text-sm rounded-md shadow-sm p-1 transition duration-300 focus:ring focus:ring-opacity-50 border-gray-300 focus:border-' . $primaryColor . '-300 focus:ring-' . $primaryColor . '-200';
@endphp

<div class="p-6">
    <div class="bg-white p-6 rounded-xl shadow-2xl">
        <div x-data="{
            isEditing: {{ count($errors) > 0 ? 'true' : 'false' }},
            currentPageIds: {{ $currentPageIds->toJson() }},
            selectedInvoices: [],
            selectAll: false,
            storageKey: {{ json_encode($storageKey) }},

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

            togglePageSelection() {
                const pageIds = this.currentPageIds;
                if (this.selectAll) {
                    this.selectedInvoices = this.selectedInvoices.filter(id => !pageIds.includes(id));
                } else {
                    const merged = new Set([...this.selectedInvoices, ...pageIds]);
                    this.selectedInvoices = Array.from(merged);
                }
            },

            updateSelectAllState() {
                const pageIds = this.currentPageIds;
                if (pageIds.length === 0) {
                    this.selectAll = false;
                    return;
                }
                this.selectAll = pageIds.every(id => this.selectedInvoices.includes(id));
            }
        }"
        x-init="loadSelected(); $nextTick(() => updateSelectAllState());"
        $watch="selectedInvoices, () => { updateSelectAllState(); saveSelected(); }"
        class="space-y-6"
        >

            {{-- Top Controls and Toggle/Save Button --}}
            <div
                class="sticky top-0 z-10 bg-white/90 backdrop-blur-md p-4 -mt-4 mb-4 rounded-xl border border-gray-200 shadow-sm transition-all duration-300 ease-in-out flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0">
                <h1 class="text-3xl font-bold text-gray-900 mb-3 md:mb-0">{{ $title }}</h1>

                <div class="flex space-x-3 items-center">
                    <a x-show="!isEditing" href="{{ route($exportRoute) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-green-600 hover:bg-green-700 transition duration-300 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Convert to Excel
                    </a>

                    {{-- New Invoice Button (Incoming only) --}}
                    @if ($showNewButton)
                        <a x-show="!isEditing" href="{{ route($newButtonRoute) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-rose-500 hover:bg-rose-700 transition duration-300 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500">
                            Record New Invoice
                        </a>
                    @endif

                    <button x-show="isEditing" type="button" @click="$refs.massUpdateForm.submit()"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-300">
                        <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 0 1 1-1h11.586a1 1 0 0 1 .707.293l2.414 2.414a1 1 0 0 1 .293.707V19a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Z"/>
                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M8 4h8v4H8V4Zm7 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        Save
                    </button>

                    {{-- Edit Toggle --}}
                    <button type="button" @click="isEditing = !isEditing"
                        x-bind:class="isEditing
                            ? 'bg-red-500 hover:bg-red-600 focus:ring-red-500'
                            : 'bg-{{ $primaryColor }}-600 hover:bg-{{ $primaryColor }}-700 focus:ring-{{ $primaryColor }}-500'"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-300 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                        <span x-text="isEditing ? 'Exit' : 'Edit Mode'"></span>
                    </button>
                </div>
            </div>

            {{-- PDF Generation (Outgoing Only) --}}
            @if ($showPdfActions)
                <div class="flex justify-between items-center py-3 {{ $headerBgClass }} rounded-lg px-4 border border-{{ $primaryColor }}-200"
                    x-show="selectedInvoices.length > 0" x-transition.opacity>
                    <button type="button" @click="clearSelection()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-100 transition duration-300 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Clear (<span x-text="selectedInvoices.length"></span>)
                    </button>

                    <div class="text-sm font-medium text-gray-700"
                        x-text="'Ready to generate documents for ' + selectedInvoices.length + ' selected invoice(s).'">
                    </div>

                    {{-- Single PDF --}}
                    <button type="button" x-show="selectedInvoices.length === 1"
                        @click="window.location.href = '{{ route($generateSingleRoute, ['outgoingInvoice' => ':id_placeholder']) }}'.replace(':id_placeholder', selectedInvoices[0])"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-blue-600 hover:bg-blue-700 transition duration-300 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Download Document (PDF)
                    </button>

                    {{-- Bulk PDF --}}
                    <form x-show="selectedInvoices.length > 1" method="POST" action="{{ route($generateDocumentsRoute) }}">
                        @csrf
                        <template x-for="id in selectedInvoices" :key="'doc-id-' + id">
                            <input type="hidden" name="invoice_ids[]" :value="id">
                        </template>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-green-600 hover:bg-green-700 transition duration-300 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Generate Documents (PDF)
                        </button>
                    </form>
                </div>
            @endif

            {{-- Flash Messages --}}
            @if (session('success') || session('error') || session('info'))
                @php
                    $message = session('success') ?? session('error') ?? session('info');
                    $type = session('success') ? 'success' : (session('error') ? 'error' : 'info');
                    [$bgClass, $ringColor] = match($type) {
                        'success' => ['bg-green-100 text-green-700 border-green-200', 'ring-green-600'],
                        'error'   => ['bg-red-100 text-red-700 border-red-200', 'ring-red-600'],
                        default   => ['bg-blue-100 text-blue-700 border-blue-200', 'ring-blue-600'],
                    };
                @endphp
                <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                    class="p-4 {{ $bgClass }} rounded-lg border flex justify-between items-start shadow-sm">
                    <span>{{ $message }}</span>
                    <button type="button" @click="showMessage = false"
                        class="ml-4 -mt-1 p-1 rounded-full text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 {{ $ringColor }}/50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex justify-between">
                        <strong class="text-red-800">Please fix the following errors:</strong>
                        <button @click="show = false" class="ml-4 -mt-1 p-1 rounded-full text-red-600 hover:text-red-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <ul class="mt-2 text-red-700 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form for Mass Update --}}
            <form x-ref="massUpdateForm" id="massUpdateForm" x-bind:action="'{{ route($route) }}'" method="POST" x-cloak>
                @csrf
                @method('PUT')

                <div class="shadow-xl rounded-lg overflow-hidden border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="{{ $headerBgClass }} sticky top-0">
                                <tr>
                                    {{-- MASTER CHECKBOX --}}
                                    <th
                                        class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-10">
                                        @if ($invoiceType === 'outgoing')
                                            <input type="checkbox" x-bind:checked="selectAll" @click="togglePageSelection()"
                                                class="rounded text-{{ $primaryColor }}-600 shadow-sm focus:border-{{ $primaryColor }}-300 focus:ring focus:ring-{{ $primaryColor }}-200 focus:ring-opacity-50 cursor-pointer">
                                        @endif
                                    </th>
                                    <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-10">NO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-48">{{ $subjectHeader }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-20">D-CODE</th>
                                    <th class="px-2 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">INVOICE NO</th>
                                    <th class="px-2 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">{{ $date1Header }}</th>
                                    <th class="px-2 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">{{ $date2Header }}</th>
                                    <th class="px-2 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">FP S/N</th>

                                    @if ($invoiceType === 'outgoing')
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">PO NO</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">PO DATE</th>
                                    @endif

                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">MCN ORDER NO</th>
                                    <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-16">CUR</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider min-w-32">AMOUNT (IDR)</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">{{ $incPmtDateHeader }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-48">REMARKS</th>
                                    <th class="relative px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-24">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($invoices as $index => $invoice)
                                    @php
                                        $id = (string) $invoice->id;
                                        $subjectName = data_get($invoice, $subjectProperty) ?? 'N/A';
                                        $dCode = data_get($invoice, 'department.department_code') ?? 'N/A';
                                        $paymentDateClass = $invoice->payment_date ? 'text-green-500' : 'text-red-500';

                                        // Helper to format date for HTML input[type=date]
                                        $getDateInput = fn($prop) => $invoice->$prop
                                            ? \Carbon\Carbon::parse($invoice->$prop)->format('Y-m-d')
                                            : null;
                                    @endphp
                                    <tr class="hover:bg-gray-200 transition duration-75">
                                        {{-- 1. CHECKBOX CELL --}}
                                        <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            @if ($invoiceType === 'outgoing')
                                                <input type="checkbox" value="{{ $id }}" x-model="selectedInvoices"
                                                    class="rounded text-{{ $primaryColor }}-600 shadow-sm focus:border-{{ $primaryColor }}-300 focus:ring focus:ring-{{ $primaryColor }}-200 focus:ring-opacity-50 cursor-pointer">
                                            @endif
                                        </td>
                                        {{-- 2. NO --}}
                                        <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            {{ $startNumber + $loop->index }}
                                        </td>
                                        {{-- 3. SUBJECT NAME --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            {{ $subjectName }}
                                        </td>
                                        {{-- 4. D-CODE --}}
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $dCode }}
                                        </td>

                                        {{-- Hidden ID field for mass update (Always include this) --}}
                                        <td class="hidden">
                                            <input x-show="isEditing" type="hidden" name="invoices[{{ $id }}][id]" value="{{ $id }}">
                                        </td>

                                        {{-- 5. INVOICE NO (Editable) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-{{ $primaryColor }}-600">
                                            <span x-show="!isEditing">{{ $invoice->inv_number ?? 'PENDING' }}</span>
                                            <input x-show="isEditing" type="text"
                                                name="invoices[{{ $id }}][inv_number]"
                                                value="{{ old('invoices.' . $id . '.inv_number', $invoice->inv_number) }}"
                                                placeholder="INV Number"
                                                x-bind:class="isEditing ? '{{ $editInputClasses }}' : '{{ $defaultInputClasses }}'">
                                            @if($errors->has("invoices.{$id}.inv_number"))
                                                <div x-data="{ showError: true }" x-show="showError" class="mt-1">
                                                    <p class="text-red-500 text-xs flex items-center gap-1">
                                                        {{ $errors->first("invoices.{$id}.inv_number") }}
                                                        <button type="button" @click="showError = false" class="text-red-600 hover:text-red-800 focus:outline-none font-bold">&times;</button>
                                                    </p>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 6. DATE 1 (Editable) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                            <span x-show="!isEditing">{{ data_get($invoice, $date1Property . '_formatted') }}</span>
                                            <input x-show="isEditing" type="date"
                                                name="invoices[{{ $id }}][{{ $date1Property }}]"
                                                value="{{ old('invoices.' . $id . '.' . $date1Property, $getDateInput($date1Property)) }}"
                                                x-bind:class="isEditing ? '{{ $editInputClasses }}' : '{{ $defaultInputClasses }}'">
                                            @if($errors->has("invoices.{$id}.{$date1Property}"))
                                                <div x-data="{ showError: true }" x-show="showError" class="mt-1">
                                                    <p class="text-red-500 text-xs flex items-center gap-1">
                                                        {{ $errors->first("invoices.{$id}.{$date1Property}") }}
                                                        <button type="button" @click="showError = false" class="text-red-600 hover:text-red-800 focus:outline-none font-bold">&times;</button>
                                                    </p>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 7. DATE 2 (Editable) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                            <span x-show="!isEditing">{{ data_get($invoice, $date2Property . '_formatted') }}</span>
                                            <input x-show="isEditing" type="date"
                                                name="invoices[{{ $id }}][{{ $date2Property }}]"
                                                value="{{ old('invoices.' . $id . '.' . $date2Property, $getDateInput($date2Property)) }}"
                                                x-bind:class="isEditing ? '{{ $editInputClasses }}' : '{{ $defaultInputClasses }}'">
                                            @if($errors->has("invoices.{$id}.{$date2Property}"))
                                                <div x-data="{ showError: true }" x-show="showError" class="mt-1">
                                                    <p class="text-red-500 text-xs flex items-center gap-1">
                                                        {{ $errors->first("invoices.{$id}.{$date2Property}") }}
                                                        <button type="button" @click="showError = false" class="text-red-600 hover:text-red-800 focus:outline-none font-bold">&times;</button>
                                                    </p>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 8. FP S/N (Editable) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-700">
                                            <span 
                                                x-show="!isEditing"
                                                @class([
                                                    'inline-block',
                                                    ($invoiceType === 'outgoing' && (empty($invoice->fp_number) || in_array(strtolower(trim($invoice->fp_number ?? '')), ['n/a', 'na', ''])))
                                                        ? 'rounded-md bg-red-50 text-red-700 border border-red-300 hover:bg-red-100 min-w-36 text-center' 
                                                        : ''
                                                ])
                                            >
                                                {{ $invoice->fp_number ?? 'N/A' }}
                                            </span>

                                            {{-- Edit mode input --}}
                                            <input x-show="isEditing" type="text"
                                                name="invoices[{{ $id }}][fp_number]"
                                                value="{{ old('invoices.' . $id . '.fp_number', $invoice->fp_number) }}"
                                                placeholder="FP S/N"
                                                x-bind:class="isEditing ? '{{ $editInputClasses }}' : '{{ $defaultInputClasses }}'">

                                            @if($errors->has("invoices.{$id}.fp_number"))
                                                <div x-data="{ showError: true }" x-show="showError" class="mt-1">
                                                    <p class="text-red-500 text-xs flex items-center gap-1">
                                                        {{ $errors->first("invoices.{$id}.fp_number") }}
                                                        <button type="button" @click="showError = false" class="text-red-600 hover:text-red-800 focus:outline-none font-bold">&times;</button>
                                                    </p>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Static Fields (PO NO/DATE for Outgoing) --}}
                                        @if ($invoiceType === 'outgoing')
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $invoice->order?->purchaseOrder?->po_number ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $invoice->po_date_formatted ?? 'N/A' }}
                                            </td>
                                        @endif

                                        {{-- 9. MCN ORDER NO --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-{{ $primaryColor }}-700">
                                            {{ $invoice->order?->ord_number ?? ($invoice->usageDepartment?->department_code ?? 'N/A') }}
                                        </td>
                                        {{-- 10. CUR --}}
                                        <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                            {{ $invoice->cur ?? 'IDR' }}
                                        </td>
                                        {{-- 11. AMOUNT (IDR) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $amountColorClass }}">
                                            {{ $invoice->formatted_amount }}
                                        </td>
                                        {{-- 12. PMT DATE --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $paymentDateClass }}">
                                            {{ $invoice->payment_date_formatted ?? '—' }}
                                        </td>
                                        {{-- 13. REMARKS --}}
                                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-500 max-w-xs truncate">
                                            {{ $invoice->remark ?? '' }}
                                        </td>

                                        {{-- 14. ACTIONS --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div x-show="!isEditing">
                                                <a href="{{ route($showRouteName, $invoice) }}"
                                                    class="text-{{ $primaryColor }}-600 hover:text-{{ $primaryColor }}-900 transition duration-300 ease-in-out font-bold">
                                                    Show Details
                                                </a>
                                            </div>
                                            <div x-show="isEditing" class="text-gray-400">
                                                In Mass Edit
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="px-4 py-6 text-center text-sm text-gray-500">
                                            No data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
