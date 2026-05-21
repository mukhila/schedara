<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AdminActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id', 'action', 'module', 'description',
        'subject_type', 'subject_id',
        'ip_address', 'user_agent', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ── Factory ──────────────────────────────────────────────────

    public static function record(
        string   $action,
        string   $module,
        string   $description,
        ?object  $subject     = null,
        array    $metadata    = [],
        ?Request $request     = null,
    ): self {
        $req = $request ?? request();

        return static::create([
            'admin_id'     => auth()->id(),
            'action'       => $action,
            'module'       => $module,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
            'ip_address'   => $req->ip(),
            'user_agent'   => $req->userAgent(),
            'metadata'     => $metadata ?: null,
            'created_at'   => now(),
        ]);
    }
}
