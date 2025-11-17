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
        return $this->expires_at->isPast();
    }

    /**
     * Check if invitation has been used
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
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
     * Mark invitation as used
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
