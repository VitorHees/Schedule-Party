<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone_number',
        'profile_picture',
        'birth_date',
        'country_id',
        'zipcode_id',
        'gender_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials for avatar
     */
    public function initials(): string
    {
        return Str::of($this->username)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // Relationships

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function zipcode(): BelongsTo
    {
        return $this->belongsTo(Zipcode::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function calendars(): BelongsToMany
    {
        return $this->belongsToMany(Calendar::class, 'calendar_user')
            ->withPivot('role_id', 'guest_token', 'max_event_distance_km', 'joined_at')
            ->withTimestamps();
    }

    public function calendarUsers(): HasMany
    {
        return $this->hasMany(CalendarUser::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function participatingEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_participants')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function voteResponses(): HasMany
    {
        return $this->hasMany(VoteResponse::class);
    }

    public function createdInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user has permission in a calendar
     * Hierarchy: User Override > Label Permissions > Role Permissions
     */
    public function hasPermissionInCalendar(Calendar $calendar, string $permissionSlug): bool
    {
        // Get the pivot record for this user and calendar
        $calendarUser = $this->calendarUsers()
            ->where('calendar_id', $calendar->id)
            ->first();

        // If user is not in the calendar, they have no permissions (unless owner check happens elsewhere)
        if (!$calendarUser) {
            return false;
        }

        // 1. User Permission Override (Highest Priority)
        // Check if there is a specific Allow/Deny override for this user
        $override = $calendarUser->permissionOverrides()
            ->whereHas('permission', fn($q) => $q->where('slug', $permissionSlug))
            ->first();

        if ($override) {
            return (bool) $override->granted;
        }

        // 2. Label (Group) Permissions (Medium Priority)
        // Check if the user belongs to any Group in this calendar that specifically grants this permission.
        $hasLabelPermission = $this->groups()
            ->where('calendar_id', $calendar->id)
            ->whereHas('permissions', fn($q) => $q->where('slug', $permissionSlug))
            ->exists();

        if ($hasLabelPermission) {
            return true;
        }

        // 3. Role Permissions (Lowest Priority)
        // Fallback to the user's assigned role
        return $calendarUser->role->permissions()
            ->where('slug', $permissionSlug)
            ->wherePivot('granted', true)
            ->exists();
    }
}
