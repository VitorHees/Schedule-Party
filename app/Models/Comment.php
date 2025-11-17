<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'content',
        'mentions',
    ];

    protected function casts(): array
    {
        return [
            'mentions' => 'array',
        ];
    }

    /**
     * Comment belongs to an event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Comment belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get mentioned users
     */
    public function getMentionedUsersAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        if (empty($this->mentions)) {
            return collect();
        }

        return User::whereIn('id', $this->mentions)->get();
    }
}
