<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemSpec extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'item_description',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function invoiceItems()
    {
        return $this->belongsToMany(InvoiceItem::class, 'invoice_item_specs', 'item_spec_id', 'invoice_item_id');
    }
}
