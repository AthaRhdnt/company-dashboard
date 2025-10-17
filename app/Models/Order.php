<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'ord_number',
        'ord_date',
        'project_name',
        'cur',
        'client_id',
        'purchase_order_id',
        'department_id',
        'amount',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function incomingInvoices()
    {
        return $this->hasMany(IncomingInvoice::class);
    }
    
    public function outgoingInvoices()
    {
        return $this->hasMany(OutgoingInvoice::class);
    }
}
