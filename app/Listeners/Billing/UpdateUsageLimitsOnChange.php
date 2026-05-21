<?php

namespace App\Listeners\Billing;

use App\Events\Billing\SubscriptionCreated;
use App\Events\Billing\SubscriptionRenewed;
use App\Services\Billing\UsageLimitService;

class UpdateUsageLimitsOnChange
{
    public function __construct(private readonly UsageLimitService $usageLimit) {}

    public function handle(SubscriptionCreated|SubscriptionRenewed $event): void
    {
        $this->usageLimit->syncFromSubscription($event->subscription);
    }
}
