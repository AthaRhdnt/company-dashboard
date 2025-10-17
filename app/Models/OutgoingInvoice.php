<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutgoingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'inv_number',
        'inv_date',
        'due_date',
        'fp_number',
        'income_date',
        'cur',
        'amount',
        'po_number',
        'order_id',
        'client_id',
        'department_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function lineItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'outgoing_invoice_taxes');
    }

    public function getTotalLineItemsCountAttribute()
    {
        return $this->lineItems->count();
    }
}
