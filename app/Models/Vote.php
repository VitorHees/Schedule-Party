<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'allow_multiple',
    ];

    protected function casts(): array
    {
        return [
            'allow_multiple' => 'boolean',
        ];
    }

    /**
     * Vote belongs to an event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Vote has many options
     */
    public function options(): HasMany
    {
        return $this->hasMany(VoteOption::class);
    }

    /**
     * Get total vote count
     */
    public function getTotalVotesAttribute(): int
    {
        return $this->options->sum(fn($option) => $option->responses->count());
    }

    /**
     * Check if user has voted
     */
    public function hasUserVoted(User $user): bool
    {
        return VoteResponse::whereIn('vote_option_id', $this->options->pluck('id'))
            ->where('user_id', $user->id)
            ->exists();
    }
}
