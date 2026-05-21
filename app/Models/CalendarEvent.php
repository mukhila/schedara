<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CalendarEvent extends Model
{
    protected $fillable = [
        'uuid', 'tenant_id', 'post_id', 'title', 'start_at', 'end_at', 'color', 'platforms', 'status', 'all_day',
    ];

    protected function casts(): array
    {
        return [
            'start_at'  => 'datetime',
            'end_at'    => 'datetime',
            'platforms' => 'array',
            'all_day'   => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    public function post(): BelongsTo   { return $this->belongsTo(Post::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    /** FullCalendar-compatible event shape */
    public function toCalendarArray(): array
    {
        return [
            'id'              => $this->uuid,
            'title'           => $this->title,
            'start'           => $this->start_at->toIso8601String(),
            'end'             => $this->end_at?->toIso8601String(),
            'backgroundColor' => $this->color,
            'borderColor'     => $this->color,
            'textColor'       => '#ffffff',
            'allDay'          => $this->all_day,
            'extendedProps'   => [
                'post_id'   => $this->post?->uuid,
                'status'    => $this->status,
                'platforms' => $this->platforms ?? [],
            ],
        ];
    }
}
