@props([
    'invoice',
    'title',
    'themeColor',       // e.g., 'indigo' or 'red'
    'subjectHeader',    // e.g., 'Client Name' or 'Vendor Name'
    'subjectProperty',  // e.g., 'client.client_name' or 'vendor.vendor_name'
    'date1Header',      // e.g., 'Invoice Date' or 'Invoice Received Date'
    'date1Property',    // e.g., 'inv_date' or 'inv_received_date'
    'date2Header',      // e.g., 'Due Date' or 'FP Date'
    'date2Property',    // e.g., 'due_date' or 'fp_date'
    // 'editRouteName' is removed
])

@php
    $subjectName = data_get($invoice, $subjectProperty) ?? 'N/A';
    $dCode = data_get($invoice, 'order.department.department_code') ?? 'N/A';
    $isOutgoing = ($themeColor == 'indigo');
    
    // Helper to display a detail row
    $detailRow = function($label, $value, $textColor = 'text-gray-900') use ($themeColor) {
        $labelColor = 'text-gray-500';
        return "
            <div class='flex justify-between border-b border-gray-100 py-3'>
                <dt class='text-sm font-medium {$labelColor}'>{$label}</dt>
                <dd class='text-sm {$textColor} font-semibold'>{$value}</dd>
            </div>
        ";
    };

    // Helper to display a financial row
    $financeRow = function($label, $value, $currency, $highlight = false) use ($themeColor, $invoice) {
        $textColor = $highlight 
            ? ($themeColor == 'indigo' ? 'text-green-600' : 'text-red-600') 
            : 'text-gray-900';
        $labelColor = 'text-gray-500';

        // Use the already formatted amount
        return "
            <div class='flex justify-between py-3 " . ($highlight ? 'border-t-2 border-dashed border-' . $themeColor . '-200' : 'border-b border-gray-100') . "'>
                <dt class='text-sm font-medium {$labelColor}'>" . strtoupper($label) . "</dt>
                <dd class='text-lg {$textColor} font-extrabold'>
                    {$invoice->formatted_amount}
                </dd>
            </div>
        ";
    };
@endphp

<div class="p-6">
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-2xl overflow-hidden">
        
        {{-- HEADER (EDIT BUTTON REMOVED) --}}
        <div class="bg-{{ $themeColor }}-600 p-6 sm:p-8 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-extrabold text-white">{{ $title }}</h1>
                <p class="mt-1 text-sm text-{{ $themeColor }}-200">
                    MCN Order No: {{ $invoice->order->ord_number ?? 'N/A' }} | D-Code: {{ $dCode }}
                </p>
            </div>
            {{-- The Edit Invoice button was here and has been intentionally removed. --}}
        </div>

        <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- COLUMN 1: CORE DETAILS & SUBJECT --}}
            <div class="md:col-span-2 space-y-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Invoice Information</h3>
                    
                    <dl class="divide-y divide-gray-200">
                        {!! $detailRow('INVOICE NO', $invoice->inv_number ?? 'PENDING', 'text-' . $themeColor . '-700') !!}
                        
                        {{-- Subject Name --}}
                        <div class='flex justify-between border-b border-gray-100 py-3'>
                            <dt class='text-sm font-medium text-gray-500'>{{ strtoupper($subjectHeader) }}</dt>
                            <dd class='text-sm text-gray-900 font-bold'>{{ $subjectName }}</dd>
                        </div>
                        
                        {{-- Date 1 --}}
                        {!! $detailRow($date1Header, $invoice->{$date1Property . '_formatted'} ?? 'N/A') !!}

                        {{-- Date 2 --}}
                        {!! $detailRow($date2Header, $invoice->{$date2Property . '_formatted'} ?? 'N/A') !!}
                        
                        {{-- PO / FP Number --}}
                        @if($isOutgoing)
                            {!! $detailRow('PO NO', $invoice->po_number ?? 'N/A') !!}
                            {!! $detailRow('PO DATE', $invoice->po_date_formatted ?? 'N/A') !!}
                        @else
                            {!! $detailRow('FP S/N', $invoice->fp_number ?? 'N/A') !!}
                        @endif

                    </dl>
                </div>

                {{-- REMARKS --}}
                <div class="p-4 border border-gray-200 rounded-lg">
                    <h3 class="text-lg font-bold mb-2 text-gray-800">Remarks</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $invoice->remarks ?? 'No remarks provided.' }}</p>
                </div>
            </div>

            {{-- COLUMN 2: FINANCIALS & STATUS --}}
            <div class="md:col-span-1 space-y-6">
                
                {{-- FINANCIAL SUMMARY --}}
                <div class="bg-gray-50 p-4 rounded-lg border-2 border-{{ $themeColor }}-100">
                    <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Financial Summary</h3>
                    <dl>
                        {{-- Since you are transforming the data in the controller to include formatted_amount, it is used here --}}
                        {!! $financeRow('INVOICE AMOUNT', $invoice->amount, $invoice->cur, $highlight = true) !!} 
                        
                        <div class='flex justify-between border-b border-gray-100 py-3'>
                            <dt class='text-sm font-medium text-gray-500'>CURRENCY</dt>
                            <dd class='text-sm text-gray-900 font-semibold'>{{ $invoice->cur ?? 'IDR' }}</dd>
                        </div>

                        {{-- Payment Status --}}
                        @php
                            $pmtTextColor = $invoice->payment_date ? 'text-green-600' : 'text-red-600';
                            $pmtStatus = $invoice->payment_date ? 'PAID' : 'PENDING';
                        @endphp
                        <div class='flex justify-between border-b border-gray-100 py-3'>
                            <dt class='text-sm font-medium text-gray-500'>STATUS</dt>
                            <dd class='text-sm font-extrabold uppercase {{ $pmtTextColor }}'>{{ $pmtStatus }}</dd>
                        </div>

                        {{-- Payment Date --}}
                        {!! $detailRow('PAYMENT DATE', $invoice->payment_date_formatted ?? 'N/A', $pmtTextColor) !!}
                    </dl>
                </div>

                {{-- RELATED DOCS/ACTIONS --}}
                <div class="p-4 border border-gray-200 rounded-lg">
                    <h3 class="text-lg font-bold mb-3 text-gray-800 border-b pb-2">Related Documents</h3>
                    <div class="space-y-2">
                        <a href="{{ route('orders.show', $invoice->order_id) }}" 
                            class="block w-full text-center py-2 text-sm rounded-md bg-{{ $themeColor }}-50 hover:bg-{{ $themeColor }}-100 text-{{ $themeColor }}-600 font-semibold transition duration-150">
                            View MCN Order {{ $invoice->order->ord_number ?? '' }}
                        </a>
                        {{-- NEW: Generate Document Button --}}
                        <a href="{{ route('outgoing-invoices.generate-single', $invoice->id) }}" 
                            target="_blank" {{-- Open PDF in new tab --}}
                            class="block w-full text-center py-2 text-sm rounded-md bg-green-500 hover:bg-green-600 text-white font-semibold transition duration-150">
                            Generate Invoice Document (PDF)
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>