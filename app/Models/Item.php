<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'item_price',
    ];

    public function itemSpecs()
    {
        return $this->hasMany(ItemSpec::class);
    }
}
