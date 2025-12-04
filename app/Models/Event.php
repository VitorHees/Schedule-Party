<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'created_by',
        'series_id',
        'name',
        'description',
        'is_nsfw', // <--- Add this
        'images',
        'start_date',
        'end_date',
        'is_all_day',
        'location',
        'url',
        'repeat_frequency',
        'repeat_end_date',
        'visibility_rule',
        'max_distance_km',
        'comments_enabled',
        'opt_in_enabled',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'repeat_end_date' => 'date',
            'is_all_day' => 'boolean',
            'is_nsfw' => 'boolean', // <--- Add this
            'comments_enabled' => 'boolean',
            'opt_in_enabled' => 'boolean',
        ];
    }

    // ... rest of your model code (relationships, etc) stays the same
}
