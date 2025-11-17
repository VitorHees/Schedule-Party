<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'name',
        'color',
    ];

    /**
     * Group belongs to a calendar
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Group has many users (many-to-many)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * Group has many events (many-to-many)
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_group')
            ->withTimestamps();
    }

    /**
     * Check if a user belongs to this group
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Add a user to this group
     */
    public function addUser(User $user): void
    {
        if (!$this->hasUser($user)) {
            $this->users()->attach($user->id, [
                'assigned_at' => now(),
            ]);

            $this->calendar->logActivity(
                'joined_group',
                'Group',
                $this->id,
                $user,
                ['group_name' => $this->name]
            );
        }
    }

    /**
     * Remove a user from this group
     */
    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);

        $this->calendar->logActivity(
            'left_group',
            'Group',
            $this->id,
            $user,
            ['group_name' => $this->name]
        );
    }
}
