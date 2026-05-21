<?php

namespace App\Jobs\Post;

use App\Models\Post;
use App\Services\Post\PublishingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishPostJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly Post $post) {}

    public function uniqueId(): string
    {
        return "publish-post-{$this->post->id}";
    }

    public function handle(PublishingService $service): void
    {
        if (!in_array($this->post->status, ['scheduled', 'queued'])) {
            return;
        }

        $service->publishPost($this->post);
    }

    public function failed(\Throwable $e): void
    {
        $this->post->update(['status' => 'failed']);
        \App\Models\PostLog::record($this->post, 'job_failed', 'error', ['error' => $e->getMessage()]);
    }
}
