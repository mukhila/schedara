<?php

namespace App\Jobs\Post;

use App\Models\Post;
use App\Services\Post\PublishingService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoRepostJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public readonly Post $post) {}

    public function handle(PublishingService $service): void
    {
        if (!$this->post->auto_repost || !$this->post->is_evergreen) {
            return;
        }

        $this->post->platformConfigs()->update(['status' => 'pending']);
        $service->publishPost($this->post);

        if ($this->post->repost_frequency) {
            $this->post->update([
                'next_repost_at' => Carbon::now()->addDays($this->post->repost_frequency),
            ]);
        }
    }
}
