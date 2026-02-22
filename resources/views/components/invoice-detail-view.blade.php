@props([
    'invoice',
    'title',
    'invoiceType', // 'outgoing' or 'incoming'
])

@php
    use Carbon\Carbon;

    if (!in_array($invoiceType, ['outgoing', 'incoming'])) {
        throw new InvalidArgumentException('invoiceType must be "outgoing" or "incoming"');
    }

    // === CONFIGURATION BASED ON TYPE ===
    $config = [
        'outgoing' => [
            'themeColor' => 'indigo',
            'subjectHeader' => 'Client Name',
            'subjectProperty' => 'client.client_name',
            'date1Header' => 'Invoice Date',
            'date1Property' => 'inv_date',
            'date2Header' => 'Due Date',
            'date2Property' => 'due_date',
            'paymentField' => 'income_date',
            'editRoute' => 'outgoing-invoices.edit',
            'indexRoute' => 'outgoing-invoices.index',
            'updatePaymentRoute' => fn($id) => route('outgoing-invoices.update-field', ['outgoingInvoice' => $id, 'field' => 'income_date']),
            'updateFieldRoute' => fn($id, $field) => route('outgoing-invoices.update-field', ['outgoingInvoice' => $id, 'field' => $field]),
            'showOrder' => true,
            'showDoNumber' => true,
            'lineItems' => $invoice->lineItems ?? collect(),
            'isLineItemOutgoing' => true,
        ],
        'incoming' => [
            'themeColor' => 'red',
            'subjectHeader' => 'Vendor Name',
            'subjectProperty' => 'vendor.vendor_name',
            'date1Header' => 'Invoice Received Date',
            'date1Property' => 'inv_received_date',
            'date2Header' => 'INV / FP Date',
            'date2Property' => 'fp_date',
            'paymentField' => 'payment_date',
            'editRoute' => 'incoming-invoices.edit',
            'indexRoute' => 'incoming-invoices.index',
            'updatePaymentRoute' => fn($id) => route('incoming-invoices.update-field', ['incomingInvoice' => $id, 'field' => 'payment_date']),
            'updateFieldRoute' => fn($id, $field) => route('incoming-invoices.update-field', ['incomingInvoice' => $id, 'field' => $field]),
            'showOrder' => false,
            'showDoNumber' => false,
            'lineItems' => $invoice->items ?? collect(),
            'isLineItemOutgoing' => false,
        ],
    ][$invoiceType];

    // Extract for cleaner access
    extract($config);

    // Helper data
    $subjectName = data_get($invoice, $subjectProperty) ?? 'N/A';
    $dCode = data_get($invoice, 'department.department_code') ?? 'N/A';
    $initialDate = $invoice->{$paymentField};
    $formatDate = fn($date) => $date ? Carbon::parse($date)->format('d M Y') : null;
    $formatCurrency = fn($amount) => 'Rp.' . ' ' . number_format($amount, 0, ',', '.');
    $amount = $invoice->amount ?? 0;
    $vat = floor($amount * 0.11);
    $invoiced = $amount + $vat;

    // Routes
    $dateUpdateRoute = $updatePaymentRoute($invoice->id);
    $fpUpdateRoute = $updateFieldRoute ? $updateFieldRoute($invoice->id, 'fp_number') : null;
@endphp

<div x-data="{
    /* ---------------- PAYMENT DATE ---------------- */
    isEditingDate: false,
    savingDate: false,
    savedDate: @js($initialDate),
    editDate: null,
    dateError: null,

    startEditDate() {
        this.editDate = this.savedDate;
        this.isEditingDate = true;
        this.dateError = null;
    },

    get isPaid() {
        return !!this.savedDate;
    },

    get formattedDate() {
        if (!this.savedDate) return 'N/A';
        const d = new Date(this.savedDate + 'T00:00:00');
        return d.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: '2-digit'
        });
    },

    async saveDate() {
        this.savingDate = true;
        this.dateError = null;
        try {
            const res = await fetch('{{ $dateUpdateRoute }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    '{{ $paymentField }}': this.editDate || null
                })
            });

            if (!res.ok) {
                let errorData;
                try {
                    errorData = await res.json();
                } catch (e) {
                    // Error already handled via dateError
                    this.dateError = 'An unexpected error occurred. Please try again.';
                    throw e;
                }

                if (res.status === 422 && errorData.errors && errorData.errors['{{ $paymentField }}']) {
                    this.dateError = errorData.errors['{{ $paymentField }}'][0];
                } else {
                    this.dateError = errorData.message || 'Failed to save payment date.';
                }
                throw new Error('Save failed');
            }

            this.savedDate = this.editDate;
            this.isEditingDate = false;
        } catch (e) {
            // Error already handled via dateError
        } finally {
            this.savingDate = false;
        }
    },

    /* ---------------- FP NUMBER (Incoming only) ---------------- */
    isEditingFp: false,
    savingFp: false,
    savedFp: @js($invoice->fp_number),
    fpValue: @js($invoice->fp_number),
    fpError: null,

    async saveFp() {
        this.savingFp = true;
        this.fpError = null;
        try {
            const res = await fetch('{{ $fpUpdateRoute }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    fp_number: this.fpValue || null
                })
            });

            if (!res.ok) {
                let errorData;
                try {
                    errorData = await res.json();
                } catch (e) {
                    // Error already handled via fpError
                    this.fpError = 'An unexpected error occurred. Please try again.';
                    throw e;
                }

                if (res.status === 422 && errorData.errors && errorData.errors.fp_number) {
                    this.fpError = errorData.errors.fp_number[0];
                } else {
                    this.fpError = errorData.message || 'Failed to save FP number.';
                }
                throw new Error('Save failed');
            }

            this.savedFp = this.fpValue;
            this.isEditingFp = false;
        } catch (e) {
            // Error already handled via fpError
        } finally {
            this.savingFp = false;
        }
    }
}" class="p-4 sm:p-6">
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-xl overflow-hidden border border-gray-100">

        {{-- HEADER --}}
        <div class="bg-{{ $themeColor }}-600 p-5 sm:p-6">
            <a href="{{ route($indexRoute) }}"
                class="inline-flex mb-3 px-3 py-1.5 rounded-md bg-white/20 text-white text-sm font-medium hover:bg-white/30 transition">
                ← Back
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ $title }}</h1>
            <p class="text-lg text-{{ $themeColor }}-200 mt-1 font-semibold">
                MCN Order No: {{ $invoice->order->ord_number ??  $invoice->usageDepartment?->department_code ?? 'N/A' }} | D-Code: {{ $dCode }}
            </p>
        </div>

        <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            {{-- FLASH MESSAGES --}}
            @if (session('success'))
                <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                    class="col-span-full p-4 mb-4 bg-green-100 text-green-700 rounded-md border border-green-200 flex justify-between items-start">
                    <span>{{ session('success') }}</span>
                    <button type="button" @click="showMessage = false"
                        class="ml-4 -mt-1 p-1 rounded-full text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-600/50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" class="col-span-full p-4 mb-4 bg-red-100 text-red-700 rounded-md border border-red-200">
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

            {{-- COLUMN 1: CORE DETAILS --}}
            <div class="md:col-span-2 space-y-6">
                <div class="bg-gray-50 p-4 sm:p-5 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Invoice Information</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">{{ $subjectHeader }}</dt>
                            <dd class="font-bold text-gray-900">{{ $subjectName }}</dd>
                        </div>

                        <div class="flex justify-between">
                            <dt class="text-gray-500">Invoice Number</dt>
                            <dd class="font-semibold text-{{ $themeColor }}-700">{{ $invoice->inv_number ?? 'N/A' }}</dd>
                        </div>

                        <div class="flex justify-between">
                            <dt class="text-gray-500">{{ $date1Header }}</dt>
                            <dd class="font-semibold text-gray-900">{{ $formatDate($invoice->{$date1Property}) ?: 'N/A' }}</dd>
                        </div>

                        <div class="flex justify-between">
                            <dt class="text-gray-500">{{ $date2Header }}</dt>
                            <dd class="font-semibold text-gray-900">{{ $formatDate($invoice->{$date2Property}) ?: 'N/A' }}</dd>
                        </div>

                        {{-- FP NUMBER (Incoming) --}}
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-500">FP S/N</dt>
                            <dd class="flex items-center gap-2">
                                <template x-if="!isEditingFp">
                                    <span class="font-semibold" x-text="savedFp || 'N/A'"></span>
                                </template>
                                <template x-if="isEditingFp">
                                    <input type="text" x-model="fpValue" @input="fpError = null"
                                        class="text-sm border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-{{ $themeColor }}-500 focus:border-{{ $themeColor }}-500 outline-none transition"
                                        :class="fpError ? 'border-red-500 ring-red-300' : ''">
                                </template>
                                <button @click="isEditingFp = !isEditingFp"
                                    class="text-{{ $themeColor }}-600 hover:text-{{ $themeColor }}-800 focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                            </dd>
                        </div>
                        <div x-show="isEditingFp" class="flex justify-end items-center gap-2 mt-1 flex-wrap">
                            <div x-show="fpError" class="text-red-500 text-xs whitespace-nowrap">
                                <span x-text="fpError"></span>
                                <button type="button" @click="fpError = null" class="ml-1 text-red-600 hover:text-red-800 font-bold">&times;</button>
                            </div>
                            <button @click="saveFp" :disabled="savingFp" class="text-xs font-medium text-green-600 hover:text-green-800 disabled:opacity-50">
                                <span x-text="savingFp ? 'Saving...' : 'Save'"></span>
                            </button>
                            <button @click="isEditingFp = false; fpError = null" class="text-xs font-medium text-gray-600 hover:text-gray-800">Cancel</button>
                        </div>

                        @if (!$showOrder && $invoice->due_date)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Due Date</dt>
                                <dd class="font-semibold text-gray-900">{{ $formatDate($invoice->due_date) }}</dd>
                            </div>
                        @endif

                        @if ($showOrder)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">PO Number</dt>
                                <dd class="font-semibold text-gray-900">{{ $invoice->order?->purchaseOrder?->po_number ?? 'N/A' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">PO Date</dt>
                                <dd class="font-semibold text-gray-900">{{ $formatDate($invoice->order?->purchaseOrder?->po_date) ?? 'N/A' }}</dd>
                            </div>
                            @if ($showDoNumber)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">RPT Number</dt>
                                    <dd class="font-semibold text-gray-900">{{ $invoice->rpt_number ?? 'N/A' }}</dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">DO Number</dt>
                                    <dd class="font-semibold text-gray-900">{{ $invoice->do_number ?? 'N/A' }}</dd>
                                </div>
                            @endif
                        @endif
                    </dl>
                </div>

                <div class="p-4 sm:p-5 border border-gray-200 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Remarks</h3>
                    <p class="text-sm text-gray-700">{{ $invoice->remark ?? 'No remarks provided.' }}</p>
                </div>

                {{-- LINE ITEMS --}}
                <div class="bg-gray-50 p-4 sm:p-5 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Line Items</h3>
                    @if ($lineItems->isNotEmpty())
                        <ul class="space-y-2 text-sm">
                            @foreach ($lineItems as $item)
                                <li class="border-b pb-2 last:border-0 last:pb-0">
                                    <p class="font-medium">
                                        @if ($isLineItemOutgoing)
                                            {{ $item->item?->item_name ?? 'N/A' }}
                                        @else
                                            {{ $item->description ?? 'N/A' }}
                                        @endif
                                    </p>

                                    @if ($isLineItemOutgoing && $item->specs->isNotEmpty())
                                        <ul class="text-gray-600 mt-1 text-xs list-disc pl-4">
                                            @foreach ($item->specs as $spec)
                                                <li>{{ $spec->item_description }}</li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <p class="text-gray-500 mt-1">
                                        {{ $item->quantity }} × {{ $formatCurrency($isLineItemOutgoing ? ($item->subtotal / max($item->quantity, 1)) : $item->adjusted_unit_price) }}
                                    </p>
                                    <p class="font-semibold text-gray-900">{{ $formatCurrency($isLineItemOutgoing ? $item->subtotal : $item->adjusted_subtotal) }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 text-sm">No line items.</p>
                    @endif
                </div>
            </div>

            {{-- COLUMN 2: FINANCIALS --}}
            <div class="md:col-span-1 space-y-6">
                <div class="bg-gray-50 p-4 sm:p-5 rounded-lg border border-{{ $themeColor }}-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-{{ $themeColor }}-200">Financial Summary</h3>
                    <dl class="space-y-3 text-sm">
                        {{-- Base: After Agreement Fee --}}
                        @if ($invoiceType === 'incoming')
                            @php
                                $baseTotal = $invoice->items->sum(fn($item) => $item->quantity * $item->unit_price);
                                $feePct = $invoice->profit_percentage ?? 0;
                                $adjustedSubtotal = $baseTotal * (1 - $feePct / 100);
                                $ppnAmount = $invoice->ppn ? $baseTotal * 0.11 : 0;
                            @endphp
                            <div class="flex justify-between border-gray-200">
                                <dt class="text-gray-500">Subtotal</dt>
                                <dd class="font-bold text-gray-900">{{ $formatCurrency($adjustedSubtotal) }}</dd>
                            </div>

                            {{-- PPN (11%) --}}
                            @if($invoice->ppn)
                                @php
                                    $ppnGross = $baseTotal * 0.11;
                                    $pph23Hidden = $invoice->order?->is_pph23_prepaid ? ($baseTotal * 0.02) : 0;
                                    $netPpn = $ppnGross - $pph23Hidden;
                                @endphp
                                <div class="flex justify-between pb-3 border-b border-gray-300 text-xs">
                                    <dt class="text-gray-500">PPN (11%)</dt>
                                    <dd class="text-green-600">+{{ $formatCurrency($netPpn) }}</dd>
                                </div>
                            @endif

                            @foreach($invoice->taxes as $tax)
                                @php
                                    $taxAmount = $baseTotal * ($tax->tax_percentage / 100);
                                @endphp
                                <div class="flex justify-between pb-3 border-b border-gray-300 text-xs">
                                    <dt class="text-gray-500">{{ $tax->tax_name }} ({{ number_format($tax->tax_percentage, 2) }}%)</dt>
                                    <dd class="text-red-600">-{{ $formatCurrency($taxAmount) }}</dd>
                                </div>
                            @endforeach

                            <div class="flex justify-between border-gray-300">
                                <dt class="text-gray-500">Amount</dt>
                                <dd class="font-extrabold text-red-600">{{ $formatCurrency($invoice->amount) }}</dd>
                            </div>
                        @else
                            {{-- Outgoing logic (unchanged) --}}
                            <div class="flex justify-between pt-2">
                                <dt class="text-gray-500">Taxable Amount</dt>
                                <dd class="font-extrabold text-green-600">{{ $formatCurrency($invoice->amount) }}</dd>
                            </div>
                            <div class="flex justify-between pt-2">
                                <dt class="text-gray-500">VAT</dt>
                                <dd class="font-extrabold text-green-600">{{ $formatCurrency($vat) }}</dd>
                            </div>
                            <div class="flex justify-between pt-2">
                                <dt class="text-gray-500">Invoiced Amount</dt>
                                <dd class="font-extrabold text-green-600">{{ $formatCurrency($invoiced) }}</dd>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <dt class="text-gray-500">Status</dt>
                            <dd x-text="isPaid ? 'PAID' : 'UNPAID'" :class="isPaid ? 'text-green-600' : 'text-red-600'" class="font-extrabold uppercase"></dd>
                        </div>

                        {{-- PAYMENT DATE --}}
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-500">Payment Date</dt>
                            <dd class="flex items-center gap-2">
                                <span x-text="formattedDate" :class="isPaid ? 'text-green-600' : 'text-red-600'" class="font-extrabold"></span>
                                <button x-show="!isEditingDate" @click="startEditDate()"
                                    class="text-{{ $themeColor }}-600 hover:text-{{ $themeColor }}-800 focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                            </dd>
                        </div>

                        <div x-show="isEditingDate" class="pt-2 flex flex-col">
                            <div class="flex items-center gap-2">
                                <input type="date" x-model="editDate" @input="dateError = null"
                                    class="text-sm border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-{{ $themeColor }}-500 focus:border-{{ $themeColor }}-500 outline-none transition"
                                    :class="dateError ? 'border-red-500 ring-red-300' : ''" />
                                <button @click="saveDate" class="text-xs font-medium text-green-600 hover:text-green-800" x-text="savingDate ? 'Saving...' : 'Save'"></button>
                                <button @click="isEditingDate = false; dateError = null" class="text-xs font-medium text-gray-600 hover:text-gray-800">Cancel</button>
                            </div>
                            <div x-show="dateError" class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <span x-text="dateError"></span>
                                <button type="button" @click="dateError = null" class="text-red-600 hover:text-red-800 font-bold">&times;</button>
                            </div>
                        </div>
                    </dl>
                </div>

                {{-- RELATED DOCS --}}
                <div class="p-4 sm:p-5 border border-gray-200 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-200">Related Documents</h3>
                    <div class="space-y-3">
                        @php $hasOrder = $invoice->order_id && $invoice->order; @endphp
                        <a href="{{ $hasOrder ? route('orders.show', $invoice->order_id) : 'javascript:void(0)' }}"
                            class="{{ $hasOrder ? 'bg-' . $themeColor . '-50 hover:bg-' . $themeColor . '-100 text-' . $themeColor . '-700' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }} block w-full text-center py-2 text-sm rounded-md font-medium transition">
                            {{ $hasOrder ? 'Order ' . $invoice->order->ord_number : 'No Order Detail' }}
                        </a>
                        @if ($invoiceType === 'outgoing')
                            <a href="{{ route('outgoing-invoices.generate-single', $invoice->id) }}" target="_blank"
                                class="block w-full text-center py-2 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white font-medium transition">
                                Generate Document (PDF)
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>