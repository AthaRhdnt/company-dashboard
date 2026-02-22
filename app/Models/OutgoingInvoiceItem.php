<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutgoingInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'outgoing_invoice_id',
        'item_id',
        'quantity',
        'subtotal',
    ];

    public function outgoingInvoice()
    {
        return $this->belongsTo(OutgoingInvoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    
    public function specs()
    {
        return $this->belongsToMany(ItemSpec::class, 'invoice_item_specs', 'invoice_item_id', 'item_spec_id');
    }
}
