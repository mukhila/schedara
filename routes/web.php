<?php

use App\Http\Controllers\AI\AiAssistantController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\MicrosoftController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\RazorpayWebhookController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\Dashboard\ExportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Social\SocialAccountController;
use App\Http\Controllers\Cms\CmsController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

// ── Billing webhooks (no CSRF, no auth) ───────────────────────────────
Route::post('/billing/stripe/webhook',   StripeWebhookController::class)->name('billing.stripe.webhook');
Route::post('/billing/razorpay/webhook', RazorpayWebhookController::class)->name('billing.razorpay.webhook');
Route::post('/billing/paypal/webhook',   \App\Http\Controllers\Billing\PaypalWebhookController::class)->name('billing.paypal.webhook');

// ── Marketing ──────────────────────────────────────────────────────────
Route::get('/', fn () => view('frontend.home'))->name('home');

// ── Public media share links ───────────────────────────────────────────
Route::get('/share/{token}', [CmsController::class, 'sharePublic'])->name('cms.share');

// ── Notifications ─────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/notifications',              [\App\Http\Controllers\Notifications\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/preferences',  [\App\Http\Controllers\Notifications\NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::get('/notifications/templates',    [\App\Http\Controllers\Notifications\NotificationController::class, 'templates'])->name('notifications.templates');
});

// ── Notification Slack Integration (requires tenant) ─────────────────
Route::middleware(['auth', 'resolve.tenant'])->group(function () {
    Route::get('/notifications/slack',            [\App\Http\Controllers\Notifications\SlackIntegrationController::class, 'index'])->name('notifications.slack');
    Route::post('/notifications/slack',           [\App\Http\Controllers\Notifications\SlackIntegrationController::class, 'connect'])->name('notifications.slack.connect');
    Route::post('/notifications/slack/test',      [\App\Http\Controllers\Notifications\SlackIntegrationController::class, 'test'])->name('notifications.slack.test');
    Route::delete('/notifications/slack',         [\App\Http\Controllers\Notifications\SlackIntegrationController::class, 'disconnect'])->name('notifications.slack.disconnect');
});

// ── Short link redirect (click tracking) ──────────────────────────────
Route::get('/r/{shortCode}', [\App\Http\Controllers\Analytics\ShortLinkController::class, 'redirect'])->name('analytics.short-link');

// ── Guest-only auth ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    // Unified login/register page
    Route::get('/login',             [LoginController::class, 'showForm'])->name('auth.login');
    Route::post('/login',            [LoginController::class, 'authenticate'])->name('auth.login.post');

    // Redirect /register to the unified login page
    Route::get('/register',          fn () => redirect()->route('auth.login'))->name('auth.register');

    // Passwordless email OTP flow (handles both login & registration)
    Route::post('/auth/email-otp',   [LoginController::class, 'sendEmailOtp'])->name('auth.email-otp');
    Route::post('/auth/check-email', [LoginController::class, 'checkEmail'])->name('auth.check-email');

    // Password reset
    Route::get('/forgot-password',   [PasswordResetController::class, 'showForgotForm'])->name('auth.forgot-password');
    Route::post('/forgot-password',  [PasswordResetController::class, 'sendOtp'])->name('auth.forgot-password.post');
    Route::get('/reset-password',    [PasswordResetController::class, 'showResetForm'])->name('auth.reset-password');
    Route::post('/reset-password',   [PasswordResetController::class, 'reset'])->name('auth.reset-password.post');

    // Social OAuth
    Route::get('/auth/google',            [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback',   [GoogleController::class, 'callback'])->name('auth.google.callback');

    Route::get('/auth/microsoft',         [MicrosoftController::class, 'redirect'])->name('auth.microsoft');
    Route::get('/auth/microsoft/callback',[MicrosoftController::class, 'callback'])->name('auth.microsoft.callback');

    Route::get('/auth/facebook',          [FacebookController::class, 'redirect'])->name('auth.facebook');
    Route::get('/auth/facebook/callback', [FacebookController::class, 'callback'])->name('auth.facebook.callback');
});

// ── OTP verification (session-based, no auth middleware required) ──────
// The controller validates presence of pending_email in session.
Route::get('/verify-email',          [OtpController::class, 'showForm'])->name('auth.verify-email');
Route::post('/verify-email',         [OtpController::class, 'verify'])->name('auth.verify-email.post');
Route::post('/verify-email/resend',  [OtpController::class, 'resend'])->name('auth.verify-email.resend');

// ── MFA challenge (before full session login) ──────────────────────────
Route::get('/mfa/challenge',   [MfaController::class, 'showChallenge'])->name('auth.mfa.challenge');
Route::post('/mfa/challenge',  [MfaController::class, 'verifyChallenge'])->name('auth.mfa.challenge.post');

// ── Team invitations (signed, public) ─────────────────────────────────
Route::get('/invitation/{token}',         [InvitationController::class, 'show'])->name('invitation.show')->middleware('signed');
Route::post('/invitation/{token}/accept', [InvitationController::class, 'accept'])->name('invitation.accept')->middleware('auth');
Route::post('/invitation/{token}/decline',[InvitationController::class, 'decline'])->name('invitation.decline');

// ── Authenticated + email verified ────────────────────────────────────
Route::middleware(['auth', 'auth.email', 'mfa'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');

    // Workspace selector (no tenant binding required)
    Route::get('/workspace',             [WorkspaceController::class, 'select'])->name('workspace.select');
    Route::post('/workspace/{tenantId}', [WorkspaceController::class, 'switchTo'])->name('workspace.switch');

    // MFA setup & sessions (no tenant required)
    Route::get('/settings/2fa',       [MfaController::class, 'showSetup'])->name('auth.mfa.setup');
    Route::post('/settings/2fa',      [MfaController::class, 'enable'])->name('auth.mfa.enable');
    Route::delete('/settings/2fa',    [MfaController::class, 'disable'])->name('auth.mfa.disable');
    Route::get('/settings/sessions',          [SessionController::class, 'index'])->name('auth.sessions');
    Route::delete('/settings/sessions/{id}',  [SessionController::class, 'revoke'])->name('auth.sessions.revoke');
    Route::delete('/settings/sessions',       [SessionController::class, 'revokeAll'])->name('auth.sessions.revoke-all');

    // ── Tenant-scoped routes ──────────────────────────────────────────
    Route::middleware('resolve.tenant')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Dashboard export
        Route::get('/dashboard/export/pdf',   [ExportController::class, 'pdf'])->name('dashboard.export.pdf');
        Route::get('/dashboard/export/excel', [ExportController::class, 'excel'])->name('dashboard.export.excel');

        // Analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/',              [AnalyticsController::class, 'index'])->name('index');
            Route::get('/engagement',    [AnalyticsController::class, 'engagement'])->name('engagement');
            Route::get('/reach',         [AnalyticsController::class, 'reach'])->name('reach');
            Route::get('/followers',     [AnalyticsController::class, 'followers'])->name('followers');
            Route::get('/campaigns',     [AnalyticsController::class, 'campaigns'])->name('campaigns');
            Route::get('/demographics',  [AnalyticsController::class, 'demographics'])->name('demographics');
            Route::get('/roi',           [AnalyticsController::class, 'roi'])->name('roi');
            Route::get('/reports',       [AnalyticsController::class, 'reports'])->name('reports');
            Route::post('/reports',      [AnalyticsController::class, 'createReport'])->name('reports.create');
        });

        // Social Accounts
        Route::get('/social/accounts',                                          [SocialAccountController::class, 'index'])->name('social.index');
        Route::get('/social/connect/{platform}',                                [SocialAccountController::class, 'connect'])->name('social.connect');
        Route::get('/social/callback/{platform}',                               [SocialAccountController::class, 'callback'])->name('social.callback');
        Route::get('/social/accounts/{account}',                                [SocialAccountController::class, 'show'])->name('social.show');
        Route::delete('/social/accounts/{account}',                             [SocialAccountController::class, 'disconnect'])->name('social.disconnect');
        Route::post('/social/accounts/{account}/sync',                          [SocialAccountController::class, 'sync'])->name('social.sync');
        Route::post('/social/accounts/{account}/refresh',                       [SocialAccountController::class, 'refresh'])->name('social.refresh');
        Route::get('/social/accounts/{account}/pages',                          [SocialAccountController::class, 'pages'])->name('social.pages');
        Route::post('/social/accounts/{account}/pages/{page}/toggle',           [SocialAccountController::class, 'togglePage'])->name('social.pages.toggle');

        // CMS / Media Library
        Route::get('/cms',                               [CmsController::class, 'index'])->name('cms.index');
        Route::post('/cms/upload',                       [CmsController::class, 'upload'])->name('cms.upload');
        Route::get('/cms/approvals',                     [CmsController::class, 'approvals'])->name('cms.approvals');
        Route::get('/cms/{uuid}',                        [CmsController::class, 'show'])->name('cms.show');
        Route::delete('/cms/{uuid}',                     [CmsController::class, 'destroy'])->name('cms.destroy');
        Route::post('/cms/{uuid}/favorite',              [CmsController::class, 'toggleFavorite'])->name('cms.favorite');

        // Posts & Scheduler
        Route::get('/posts',                              [PostController::class, 'index'])->name('posts.index');
        Route::get('/posts/create',                       [PostController::class, 'create'])->name('posts.create');
        Route::post('/posts',                             [PostController::class, 'store'])->name('posts.store');
        Route::get('/posts/calendar',                     [PostController::class, 'calendar'])->name('posts.calendar');
        Route::get('/posts/drafts',                       [PostController::class, 'drafts'])->name('posts.drafts');
        Route::post('/posts/bulk-import',                 [PostController::class, 'bulkImport'])->name('posts.bulk-import');
        Route::get('/posts/{uuid}',                       [PostController::class, 'show'])->name('posts.show');
        Route::get('/posts/{uuid}/edit',                  [PostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{uuid}',                       [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{uuid}',                    [PostController::class, 'destroy'])->name('posts.destroy');

        // Team management
        Route::get('/team',                                   [TeamController::class, 'index'])->name('team.index');
        Route::post('/team/invite',                           [TeamController::class, 'invite'])->name('team.invite');
        Route::put('/team/{userId}/role',                     [TeamController::class, 'updateRole'])->name('team.update-role');
        Route::delete('/team/{userId}',                       [TeamController::class, 'removeMember'])->name('team.remove');
        Route::delete('/team/invitations/{invitationId}',    [TeamController::class, 'cancelInvitation'])->name('team.invitations.cancel');
        Route::post('/team/invitations/{invitationId}/resend',[TeamController::class, 'resendInvitation'])->name('team.invitations.resend');

        // AI Marketing Assistant
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/',                    [AiAssistantController::class, 'dashboard'])->name('dashboard');
            Route::get('/chat',                [AiAssistantController::class, 'chat'])->name('chat');
            Route::get('/caption',             [AiAssistantController::class, 'caption'])->name('caption');
            Route::get('/hashtags',            [AiAssistantController::class, 'hashtags'])->name('hashtags');
            Route::get('/content-ideas',       [AiAssistantController::class, 'contentIdeas'])->name('content-ideas');
            Route::get('/seo',                 [AiAssistantController::class, 'seo'])->name('seo');
            Route::get('/ad-copy',             [AiAssistantController::class, 'adCopy'])->name('ad-copy');
            Route::get('/response-suggestions',[AiAssistantController::class, 'responseSuggestions'])->name('response-suggestions');
            Route::get('/campaign',            [AiAssistantController::class, 'campaign'])->name('campaign');
            Route::get('/templates',           [AiAssistantController::class, 'templates'])->name('templates');
            Route::get('/brand-voice',         [AiAssistantController::class, 'brandVoice'])->name('brand-voice');
            Route::get('/usage',               [AiAssistantController::class, 'usage'])->name('usage');
        });

        // ── Collaboration & Workflow ─────────────────────────────────
        Route::prefix('collaboration')->name('collaboration.')->group(function () {
            Route::get('/', \App\Http\Controllers\Collaboration\CollaborationDashboardController::class)->name('dashboard');

            // Tasks (Kanban board)
            Route::get('/tasks',                    [\App\Http\Controllers\Collaboration\TaskBoardController::class, 'index'])->name('tasks.index');
            Route::post('/tasks',                   [\App\Http\Controllers\Collaboration\TaskBoardController::class, 'store'])->name('tasks.store');
            Route::get('/tasks/{uuid}',             [\App\Http\Controllers\Collaboration\TaskBoardController::class, 'show'])->name('tasks.show');
            Route::put('/tasks/{uuid}',             [\App\Http\Controllers\Collaboration\TaskBoardController::class, 'update'])->name('tasks.update');
            Route::delete('/tasks/{uuid}',          [\App\Http\Controllers\Collaboration\TaskBoardController::class, 'destroy'])->name('tasks.destroy');

            // Approval center
            Route::get('/approvals',                [\App\Http\Controllers\Collaboration\ApprovalCenterController::class, 'index'])->name('approvals.index');
            Route::get('/approvals/{uuid}',         [\App\Http\Controllers\Collaboration\ApprovalCenterController::class, 'show'])->name('approvals.show');
            Route::post('/approvals/{uuid}/approve',[\App\Http\Controllers\Collaboration\ApprovalCenterController::class, 'approve'])->name('approvals.approve');
            Route::post('/approvals/{uuid}/reject', [\App\Http\Controllers\Collaboration\ApprovalCenterController::class, 'reject'])->name('approvals.reject');

            // Activity feed
            Route::get('/activity', \App\Http\Controllers\Collaboration\ActivityFeedController::class)->name('activity');
        });

        // Billing
        Route::get('/billing',                   [BillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/plans',             [BillingController::class, 'plans'])->name('billing.plans');
        Route::get('/billing/usage',             [BillingController::class, 'usage'])->name('billing.usage');
        Route::get('/billing/invoices',          [BillingController::class, 'invoices'])->name('billing.invoices');
        Route::get('/billing/invoices/{uuid}/download', [\App\Http\Controllers\Api\Billing\InvoiceApiController::class, 'download'])->name('billing.invoices.download');
        Route::post('/billing/checkout',         [BillingController::class, 'checkout'])->name('billing.checkout');
        Route::post('/billing/portal',           [BillingController::class, 'portal'])->name('billing.portal');
        Route::post('/billing/cancel',           [BillingController::class, 'cancel'])->name('billing.cancel');
        Route::post('/billing/pause',            [BillingController::class, 'pause'])->name('billing.pause');
        Route::post('/billing/resume',           [BillingController::class, 'resume'])->name('billing.resume');
        Route::get('/billing/revenue',           [BillingController::class, 'revenue'])->name('billing.revenue');
        Route::get('/billing/razorpay/callback', [BillingController::class, 'razorpayCallback'])->name('billing.razorpay.callback');
        Route::get('/billing/paypal/callback',   [BillingController::class, 'paypalCallback'])->name('billing.paypal.callback');

        // ── Agency Client Management ─────────────────────────────────
        Route::prefix('agency')->name('agency.')->group(function () {
            Route::get('/',                            [\App\Http\Controllers\Client\AgencyDashboardController::class, 'index'])->name('dashboard');
            Route::get('/clients/create',              [\App\Http\Controllers\Client\AgencyDashboardController::class, 'create'])->name('clients.create');
            Route::get('/clients/{uuid}',              [\App\Http\Controllers\Client\AgencyDashboardController::class, 'show'])->name('clients.show');
            Route::get('/clients/{uuid}/onboarding',   [\App\Http\Controllers\Client\AgencyDashboardController::class, 'onboarding'])->name('clients.onboarding');

            // Client Billing (web views)
            Route::get('/clients/{clientUuid}/billing',        [\App\Http\Controllers\Client\ClientBillingController::class, 'index'])->name('billing.index');
            Route::get('/clients/{clientUuid}/billing/create', [\App\Http\Controllers\Client\ClientBillingController::class, 'create'])->name('billing.create');

            // White-label settings
            Route::get('/workspaces/{workspaceUuid}/white-label', [\App\Http\Controllers\Client\WhiteLabelController::class, 'edit'])->name('white-label.edit');
        });
    });
});

// ── Client Portal (separate auth scope) ──────────────────────────────
Route::middleware(['auth', 'auth.email'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/',                              [\App\Http\Controllers\Client\ClientPortalController::class, 'index'])->name('dashboard');
    Route::get('/workspaces/{workspaceUuid}',    [\App\Http\Controllers\Client\ClientPortalController::class, 'workspace'])->name('workspace');
});

// ── Admin Panel ───────────────────────────────────────────────────────
Route::prefix('schedara/admin')->name('admin.')->group(function () {

    // ── Public: login / logout ────────────────────────────────────
    Route::get('/login',  [\App\Http\Controllers\Admin\Auth\AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Admin\Auth\AdminAuthController::class, 'login'])->name('login.store');
    Route::post('/logout',[\App\Http\Controllers\Admin\Auth\AdminAuthController::class, 'logout'])->name('logout');

    // ── Protected: requires super-admin session ───────────────────
    Route::middleware('auth.admin')->group(function () {

        Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

        // Analytics & Revenue
        Route::get('/analytics', [\App\Http\Controllers\Admin\AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/revenue',   [\App\Http\Controllers\Admin\AdminRevenueController::class, 'index'])->name('revenue.index');

        // Profile & Password
        Route::get('/profile',          [\App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile',          [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');
        Route::post('/password',        [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('password.update');

        // Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',                    [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('index');
            Route::get('/{user}',              [\App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('show');
            Route::post('/{user}/suspend',     [\App\Http\Controllers\Admin\AdminUserController::class, 'suspend'])->name('suspend');
            Route::post('/{user}/activate',    [\App\Http\Controllers\Admin\AdminUserController::class, 'activate'])->name('activate');
            Route::post('/{user}/make-admin',  [\App\Http\Controllers\Admin\AdminUserController::class, 'makeAdmin'])->name('make-admin');
            Route::post('/{user}/revoke-admin',[\App\Http\Controllers\Admin\AdminUserController::class, 'revokeAdmin'])->name('revoke-admin');
            Route::post('/{user}/impersonate', [\App\Http\Controllers\Admin\AdminUserController::class, 'impersonate'])->name('impersonate');
        });
        Route::post('/stop-impersonating', [\App\Http\Controllers\Admin\AdminUserController::class, 'stopImpersonating'])->name('stop-impersonating');

        // Plans
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/',            [\App\Http\Controllers\Admin\AdminPlanController::class, 'index'])->name('index');
            Route::get('/create',      [\App\Http\Controllers\Admin\AdminPlanController::class, 'create'])->name('create');
            Route::post('/',           [\App\Http\Controllers\Admin\AdminPlanController::class, 'store'])->name('store');
            Route::get('/{plan}/edit', [\App\Http\Controllers\Admin\AdminPlanController::class, 'edit'])->name('edit');
            Route::put('/{plan}',      [\App\Http\Controllers\Admin\AdminPlanController::class, 'update'])->name('update');
            Route::delete('/{plan}',   [\App\Http\Controllers\Admin\AdminPlanController::class, 'destroy'])->name('destroy');
        });

        // Subscriptions
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/',                             [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'index'])->name('index');
            Route::get('/{subscription}',               [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'show'])->name('show');
            Route::post('/{subscription}/cancel',       [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'cancel'])->name('cancel');
            Route::post('/{subscription}/extend-trial', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'extendTrial'])->name('extend-trial');
        });

        // Support Tickets
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/',                   [\App\Http\Controllers\Admin\AdminTicketController::class, 'index'])->name('index');
            Route::get('/{ticket}',           [\App\Http\Controllers\Admin\AdminTicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/assign',   [\App\Http\Controllers\Admin\AdminTicketController::class, 'assign'])->name('assign');
            Route::post('/{ticket}/reply',    [\App\Http\Controllers\Admin\AdminTicketController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/status',   [\App\Http\Controllers\Admin\AdminTicketController::class, 'updateStatus'])->name('status');
        });

        // CMS Pages
        Route::prefix('cms')->name('cms.')->group(function () {
            Route::get('/',                    [\App\Http\Controllers\Admin\AdminCmsController::class, 'index'])->name('index');
            Route::get('/create',              [\App\Http\Controllers\Admin\AdminCmsController::class, 'create'])->name('create');
            Route::post('/',                   [\App\Http\Controllers\Admin\AdminCmsController::class, 'store'])->name('store');
            Route::get('/{cmsPage}/edit',      [\App\Http\Controllers\Admin\AdminCmsController::class, 'edit'])->name('edit');
            Route::put('/{cmsPage}',           [\App\Http\Controllers\Admin\AdminCmsController::class, 'update'])->name('update');
            Route::post('/{cmsPage}/publish',  [\App\Http\Controllers\Admin\AdminCmsController::class, 'publish'])->name('publish');
            Route::post('/{cmsPage}/unpublish',[\App\Http\Controllers\Admin\AdminCmsController::class, 'unpublish'])->name('unpublish');
            Route::delete('/{cmsPage}',        [\App\Http\Controllers\Admin\AdminCmsController::class, 'destroy'])->name('destroy');
        });

        // API Integrations
        Route::prefix('api-integrations')->name('api.')->group(function () {
            Route::get('/',                         [\App\Http\Controllers\Admin\AdminApiController::class, 'index'])->name('index');
            Route::post('/',                        [\App\Http\Controllers\Admin\AdminApiController::class, 'store'])->name('store');
            Route::put('/{apiIntegration}',         [\App\Http\Controllers\Admin\AdminApiController::class, 'update'])->name('update');
            Route::post('/{apiIntegration}/health', [\App\Http\Controllers\Admin\AdminApiController::class, 'healthCheck'])->name('health');
            Route::delete('/{apiIntegration}',      [\App\Http\Controllers\Admin\AdminApiController::class, 'destroy'])->name('destroy');
        });

        // Settings & Activity
        Route::get('/settings',  [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.update');
        Route::get('/activity',  [\App\Http\Controllers\Admin\AdminActivityController::class, 'index'])->name('activity.index');
    });
});
