<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'inv_number',
        'inv_received_date',
        'fp_date',
        'fp_number',
        'cur',
        'amount',
        'profit_percentage',
        'payment_date',
        'order_id',
        'vendor_id',
        'department_id',
    ];

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

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'incoming_invoice_taxes');
    }
}
