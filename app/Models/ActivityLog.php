<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    /**
     * ActivityLog belongs to a calendar
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * ActivityLog belongs to a user (nullable for system actions)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable description
     */
    public function getDescriptionAttribute(): string
    {
        $userName = $this->user?->username ?? 'System';

        return match($this->action) {
            'created' => "{$userName} created {$this->resource_type}",
            'updated' => "{$userName} updated {$this->resource_type}",
            'deleted' => "{$userName} deleted {$this->resource_type}",
            'joined' => "{$userName} joined the calendar",
            'left' => "{$userName} left the calendar",
            'joined_group' => "{$userName} joined group {$this->details['group_name']}",
            'left_group' => "{$userName} left group {$this->details['group_name']}",
            'voted' => "{$userName} voted on {$this->resource_type}",
            'commented' => "{$userName} commented on {$this->resource_type}",
            'opted_in' => "{$userName} opted in to {$this->resource_type}",
            'opted_out' => "{$userName} opted out of {$this->resource_type}",
            default => "{$userName} performed {$this->action} on {$this->resource_type}",
        };
    }
}
