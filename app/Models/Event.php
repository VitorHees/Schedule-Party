<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'created_by',
        'series_id',
        'name',
        'description',
        'is_nsfw',
        'images',
        'start_date',
        'end_date',
        'is_all_day',
        'location',
        'url',
        'repeat_frequency',
        'repeat_end_date',
        'visibility_rule',
        'max_distance_km',
        'min_age',
        'event_zipcode',
        'event_country_id',
        'is_role_restricted', // Legacy field, kept for safety
        'comments_enabled',
        'opt_in_enabled',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'repeat_end_date' => 'date',
            'is_all_day' => 'boolean',
            'is_nsfw' => 'boolean',
            'is_role_restricted' => 'boolean',
            'comments_enabled' => 'boolean',
            'opt_in_enabled' => 'boolean',
        ];
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Event belongs to many groups.
     * UPDATED: Includes the 'is_restricted' pivot column.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'event_group')
            ->withPivot('is_restricted')
            ->withTimestamps();
    }

    public function genders(): BelongsToMany
    {
        return $this->belongsToMany(Gender::class, 'event_gender')
            ->withTimestamps();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'event_country_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getMixedColorAttribute(): string
    {
        $groupColors = $this->groups->pluck('color');

        if ($groupColors->isEmpty()) {
            return '#94A3B8'; // Default gray
        }

        if ($groupColors->count() === 1) {
            return $groupColors->first();
        }

        return 'linear-gradient(90deg, ' . $groupColors->implode(', ') . ')';
    }

    public function isRepeating(): bool
    {
        return $this->repeat_frequency !== 'none';
    }

    public function isUserParticipating(User $user): bool
    {
        return $this->participants()
            ->where('user_id', $user->id)
            ->wherePivot('status', 'opted_in')
            ->exists();
    }

    public function getParticipantCountAttribute(): int
    {
        return $this->participants()
            ->wherePivot('status', 'opted_in')
            ->count();
    }
}
