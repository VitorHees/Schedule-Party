<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermissionOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_user_id',
        'permission_id',
        'granted',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
        ];
    }

    /**
     * Override belongs to a calendar_user
     */
    public function calendarUser(): BelongsTo
    {
        return $this->belongsTo(CalendarUser::class);
    }

    /**
     * Override belongs to a permission
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
