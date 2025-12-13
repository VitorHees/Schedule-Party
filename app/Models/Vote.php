<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'max_allowed_selections',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'max_allowed_selections' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(VoteOption::class);
    }

    public function getTotalVotesAttribute(): int
    {
        // Count unique users who have voted in this poll
        return $this->options->flatMap->responses->pluck('user_id')->unique()->count();
    }

    public function hasUserVoted(User $user): bool
    {
        return VoteResponse::whereIn('vote_option_id', $this->options->pluck('id'))
            ->where('user_id', $user->id)
            ->exists();
    }

    public function userResponses(User $user)
    {
        return VoteResponse::whereIn('vote_option_id', $this->options->pluck('id'))
            ->where('user_id', $user->id)
            ->pluck('vote_option_id')
            ->toArray();
    }
}
