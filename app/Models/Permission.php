<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
    ];

    /**
     * Permission belongs to many roles (many-to-many)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withPivot('granted')
            ->withTimestamps();
    }

    /**
     * Permission has many user overrides
     */
    public function userOverrides(): HasMany
    {
        return $this->hasMany(UserPermissionOverride::class);
    }
}
