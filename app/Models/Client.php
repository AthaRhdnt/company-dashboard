<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'address',
        'phone_number',
        'fax_number',
        'contact_person_name',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function outgoingInvoices()
    {
        return $this->hasMany(OutgoingInvoice::class);
    }
}
