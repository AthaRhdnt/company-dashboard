<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $invoice['invoice']->inv_number ?? 'Draft' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24pt;
            color: #333;
            border-bottom: 3px solid #000;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 0;
        }
        .company-info {
            float: right;
            text-align: right;
            line-height: 1.5;
            font-size: 9pt;
        }
        .clear {
            clear: both;
        }
        .details-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        /* New styles for better layout control */
        .info-column {
            width: 45%;
            float: left;
            line-height: 1.5;
            padding: 0 5px;
        }
        .info-column-right {
            float: right;
        }
        .info-column h4 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 10pt;
            border-bottom: 1px solid #000;
            display: inline-block;
        }

        .charged-to {
            width: 45%;
            float: left;
            line-height: 1.5;
        }
        .invoice-info {
            width: 45%;
            float: right;
            font-size: 10pt;
        }
        .invoice-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-info table td {
            padding: 3px 0;
        }
        .invoice-info .label {
            width: 40%;
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
            text-transform: uppercase;
            font-size: 9pt;
        }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }

        .summary-container {
            margin-top: 20px;
            width: 100%;
        }
        .amount-in-words {
            width: 55%;
            float: left;
            padding-top: 10px;
            line-height: 1.4;
        }
        .financial-summary {
            width: 40%;
            float: right;
            border: 2px solid #000;
        }
        .financial-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .financial-summary td {
            padding: 5px 8px;
            border-bottom: 1px solid #ccc;
        }
        .financial-summary .label {
            font-weight: bold;
            text-align: left;
            width: 60%;
        }
        .financial-summary .value {
            text-align: right;
        }
        .signatory-block {
            margin-top: 40px;
            width: 100%;
        }
        .signatory-block .signature-column {
            width: 40%;
            float: right;
            text-align: center;
        }
        .bank-details {
            width: 55%;
            float: left;
            border: 1px solid #000;
            padding: 10px;
        }
        .bank-details p {
            margin: 0;
            line-height: 1.5;
        }
        .items-table .details-cell {
            white-space: pre-wrap; /* Ensures line breaks in content are respected */
        }
    </style>
</head>
<body>
    <div class="container">
        
        {{-- COMPANY INFO (Placeholder) --}}
        <div class="company-info">
            **MY CLIENT COMPANY (TEMPLATE)**<br>
            Jl. Contoh Alamat No. 123<br>
            Jakarta, Indonesia 12345<br>
            Telp: (021) 1234567 | Fax: (021) 7654321<br>
            NPWP: 01.234.567.8-901.234
        </div>
        
        <div class="header">
            <h1>INVOICE</h1>
        </div>
        <div class="clear"></div>

        {{-- CLIENT AND INVOICE DETAILS --}}
        <div class="details-table">
            
            {{-- CLIENT DETAILS (CHARGED TO) --}}
            <div class="charged-to info-column">
                <h4>CHARGED TO</h4>
                @php $client = $invoice['client']; @endphp
                <p style="margin-top: 5px;">
                    **{{ $client->client_name ?? 'N/A' }}**<br>
                    {{ $client->address ?? 'Full Address Details' }}<br>
                    {{-- Removed address_road and address_city, using the single 'address' field from the model --}}
                    
                    @if($client->phone_number) Telp: {{ $client->phone_number }} @endif
                    @if($client->phone_number && $client->fax_number); @endif 
                    @if($client->fax_number) Fax: {{ $client->fax_number }} @endif
                    <br>
                    UP: {{ $client->contact_person_name ?? 'CONTACT PERSON' }}
                </p>
            </div>
            
            {{-- INVOICE REFERENCE DETAILS --}}
            <div class="invoice-info info-column info-column-right">
                <table>
                    <tr>
                        <td class="label">Inv. No.</td>
                        <td>:</td>
                        <td>{{ $invoice['invoice']->inv_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Inv. Date</td>
                        <td>:</td>
                        <td>{{ $invoice['dates']['invDate'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Due Date</td>
                        <td>:</td>
                        <td>{{ $invoice['dates']['dueDate'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">FP S/N</td>
                        <td>:</td>
                        <td>{{ $invoice['invoice']->fp_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">PO No.</td>
                        <td>:</td>
                        {{-- The PHP helper guarantees 'order' exists if poDate exists, access the nested relationship --}}
                        <td>{{ data_get($invoice['order'], 'purchaseOrder.po_number') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">PO Date</td>
                        <td>:</td>
                        <td>{{ $invoice['dates']['poDate'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="clear"></div>
        
        {{-- ORDER & PROJECT DETAILS --}}
        @php $order = $invoice['order']; @endphp
        @if($order)
        <div class="details-table" style="margin-top: 5px;">
            <div class="info-column" style="width: 100%; padding: 5px 0 0 0;">
                <h4>ORDER & PROJECT DETAILS</h4>
                <p style="margin-top: 5px; line-height: 1.4;">
                    Order No: {{ $order->ord_number ?? '-' }} &nbsp;&nbsp;|&nbsp;&nbsp; 
                    Department: {{ data_get($order, 'department.department_code') ?? 'N/A' }} &nbsp;&nbsp;|&nbsp;&nbsp;
                    Project: {{ $order->project_name ?? 'N/A' }}<br>
                </p>
            </div>
        </div>
        <div class="clear"></div>
        @endif

        {{-- ITEMS TABLE --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No.</th>
                    <th style="width: 45%;">Details</th>
                    <th class="text-right" style="width: 10%;">Quantity</th>
                    <th style="width: 10%;">Unit</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 15%;">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                {{-- Items now use calculated and real data from InvoiceItem model --}}
                @forelse ($invoice['items'] as $item)
                    <tr>
                        <td class="text-center">{{ $item['no'] }}</td>
                        {{-- FIXED: Use nl2br to convert \n (new lines) to <br> for specs display --}}
                        <td class="details-cell">{!! nl2br(e($item['details'])) !!}</td> 
                        <td class="text-right">{{ $item['quantity'] }}</td>
                        <td>{{ $item['unit'] }}</td>
                        <td class="text-right">Rp {{ $item['unit_price'] }}</td>
                        <td class="text-right">Rp {{ $item['sub_total'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No line items specified for this invoice.</td>
                    </tr>
                @endforelse
                
                {{-- Fill empty space for presentation (optional) --}}
                @for ($i = count($invoice['items']); $i < 5; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        {{-- SUMMARY & AMOUNT IN WORDS --}}
        <div class="summary-container">
            <div class="amount-in-words">
                Says: <br>
                # {{ $invoice['financials']['amountInWords'] }} #
            </div>
            <div class="financial-summary">
                <table>
                    <tr>
                        <td class="label">Taxable Amount (DPP)</td>
                        <td class="text-right">Rp</td>
                        <td class="value">{{ $invoice['financials']['taxableAmount'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">VAT (PPN 11%)</td>
                        <td class="text-right">Rp</td>
                        <td class="value">{{ $invoice['financials']['vat'] }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #000;">
                        <td class="label">Invoiced Amount</td>
                        <td class="text-right">Rp</td>
                        <td class="value">{{ $invoice['financials']['totalAmount'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="clear"></div>

        {{-- BANK DETAILS AND SIGNATORY --}}
        <div class="signatory-block">
            <div class="bank-details">
                <p>Please place your transfer to:</p>
                <p>Bank: **BANK NAME (TEMPLATE)**</p>
                <p>Account Name: **MY CLIENT COMPANY (TEMPLATE)**</p>
                <p>Account Number: **1234567890 (TEMPLATE)**</p>
            </div>
            
            <div class="signature-column">
                <p>Jakarta, {{ $invoice['dates']['invDate'] }}</p>
                <br><br><br><br>
                **Director Name (Template)**<br>
                Director
            </div>
        </div>
        <div class="clear"></div>
        
    </div>
</body>
</html>
