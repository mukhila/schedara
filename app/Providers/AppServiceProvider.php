<?php

namespace App\Providers;

use App\Enums\TenantPermission;
use App\Events\Media\ContentApproved as MediaApproved;
use App\Events\Media\ContentRejected as MediaRejected;
use App\Events\Media\MediaOptimized;
use App\Events\Media\MediaUploaded;
use App\Events\Media\VideoCompressed;
use App\Listeners\Media\LogMediaActivity;
use App\Listeners\Media\SendMediaNotifications;
use App\Listeners\Media\SyncCdnListener;
use App\Events\Post\CaptionGenerated;
use App\Events\Post\MediaProcessed;
use App\Events\Post\PostFailed;
use App\Events\Post\PostPublished;
use App\Events\Post\PostScheduled;
use App\Events\Social\AccountSyncCompleted;
use App\Events\Social\SocialAccountConnected;
use App\Events\Social\SocialAccountDisconnected;
use App\Events\Social\TokenExpired;
use App\Listeners\Post\LogPostActivity;
use App\Listeners\Post\SendPostNotifications;
use App\Events\Analytics\AnalyticsUpdated;
use App\Events\Analytics\CampaignCompleted;
use App\Events\Analytics\ROIThresholdReached;
use App\Events\Analytics\ViralPostDetected;
use App\Listeners\Analytics\SendAnalyticsNotifications;
use App\Listeners\Analytics\TriggerAIAnalysis;
use App\Listeners\Analytics\UpdateAnalyticsCache;
use App\Listeners\Analytics\UpdateDashboard;
use App\Events\AI\AiContentGenerated;
use App\Events\AI\AiLimitReached;
use App\Events\AI\AiProviderFailed;
use App\Events\AI\CampaignGenerated;
use App\Events\Billing\CouponApplied;
use App\Events\Billing\PaymentFailed as BillingPaymentFailed;
use App\Events\Billing\PaymentSuccessful;
use App\Events\Billing\SubscriptionCancelled;
use App\Events\Billing\SubscriptionCreated as BillingSubscriptionCreated;
use App\Events\Billing\SubscriptionRenewed;
use App\Events\Billing\TrialExpired;
use App\Listeners\Billing\GenerateInvoiceOnPayment;
use App\Listeners\Billing\LogBillingEvent;
use App\Listeners\Billing\SendBillingNotification;
use App\Listeners\Billing\UpdateUsageLimitsOnChange;
use App\Events\Client\ClientCreated;
use App\Events\Client\ClientOnboarded;
use App\Events\Client\InvoiceGenerated;
use App\Events\Client\PaymentCompleted;
use App\Events\Client\WhiteLabelUpdated;
use App\Listeners\AI\HandleProviderFailover;
use App\Listeners\AI\NotifyAiLimitReached;
use App\Listeners\AI\TrackAiUsage;
use App\Listeners\Client\GenerateWhiteLabelAssets;
use App\Listeners\Client\LogClientOnboarded;
use App\Listeners\Client\SendClientWelcomeEmail;
use App\Listeners\Client\SendInvoiceEmail;
use App\Listeners\Client\UpdateClientStatus;
use App\Repositories\Client\AgencyClientRepository;
use App\Repositories\Client\ClientBillingRepository;
use App\Repositories\Client\ClientReportRepository;
use App\Repositories\Client\WhiteLabelRepository;
use App\Repositories\Contracts\AgencyClientRepositoryInterface;
use App\Repositories\Contracts\ClientBillingRepositoryInterface;
use App\Repositories\Contracts\ClientReportRepositoryInterface;
use App\Repositories\Contracts\WhiteLabelRepositoryInterface;
use App\Models\Notification;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\View;
use App\Listeners\Social\HandleTokenExpired;
use App\Listeners\Social\LogSocialActivity;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AgencyClientRepositoryInterface::class,  AgencyClientRepository::class);
        $this->app->bind(ClientBillingRepositoryInterface::class,  ClientBillingRepository::class);
        $this->app->bind(ClientReportRepositoryInterface::class,   ClientReportRepository::class);
        $this->app->bind(WhiteLabelRepositoryInterface::class,     WhiteLabelRepository::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('production', 'staging') && ! config('analytics.bitly_api_key')) {
            \Illuminate\Support\Facades\Log::warning(
                'BITLY_API_KEY is not configured. URL shortening will use local /r/{shortCode} redirects. ' .
                'Bitly click analytics will be unavailable in reports.'
            );
        }

        $this->registerSocialiteProviders();
        $this->registerSocialEvents();
        $this->registerPostEvents();
        $this->registerMediaEvents();
        $this->registerAnalyticsEvents();
        $this->registerAiEvents();
        $this->registerClientEvents();
        $this->registerBillingEvents();
        $this->registerTenantGates();
        $this->registerNotificationViewComposer();
    }

    private function registerSocialiteProviders(): void
    {
        Event::listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event): void {
            $event->extendSocialite('microsoft', \SocialiteProviders\Microsoft\Provider::class);
            $event->extendSocialite('pinterest', \SocialiteProviders\Pinterest\Provider::class);
            $event->extendSocialite('threads',   \SocialiteProviders\Threads\Provider::class);
        });
    }

    private function registerSocialEvents(): void
    {
        Event::listen(SocialAccountConnected::class,    [LogSocialActivity::class, 'handleConnected']);
        Event::listen(SocialAccountDisconnected::class, [LogSocialActivity::class, 'handleDisconnected']);
        Event::listen(TokenExpired::class,              [LogSocialActivity::class, 'handleTokenExpired']);
        Event::listen(AccountSyncCompleted::class,      [LogSocialActivity::class, 'handleSyncCompleted']);
        Event::listen(TokenExpired::class,              HandleTokenExpired::class);
    }

    private function registerPostEvents(): void
    {
        Event::listen(PostScheduled::class,    [LogPostActivity::class,        'handleScheduled']);
        Event::listen(PostPublished::class,    [LogPostActivity::class,        'handlePublished']);
        Event::listen(PostFailed::class,       [LogPostActivity::class,        'handleFailed']);
        Event::listen(MediaProcessed::class,   [LogPostActivity::class,        'handleMediaProcessed']);
        Event::listen(CaptionGenerated::class, [LogPostActivity::class,        'handleCaptionGenerated']);

        Event::listen(PostScheduled::class,    [SendPostNotifications::class,  'handleScheduled']);
        Event::listen(PostPublished::class,    [SendPostNotifications::class,  'handlePublished']);
        Event::listen(PostFailed::class,       [SendPostNotifications::class,  'handleFailed']);
    }

    private function registerMediaEvents(): void
    {
        Event::listen(MediaUploaded::class,   [LogMediaActivity::class,    'handleUploaded']);
        Event::listen(MediaOptimized::class,  [LogMediaActivity::class,    'handleOptimized']);
        Event::listen(VideoCompressed::class, [LogMediaActivity::class,    'handleCompressed']);
        Event::listen(MediaApproved::class,   [LogMediaActivity::class,    'handleApproved']);
        Event::listen(MediaRejected::class,   [LogMediaActivity::class,    'handleRejected']);

        Event::listen(MediaUploaded::class,   [SendMediaNotifications::class, 'handleUploaded']);
        Event::listen(MediaApproved::class,   [SendMediaNotifications::class, 'handleApproved']);
        Event::listen(MediaRejected::class,   [SendMediaNotifications::class, 'handleRejected']);

        Event::listen(MediaUploaded::class,   [SyncCdnListener::class, 'handleUploaded']);
        Event::listen(MediaOptimized::class,  [SyncCdnListener::class, 'handleOptimized']);
        Event::listen(VideoCompressed::class, [SyncCdnListener::class, 'handleCompressed']);
        Event::listen(MediaApproved::class,   [SyncCdnListener::class, 'handleApproved']);
    }

    private function registerAnalyticsEvents(): void
    {
        Event::listen(AnalyticsUpdated::class,    [UpdateAnalyticsCache::class,       'handle']);
        Event::listen(AnalyticsUpdated::class,    [TriggerAIAnalysis::class,          'handle']);
        Event::listen(AnalyticsUpdated::class,    [UpdateDashboard::class,            'handle']);
        Event::listen(CampaignCompleted::class,   [SendAnalyticsNotifications::class, 'handleCampaignCompleted']);
        Event::listen(ROIThresholdReached::class,  [SendAnalyticsNotifications::class, 'handleRoiThreshold']);
        Event::listen(ViralPostDetected::class,    [SendAnalyticsNotifications::class, 'handleViralPost']);
    }

    private function registerAiEvents(): void
    {
        Event::listen(AiContentGenerated::class, TrackAiUsage::class);
        Event::listen(AiLimitReached::class,     NotifyAiLimitReached::class);
        Event::listen(AiProviderFailed::class,   HandleProviderFailover::class);
        Event::listen(CampaignGenerated::class,  TrackAiUsage::class);
    }

    private function registerBillingEvents(): void
    {
        Event::listen(BillingSubscriptionCreated::class, [SendBillingNotification::class,   'handleSubscriptionCreated']);
        Event::listen(BillingSubscriptionCreated::class, [UpdateUsageLimitsOnChange::class, 'handle']);
        Event::listen(BillingSubscriptionCreated::class, [LogBillingEvent::class,            'handleSubscriptionCreated']);

        Event::listen(SubscriptionRenewed::class,        [UpdateUsageLimitsOnChange::class, 'handle']);
        Event::listen(SubscriptionRenewed::class,        [LogBillingEvent::class,            'handleSubscriptionRenewed']);

        Event::listen(SubscriptionCancelled::class,      [SendBillingNotification::class,   'handleCancelled']);
        Event::listen(SubscriptionCancelled::class,      [LogBillingEvent::class,            'handleSubscriptionCancelled']);

        Event::listen(PaymentSuccessful::class,          [SendBillingNotification::class,   'handlePaymentSuccessful']);
        Event::listen(PaymentSuccessful::class,          [GenerateInvoiceOnPayment::class,   'handle']);
        Event::listen(PaymentSuccessful::class,          [LogBillingEvent::class,            'handlePaymentSuccessful']);

        Event::listen(BillingPaymentFailed::class,       [SendBillingNotification::class,   'handlePaymentFailed']);
        Event::listen(BillingPaymentFailed::class,       [LogBillingEvent::class,            'handlePaymentFailed']);

        Event::listen(TrialExpired::class,               [SendBillingNotification::class,   'handleTrialExpired']);
        Event::listen(TrialExpired::class,               [LogBillingEvent::class,            'handleTrialExpired']);

        Event::listen(CouponApplied::class,              [LogBillingEvent::class,            'handleCouponApplied']);
    }

    private function registerClientEvents(): void
    {
        Event::listen(ClientCreated::class,   SendClientWelcomeEmail::class);
        Event::listen(ClientOnboarded::class, LogClientOnboarded::class);
        Event::listen(InvoiceGenerated::class, SendInvoiceEmail::class);
        Event::listen(PaymentCompleted::class, UpdateClientStatus::class);
        Event::listen(WhiteLabelUpdated::class, GenerateWhiteLabelAssets::class);
    }

    private function registerNotificationViewComposer(): void
    {
        View::composer('layouts.backend', function ($view) {
            if (!auth()->check()) {
                $view->with(['bellNotifications' => collect(), 'unread' => 0]);
                return;
            }

            $service = app(NotificationService::class);
            $userId  = auth()->id();

            $view->with([
                'bellNotifications' => $service->recent($userId, config('notifications.bell_limit', 8)),
                'unread'            => $service->unreadCount($userId),
            ]);
        });
    }

    private function registerTenantGates(): void
    {
        // Super-admin gate: bypasses all tenant-level checks
        Gate::define('super-admin', fn (User $user) => $user->is_super_admin);

        // Tenant permission gates — one gate per TenantPermission case
        foreach (TenantPermission::cases() as $permission) {
            Gate::define($permission->value, function (User $user) use ($permission): bool {
                // Super admins pass all gates
                if ($user->is_super_admin) {
                    return true;
                }

                // Resolve membership from the container (bound by ResolveTenant middleware)
                if (!app()->bound('current.tenant.id')) {
                    return false;
                }

                /** @var TenantUser|null $membership */
                $membership = app()->bound('current.tenant.membership')
                    ? app('current.tenant.membership')
                    : TenantUser::where('tenant_id', app('current.tenant.id'))
                                ->where('user_id', $user->id)
                                ->whereNotNull('joined_at')
                                ->first();

                return $membership?->hasPermission($permission) ?? false;
            });
        }
    }
}
