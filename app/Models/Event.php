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
        'latitude',
        'longitude',
        'url',
        'repeat_frequency',
        'repeat_end_date',
        'max_distance_km',
        'min_age',
        'event_zipcode',
        'event_country_id',
        // 'is_role_restricted', // REMOVED: Legacy field
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
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_nsfw' => 'boolean',
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

    /**
     * UPDATED: Returns array of colors instead of CSS string.
     * Let the View handle the gradient/style logic.
     */
    public function getGroupColorsAttribute(): array
    {
        $colors = $this->groups->pluck('color')->toArray();
        return !empty($colors) ? $colors : ['#94A3B8'];
    }

    // Keep legacy accessor for backward compatibility if needed, but simplified
    public function getMixedColorAttribute(): string
    {
        $colors = $this->getGroupColorsAttribute();
        if (count($colors) === 1) return $colors[0];
        return 'linear-gradient(90deg, ' . implode(', ', $colors) . ')';
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
