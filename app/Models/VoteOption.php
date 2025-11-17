<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoteOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_id',
        'option_text',
    ];

    /**
     * VoteOption belongs to a vote
     */
    public function vote(): BelongsTo
    {
        return $this->belongsTo(Vote::class);
    }

    /**
     * VoteOption has many responses
     */
    public function responses(): HasMany
    {
        return $this->hasMany(VoteResponse::class);
    }

    /**
     * Get vote count for this option
     */
    public function getVoteCountAttribute(): int
    {
        return $this->responses()->count();
    }

    /**
     * Get vote percentage
     */
    public function getPercentageAttribute(): float
    {
        $totalVotes = $this->vote->total_votes;

        if ($totalVotes === 0) {
            return 0;
        }

        return round(($this->vote_count / $totalVotes) * 100, 1);
    }

    /**
     * Check if user voted for this option
     */
    public function hasUserVoted(User $user): bool
    {
        return $this->responses()
            ->where('user_id', $user->id)
            ->exists();
    }
}
