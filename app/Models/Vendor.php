<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_name',
    ];

    public function incomingInvoices()
    {
        return $this->hasMany(IncomingInvoice::class);
    }
}
