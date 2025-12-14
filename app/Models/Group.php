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
        'is_selectable', // true = Members can have this label, false = Sorting/Event only
        'is_private',    // true = Only owner can assign (if selectable), false = Anyone can join
    ];

    protected function casts(): array
    {
        return [
            'is_selectable' => 'boolean',
            'is_private' => 'boolean',
        ];
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_group')
            ->withTimestamps();
    }
}
