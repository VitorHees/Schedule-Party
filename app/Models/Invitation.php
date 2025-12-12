<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'created_by',
        'invite_type',
        'token',
        'email',
        'role_id',
        'click_count',
        'usage_count', // Added
        'last_clicked_at',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'last_clicked_at' => 'datetime',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'click_count' => 'integer',
            'usage_count' => 'integer', // Added
        ];
    }

    /**
     * Auto-generate token on creation
     */
    protected static function booted(): void
    {
        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(32);
            }
        });
    }

    /**
     * Invitation belongs to a calendar
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Invitation was created by a user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Invitation assigns a role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if invitation is expired
     */
    public function isExpired(): bool
    {
        // If expires_at is null, it never expires
        if (is_null($this->expires_at)) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if invitation has been used
     * Note: For 'link' types that are unlimited, used_at might not be relevant for validity
     */
    public function isUsed(): bool
    {
        // Email invites are one-time use
        if ($this->invite_type === 'email') {
            return !is_null($this->used_at);
        }
        // Links are unlimited by default
        return false;
    }

    /**
     * Check if invitation is valid
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }

    /**
     * Increment click count
     */
    public function incrementClickCount(): void
    {
        $this->increment('click_count');
        $this->update(['last_clicked_at' => now()]);
    }

    /**
     * Increment usage count (successful join)
     */
    public function incrementUsageCount(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Mark invitation as used (mainly for email invites)
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Get invitation URL
     */
    public function getUrlAttribute(): string
    {
        return route('invitations.accept', $this->token);
    }
}
