<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Role has many permissions (many-to-many)
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withPivot('granted')
            ->withTimestamps();
    }

    /**
     * Role has many calendar users
     */
    public function calendarUsers(): HasMany
    {
        return $this->hasMany(CalendarUser::class);
    }

    /**
     * Role has many invitations
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()
            ->where('slug', $permissionSlug)
            ->wherePivot('granted', true)
            ->exists();
    }

    /**
     * Grant a permission to this role
     */
    public function grantPermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([
            $permission->id => ['granted' => true]
        ]);
    }

    /**
     * Revoke a permission from this role
     */
    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->updateExistingPivot($permission->id, [
            'granted' => false
        ]);
    }
}
