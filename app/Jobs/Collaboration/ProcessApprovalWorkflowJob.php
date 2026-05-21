<?php

namespace App\Jobs\Collaboration;

use App\Models\PostApproval;
use App\Services\Collaboration\PostApprovalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessApprovalWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        private readonly int    $approvalId,
        private readonly string $action,     // 'approve' | 'reject'
        private readonly int    $reviewerId,
        private readonly ?string $comment = null,
    ) {
        $this->onQueue('collaboration');
    }

    public function handle(PostApprovalService $service): void
    {
        $approval = PostApproval::find($this->approvalId);
        if (!$approval || !$approval->isPending()) {
            return;
        }

        match ($this->action) {
            'approve' => $service->approve($approval, $this->reviewerId, $this->comment),
            'reject'  => $service->reject($approval, $this->reviewerId, $this->comment ?? ''),
            default   => null,
        };
    }
}
