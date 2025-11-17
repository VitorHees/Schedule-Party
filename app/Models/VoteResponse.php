<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoteResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_option_id',
        'user_id',
    ];

    /**
     * VoteResponse belongs to a vote option
     */
    public function voteOption(): BelongsTo
    {
        return $this->belongsTo(VoteOption::class);
    }

    /**
     * VoteResponse belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
