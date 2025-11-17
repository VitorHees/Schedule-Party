<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarUser extends Model
{
    use HasFactory;

    protected $table = 'calendar_user'; // Explicitly set singular table name

    protected $fillable = [
        'calendar_id',
        'user_id',
        'role_id',
        'guest_token',
        'max_event_distance_km',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    /**
     * CalendarUser belongs to a calendar
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * CalendarUser belongs to a user (nullable for guests)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * CalendarUser belongs to a role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * CalendarUser has many permission overrides
     */
    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(UserPermissionOverride::class);
    }

    /**
     * Check if this is a guest user
     */
    public function isGuest(): bool
    {
        return !is_null($this->guest_token) && is_null($this->user_id);
    }

    /**
     * Check if user is owner
     */
    public function isOwner(): bool
    {
        return $this->role->slug === 'owner';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role->slug === 'admin';
    }

    /**
     * Check if user is regular
     */
    public function isRegular(): bool
    {
        return $this->role->slug === 'regular';
    }
}
