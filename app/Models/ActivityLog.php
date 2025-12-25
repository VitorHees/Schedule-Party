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

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDescriptionAttribute(): string
    {
        $userName = $this->user?->username ?? 'System';
        $details = $this->details ?? [];

        // Helper for common name fields
        $resourceName = $details['name'] ?? $this->resource_type;
        $eventName = $details['event_name'] ?? $resourceName;

        return match($this->action) {
            'created' => "{$userName} created {$this->resource_type} \"{$resourceName}\"",
            'updated' => "{$userName} updated {$this->resource_type} \"{$resourceName}\"",
            'deleted' => "{$userName} deleted {$this->resource_type} \"{$resourceName}\"",

            'joined' => "{$userName} joined the calendar",
            'left' => "{$userName} left the calendar",
            'kicked_user' => "{$userName} removed " . ($details['kicked_username'] ?? 'a user') . " from the calendar",

            'invited_user' => "{$userName} invited " . ($details['invited_email'] ?? 'someone') . " as " . ($details['role'] ?? 'member'),
            'generated_link' => "{$userName} generated a new invite link",
            'deleted_invite' => "{$userName} deleted an invitation for " . ($details['email'] ?? 'a user'),

            'joined_group' => "{$userName} joined group \"" . ($details['group_name'] ?? 'Unknown') . "\"",
            'left_group' => "{$userName} left group \"" . ($details['group_name'] ?? 'Unknown') . "\"",
            'assigned_label_to_user' => "{$userName} assigned label \"" . ($details['group_name'] ?? '') . "\" to " . ($details['target_user'] ?? 'user'),
            'removed_label_from_user' => "{$userName} removed label \"" . ($details['group_name'] ?? '') . "\" from " . ($details['target_user'] ?? 'user'),

            'voted' => "{$userName} voted for \"" . (implode(', ', $details['choices'] ?? [])) . "\" in poll \"" . ($details['poll_title'] ?? 'Poll') . "\"",

            'commented' => "{$userName} commented on event \"{$eventName}\"",

            // Updated to show specific content changes
            'updated_comment' => "{$userName} edited their comment \"" . ($details['old_content'] ?? '...') . "\" to \"" . ($details['new_content'] ?? '...') . "\" on \"{$eventName}\"",

            'opted_in' => "{$userName} opted in to event \"{$eventName}\"",
            'opted_out' => "{$userName} opted out of event \"{$eventName}\"",

            'promoted_owner' => "{$userName} promoted a new owner",
            'changed_role' => "{$userName} changed " . ($details['target_user'] ?? 'user') . "'s role to " . ($details['new_role'] ?? 'new role'),

            default => "{$userName} performed {$this->action} on {$this->resource_type}",
        };
    }
}
