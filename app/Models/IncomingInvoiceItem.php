<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomingInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'incoming_invoice_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $appends = ['adjusted_unit_price', 'adjusted_subtotal',];

    public function incomingInvoice()
    {
        return $this->belongsTo(IncomingInvoice::class);
    }

    public function getAdjustedUnitPriceAttribute()
    {
        $invoice = $this->incomingInvoice;
        $feePct = $invoice?->profit_percentage ?? 0;
        return $this->unit_price * (1 - ($feePct / 100));
    }

    public function getAdjustedSubtotalAttribute()
    {
        return $this->quantity * $this->adjusted_unit_price;
    }
}
