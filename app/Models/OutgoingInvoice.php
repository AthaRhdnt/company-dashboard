<?php

namespace App\Models;

use Carbon\Carbon;
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
        'rpt_number',
        'do_number',
        'cur',
        'amount',
        'po_number',
        'order_id',
        'client_id',
        'department_id',
        'remark',
    ];

    protected $appends = ['transaction_status', 'sort_group'];

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
        return $this->hasMany(OutgoingInvoiceItem::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'outgoing_invoice_taxes');
    }

    public function getTotalLineItemsCountAttribute()
    {
        return $this->lineItems->count();
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2, ',', '.');
    }

    public function getTransactionStatusAttribute(): string
    {
        return $this->order
            ? $this->order->transaction_status
            : 'Completed';
    }

    public function getSortGroupAttribute(): int
    {
        return $this->order
            ? $this->order->sort_group
            : 2;
    }
}
