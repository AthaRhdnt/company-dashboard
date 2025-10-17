<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_name',
        'tax_percentage',
    ];
    
    // Relationships (Optional but good practice)
    public function outgoingInvoices()
    {
        return $this->belongsToMany(OutgoingInvoice::class, 'outgoing_invoice_taxes');
    }

    public function incomingInvoices()
    {
        return $this->belongsToMany(IncomingInvoice::class, 'incoming_invoice_taxes');
    }
}
