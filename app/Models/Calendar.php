<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'groups_locked',
    ];

    protected function casts(): array
    {
        return [
            'groups_locked' => 'boolean',
        ];
    }

    /**
     * Calendar has many users through pivot (many-to-many)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'calendar_user')
            ->withPivot('role_id', 'guest_token', 'max_event_distance_km', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Calendar has many calendar_user pivot records
     */
    public function calendarUsers(): HasMany
    {
        return $this->hasMany(CalendarUser::class);
    }

    /**
     * Calendar has many groups
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Calendar has many events
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Calendar has many invitations
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Calendar has many activity logs
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the owner of the calendar
     */
    public function owner(): ?User
    {
        return $this->calendarUsers()
            ->whereHas('role', fn($q) => $q->where('slug', 'owner'))
            ->first()?->user;
    }

    /**
     * Check if calendar is personal
     */
    public function isPersonal(): bool
    {
        return $this->type === 'personal';
    }

    /**
     * Check if calendar is collaborative
     */
    public function isCollaborative(): bool
    {
        return $this->type === 'collaborative';
    }

    /**
     * Log an activity for this calendar
     */
    public function logActivity(string $action, string $resourceType, ?int $resourceId = null, ?User $user = null, ?array $details = null): void
    {
        $this->activityLogs()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'details' => $details,
        ]);
    }
}
