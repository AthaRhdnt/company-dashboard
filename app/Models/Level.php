<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_name',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function getPermissionListAttribute()
    {
        // Checks if the 'permissions' relationship has been loaded (eager-loaded)
        if ($this->relationLoaded('permissions')) {
            // Map the permissions collection to get only the 'permission_name' field
            // Then join them into a single string separated by a comma and a space
            return $this->permissions->pluck('permission_name')->implode(', ');
        }
        
        // Fallback if the relationship wasn't loaded (though your controller loads it)
        return 'N/A';
    }
}
