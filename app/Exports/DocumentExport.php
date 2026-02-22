<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DocumentExport implements WithMultipleSheets
{
    protected $invoice;

    public function __construct(array $invoiceData)
    {
        $this->invoice = $invoiceData;
    }

    public function sheets(): array
    {
        return [
            new InvoiceExport($this->invoice),
            new ReceiptExport($this->invoice),
            new DeliveryExport($this->invoice),
        ];
    }
}
