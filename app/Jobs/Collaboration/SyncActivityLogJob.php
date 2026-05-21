<?php

namespace App\Jobs\Collaboration;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncActivityLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(
        private readonly string  $action,
        private readonly string  $module,
        private readonly ?string $description = null,
        private readonly array   $properties  = [],
        private readonly ?int    $tenantId    = null,
        private readonly ?int    $userId      = null,
        private readonly ?string $subjectType = null,
        private readonly ?int    $subjectId   = null,
        private readonly ?string $ipAddress   = null,
        private readonly ?string $userAgent   = null,
    ) {
        $this->onQueue('collaboration');
    }

    public function handle(): void
    {
        ActivityLog::create([
            'tenant_id'    => $this->tenantId,
            'user_id'      => $this->userId,
            'action'       => $this->action,
            'module'       => $this->module,
            'subject_type' => $this->subjectType,
            'subject_id'   => $this->subjectId,
            'description'  => $this->description,
            'properties'   => $this->properties ?: null,
            'ip_address'   => $this->ipAddress,
            'user_agent'   => $this->userAgent,
        ]);
    }
}
