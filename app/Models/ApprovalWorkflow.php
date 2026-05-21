<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflow extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'post_id',
        'tenant_id',
        'stages',
        'current_stage',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'stages'        => 'array',
            'current_stage' => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function currentStageData(): ?array
    {
        return collect($this->stages)
            ->firstWhere('stage', $this->current_stage);
    }

    public function approveCurrentStage(int $approverId, ?string $comment = null): void
    {
        $stages = $this->stages;

        foreach ($stages as &$stage) {
            if ($stage['stage'] === $this->current_stage) {
                $stage['approved_at'] = now()->toISOString();
                $stage['approver_id'] = $approverId;
                $stage['comment']     = $comment;
                break;
            }
        }

        $nextStage  = $this->current_stage + 1;
        $hasMore    = collect($stages)->contains('stage', $nextStage);
        $newStatus  = $hasMore ? 'in_review' : 'approved';

        $this->update([
            'stages'        => $stages,
            'current_stage' => $hasMore ? $nextStage : $this->current_stage,
            'status'        => $newStatus,
        ]);

        if (! $hasMore) {
            $this->post->update(['status' => Post::STATUS_APPROVED]);
        }
    }

    public function reject(int $rejectorId, string $reason): void
    {
        $this->update(['status' => 'rejected']);
        $this->post->update([
            'status'         => Post::STATUS_FAILED,
            'failure_reason' => "Rejected at stage {$this->current_stage}: {$reason}",
        ]);
    }

    public function isComplete(): bool
    {
        return $this->status === 'approved';
    }
}
