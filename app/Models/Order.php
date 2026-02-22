<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use NumberFormatter;

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
        'remark',
        'is_pph23_prepaid',
    ];

    protected $appends = [
        'transaction_status',
        'sort_group',
    ];

    /* ======================
     |  Relationships
     ====================== */

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

    /* ======================
     |  Lifecycle
     ====================== */

    public function getTransactionStatusAttribute(): string
    {
        // Cancelled (explicit business rule)
        if (
            ($this->amount ?? 0) == 0 &&
            Str::contains(strtolower($this->remark ?? ''), 'cancelled')
        ) {
            return 'Cancelled';
        }

        // Completed
        $hasIncome = $this->outgoingInvoices
            ->contains(fn ($inv) => !empty($inv->income_date));

        $hasPayment = $this->incomingInvoices
            ->contains(fn ($inv) =>
                !empty($inv->payment_date) &&
                ($inv->amount ?? 0) > 0
            );

        if ($hasIncome && $hasPayment) {
            return 'Completed';
        }

        // Otherwise
        return 'Ongoing';
    }

    public function getSortGroupAttribute(): int
    {
        // Lower = higher priority
        return $this->transaction_status === 'Ongoing' ? 1 : 2;
    }

    /* ======================
     |  Formatting
     ====================== */

    protected function formattedAmount(): Attribute
    {
        return Attribute::get(function () {
            if ($this->amount === null) {
                return '0';
            }

            $formatter = new NumberFormatter('id_ID', NumberFormatter::DECIMAL);
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);

            return $formatter->format($this->amount);
        });
    }
}
