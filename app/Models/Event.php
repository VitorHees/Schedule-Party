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
        'name',
        'description',
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
            'comments_enabled' => 'boolean',
            'opt_in_enabled' => 'boolean',
        ];
    }

    /**
     * Event belongs to a calendar
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Event was created by a user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Event belongs to many groups (many-to-many)
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'event_group')
            ->withTimestamps();
    }

    /**
     * Event has many participants (opt-in/out)
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Event has many comments
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Event has many votes
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get mixed color from all assigned groups
     */
    public function getMixedColorAttribute(): string
    {
        $groupColors = $this->groups->pluck('color');

        if ($groupColors->isEmpty()) {
            return '#94A3B8'; // Default gray
        }

        if ($groupColors->count() === 1) {
            return $groupColors->first();
        }

        // Return as CSS gradient for multiple colors
        return 'linear-gradient(90deg, ' . $groupColors->implode(', ') . ')';
    }

    /**
     * Check if event is repeating
     */
    public function isRepeating(): bool
    {
        return $this->repeat_frequency !== 'none';
    }

    /**
     * Check if user is participating
     */
    public function isUserParticipating(User $user): bool
    {
        return $this->participants()
            ->where('user_id', $user->id)
            ->wherePivot('status', 'opted_in')
            ->exists();
    }

    /**
     * Get participant count
     */
    public function getParticipantCountAttribute(): int
    {
        return $this->participants()
            ->wherePivot('status', 'opted_in')
            ->count();
    }
}
