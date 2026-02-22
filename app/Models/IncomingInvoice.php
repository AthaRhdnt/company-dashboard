<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'inv_number',
        'inv_received_date',
        'due_date',
        'fp_date',
        'fp_number',
        'cur',
        'amount',
        'profit_percentage',
        'payment_date',
        'order_id',
        'vendor_id',
        'department_id',
        'usage_department_id',
        'remark',
        'ppn'
    ];

    protected $appends = ['transaction_status', 'sort_group'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function usageDepartment()
    {
        return $this->belongsTo(Department::class, 'usage_department_id');
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'incoming_invoice_taxes');
    }

    public function items()
    {
        return $this->hasMany(IncomingInvoiceItem::class);
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2, ',', '.');
    }

    public function getTransactionStatusAttribute()
    {
        return $this->order
            ? $this->order->transaction_status
            : 'Completed';
    }

    public function getSortGroupAttribute()
    {
        return $this->order
            ? $this->order->sort_group
            : 2;
    }
}
