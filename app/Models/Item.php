<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($item) {
            // Delete all associated ItemSpecs
            $item->itemSpecs()->delete();
        });
    }

    protected function itemSpec(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Safely retrieve the count using the convention Laravel creates with withCount()
                $count = $this->itemSpecs_count ?? $this->itemSpecs()->count();
                
                if ($count > 0) {
                    return "{$count} Detail(s)"; 
                }
                return 'No Specs';
            },
        );
    }
}
