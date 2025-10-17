<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name',
        'department_code',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
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
