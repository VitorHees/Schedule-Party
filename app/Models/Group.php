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
        'is_selectable',
        'is_self_assignable', // Keeping this if you ran the previous migration, otherwise 'is_selectable' covers the concept
    ];

    protected function casts(): array
    {
        return [
            'is_selectable' => 'boolean',
            'is_self_assignable' => 'boolean',
        ];
    }

    /**
     * Group belongs to a calendar
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Group has many users (members)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * Group belongs to many events
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_group')
            ->withTimestamps();
    }
}
