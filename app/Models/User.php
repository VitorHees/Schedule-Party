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

    /**
     * User belongs to a country
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * User belongs to a zipcode
     */
    public function zipcode(): BelongsTo
    {
        return $this->belongsTo(Zipcode::class);
    }

    /**
     * User belongs to a gender
     */
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    /**
     * User has many calendars through pivot (many-to-many)
     */
    public function calendars(): BelongsToMany
    {
        return $this->belongsToMany(Calendar::class, 'calendar_user')
            ->withPivot('role_id', 'guest_token', 'max_event_distance_km', 'joined_at')
            ->withTimestamps();
    }

    /**
     * User has many calendar_user pivot records
     */
    public function calendarUsers(): HasMany
    {
        return $this->hasMany(CalendarUser::class);
    }

    /**
     * User belongs to many groups (many-to-many)
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * Events created by this user
     */
    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Events the user has opted in/out of
     */
    public function participatingEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_participants')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Comments made by this user
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Vote responses by this user
     */
    public function voteResponses(): HasMany
    {
        return $this->hasMany(VoteResponse::class);
    }

    /**
     * Invitations created by this user
     */
    public function createdInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'created_by');
    }

    /**
     * Activity logs for this user
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user has permission in a calendar
     */
    public function hasPermissionInCalendar(Calendar $calendar, string $permissionSlug): bool
    {
        $calendarUser = $this->calendarUsers()
            ->where('calendar_id', $calendar->id)
            ->first();

        if (!$calendarUser) {
            return false;
        }

        // Check for individual override first
        $override = $calendarUser->permissionOverrides()
            ->whereHas('permission', fn($q) => $q->where('slug', $permissionSlug))
            ->first();

        if ($override) {
            return $override->granted;
        }

        // Check role default permissions
        return $calendarUser->role->permissions()
            ->where('slug', $permissionSlug)
            ->wherePivot('granted', true)
            ->exists();
    }
}
