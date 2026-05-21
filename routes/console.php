<?php

use App\Jobs\Analytics\CleanupAnalyticsLogsJob;
use App\Jobs\Notifications\CleanupOldNotificationsJob;
use App\Jobs\Notifications\SendDigestEmailJob;
use App\Jobs\Analytics\ProcessCampaignAnalyticsJob;
use App\Jobs\Analytics\SyncAnalyticsJob;
use App\Jobs\Analytics\UpdateFollowerStatsJob;
use App\Jobs\Billing\ProcessSubscriptionRenewalJob;
use App\Jobs\Billing\RetryFailedPaymentJob;
use App\Jobs\Billing\SendBillingReminderJob;
use App\Jobs\Media\CleanupMediaTempJob;
use App\Jobs\Post\AutoRepostJob;
use App\Jobs\Post\CleanupTempFilesJob;
use App\Jobs\Post\PublishPostJob;
use App\Jobs\Social\CheckExpiredAccountsJob;
use App\Jobs\Social\SyncSocialAccountJob;
use App\Models\AnalyticsCampaign;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Social Account Scheduler ──────────────────────────────────────────

// Check for expired/expiring tokens every 6 hours
// (CheckExpiredAccountsJob internally dispatches RefreshSocialTokenJob on the 'social' queue)
Schedule::job(CheckExpiredAccountsJob::class)->everySixHours();

// Daily full sync of all active accounts (profile + pages)
Schedule::call(function () {
    SocialAccount::with('platform')->active()->chunk(50, function ($accounts) {
        foreach ($accounts as $account) {
            SyncSocialAccountJob::dispatch($account)->onQueue('social');
        }
    });
})->daily()->name('social:sync-all')->withoutOverlapping();

// ── Post Scheduler ────────────────────────────────────────────────────

// Publish due posts every minute
Schedule::call(function () {
    Post::where('status', 'scheduled')
        ->where('scheduled_at', '<=', now())
        ->chunk(20, function ($posts) {
            foreach ($posts as $post) {
                PublishPostJob::dispatch($post)->onQueue('publishing');
            }
        });
})->everyMinute()->name('posts:publish-due')->withoutOverlapping();

// Auto-repost evergreen content daily
Schedule::call(function () {
    Post::evergreen()
        ->dueRepost()
        ->chunk(20, function ($posts) {
            foreach ($posts as $post) {
                AutoRepostJob::dispatch($post)->onQueue('publishing');
            }
        });
})->daily()->name('posts:auto-repost')->withoutOverlapping();

// Cleanup temp uploads daily at 2am
Schedule::job(CleanupTempFilesJob::class)->dailyAt('02:00');

// Cleanup media temp files daily at 3am
Schedule::job(CleanupMediaTempJob::class)->dailyAt('03:00');

// ── Analytics Scheduler ───────────────────────────────────────────────

// Sync post analytics from all platforms every hour
Schedule::call(function () {
    Tenant::where('status', 'active')->pluck('id')->each(function (int $tenantId) {
        SyncAnalyticsJob::dispatch($tenantId)->onQueue(config('analytics.queue', 'analytics'));
    });
})->hourly()->name('analytics:sync-all')->withoutOverlapping();

// Update follower snapshots daily at 4am
Schedule::call(function () {
    Tenant::where('status', 'active')->pluck('id')->each(function (int $tenantId) {
        UpdateFollowerStatsJob::dispatch($tenantId)->onQueue(config('analytics.queue', 'analytics'));
    });
})->dailyAt('04:00')->name('analytics:follower-stats')->withoutOverlapping();

// Process active campaign metrics every 2 hours
Schedule::call(function () {
    AnalyticsCampaign::where('status', 'active')
        ->chunk(50, function ($campaigns) {
            foreach ($campaigns as $campaign) {
                ProcessCampaignAnalyticsJob::dispatch($campaign)
                    ->onQueue(config('analytics.queue', 'analytics'));
            }
        });
})->everyTwoHours()->name('analytics:campaign-metrics')->withoutOverlapping();

// Purge old analytics logs daily at 1am
Schedule::job(new CleanupAnalyticsLogsJob(config('analytics.log_retention_days', 30)))
    ->dailyAt('01:00')
    ->name('analytics:cleanup-logs');

// ── Billing Scheduler ─────────────────────────────────────────────────

// Process expired trials and subscriptions daily at midnight
Schedule::job(ProcessSubscriptionRenewalJob::class)
    ->daily()
    ->name('billing:renewal')
    ->withoutOverlapping();

// Retry past-due subscriptions daily at 9am
Schedule::call(function () {
    Subscription::where('status', 'past_due')
        ->chunk(50, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                RetryFailedPaymentJob::dispatch($subscription->id)->onQueue('billing');
            }
        });
})->dailyAt('09:00')->name('billing:retry-payments')->withoutOverlapping();

// Send trial-expiring reminders (3 days out) daily at 10am
Schedule::call(function () {
    Subscription::where('status', 'trialing')
        ->whereDate('trial_ends_at', now()->addDays(3)->toDateString())
        ->chunk(50, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                SendBillingReminderJob::dispatch($subscription->id, 'trial_expiring')->onQueue('billing');
            }
        });
})->dailyAt('10:00')->name('billing:trial-reminders')->withoutOverlapping();

// Send renewal reminders (3 days out) daily at 10:30am
Schedule::call(function () {
    Subscription::where('status', 'active')
        ->whereNull('trial_ends_at')
        ->whereDate('current_period_end', now()->addDays(3)->toDateString())
        ->chunk(50, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                SendBillingReminderJob::dispatch($subscription->id, 'renewal')->onQueue('billing');
            }
        });
})->dailyAt('10:30')->name('billing:renewal-reminders')->withoutOverlapping();

// ── Notifications Scheduler ───────────────────────────────────────────

// Prune old notifications daily at 3:30am
Schedule::job(new CleanupOldNotificationsJob(config('notifications.retention_days', 60)))
    ->dailyAt('03:30')
    ->name('notifications:cleanup');

// Digest email — daily or weekly depending on config
if (config('notifications.digest_schedule') === 'daily') {
    Schedule::job(new SendDigestEmailJob('daily'))
        ->dailyAt('08:00')
        ->name('notifications:digest-daily');
} elseif (config('notifications.digest_schedule') === 'weekly') {
    Schedule::job(new SendDigestEmailJob('weekly'))
        ->weeklyOn(1, '08:00') // every Monday 8am
        ->name('notifications:digest-weekly');
}
