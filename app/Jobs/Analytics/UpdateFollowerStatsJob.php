<?php

namespace App\Jobs\Analytics;

use App\Models\AccountAnalytic;
use App\Models\SocialAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateFollowerStatsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly int  $tenantId,
        public readonly ?int $accountId = null,
    ) {}

    public function uniqueId(): string
    {
        return "update-follower-stats-{$this->tenantId}-{$this->accountId}";
    }

    public function handle(): void
    {
        SocialAccount::where('tenant_id', $this->tenantId)
            ->when($this->accountId, fn ($q) => $q->where('id', $this->accountId))
            ->where('status', 'active')
            ->each(function (SocialAccount $account) {
                $snapshot = $this->fetchFollowerSnapshot($account);

                AccountAnalytic::updateOrCreate(
                    ['social_account_id' => $account->id, 'date' => today()->toDateString()],
                    array_merge($snapshot, ['tenant_id' => $this->tenantId])
                );
            });
    }

    private function fetchFollowerSnapshot(SocialAccount $account): array
    {
        // Stub — replace with real platform API calls
        return [
            'followers'    => $account->followers_count ?? 0,
            'following'    => $account->following_count ?? 0,
            'posts_count'  => 0,
            'profile_views'=> 0,
        ];
    }
}
