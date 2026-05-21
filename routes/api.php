<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ── Public auth endpoints ─────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register',         [AuthController::class, 'register']);
    Route::post('/verify-otp',       [AuthController::class, 'verifyOtp']);
    Route::post('/login',            [AuthController::class, 'login']);
    Route::post('/forgot-password',  [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',   [AuthController::class, 'resetPassword']);
});

// ── MFA verify (requires mfa:verify ability) ──────────────────────────
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/mfa/verify', [AuthController::class, 'mfaVerify']);
});

// ── Authenticated endpoints ───────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout',               [AuthController::class, 'logout']);
    Route::get('/sessions',              [AuthController::class, 'sessions']);
    Route::delete('/sessions/{id}',      [AuthController::class, 'revokeSession']);
    Route::delete('/sessions',           [AuthController::class, 'revokeAllSessions']);
});

// ── Social Accounts API ───────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('social')->group(function () {
    Route::get('/platforms',                          [\App\Http\Controllers\API\Social\SocialPlatformController::class,   'index']);
    Route::get('/accounts',                           [\App\Http\Controllers\API\Social\SocialAccountApiController::class, 'index']);
    Route::get('/accounts/{account}',                 [\App\Http\Controllers\API\Social\SocialAccountApiController::class, 'show']);
    Route::delete('/accounts/{account}',              [\App\Http\Controllers\API\Social\SocialAccountApiController::class, 'destroy']);
    Route::post('/accounts/{account}/refresh',        [\App\Http\Controllers\API\Social\SocialAccountApiController::class, 'refresh']);
    Route::post('/accounts/{account}/sync',           [\App\Http\Controllers\API\Social\SocialAccountApiController::class, 'sync']);
    Route::get('/accounts/{account}/pages',           [\App\Http\Controllers\API\Social\SocialAccountApiController::class, 'pages']);
});

// ── CMS / Media Library API ──────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('media')->group(function () {
    Route::get('/stats',                         [\App\Http\Controllers\Api\Media\MediaApiController::class, 'stats']);
    Route::get('/search',                        [\App\Http\Controllers\Api\Media\MediaApiController::class, 'search']);
    Route::get('/duplicates',                    [\App\Http\Controllers\Api\Media\MediaApiController::class, 'duplicates']);
    Route::get('/',                              [\App\Http\Controllers\Api\Media\MediaApiController::class, 'index']);
    Route::post('/upload',                       [\App\Http\Controllers\Api\Media\MediaApiController::class, 'upload']);
    Route::post('/move',                         [\App\Http\Controllers\Api\Media\MediaApiController::class, 'move']);
    Route::get('/{uuid}',                        [\App\Http\Controllers\Api\Media\MediaApiController::class, 'show']);
    Route::put('/{uuid}',                        [\App\Http\Controllers\Api\Media\MediaApiController::class, 'update']);
    Route::delete('/{uuid}',                     [\App\Http\Controllers\Api\Media\MediaApiController::class, 'destroy']);
    Route::post('/{uuid}/optimize',              [\App\Http\Controllers\Api\Media\MediaApiController::class, 'optimize']);
    Route::post('/{uuid}/compress',              [\App\Http\Controllers\Api\Media\MediaApiController::class, 'compress']);
    Route::post('/{uuid}/tag',                   [\App\Http\Controllers\Api\Media\MediaApiController::class, 'tag']);
    Route::post('/{uuid}/favorite',              [\App\Http\Controllers\Api\Media\MediaApiController::class, 'toggleFavorite']);
    Route::post('/{uuid}/share-link',            [\App\Http\Controllers\Api\Media\MediaApiController::class, 'shareLink']);
    Route::post('/{uuid}/ai-tag',                [\App\Http\Controllers\Api\Media\MediaApiController::class, 'aiTag']);
    Route::post('/{uuid}/request-approval',      [\App\Http\Controllers\Api\Media\MediaApiController::class, 'requestApproval']);
    Route::post('/{uuid}/approve',               [\App\Http\Controllers\Api\Media\MediaApiController::class, 'approve']);
    Route::post('/{uuid}/reject',                [\App\Http\Controllers\Api\Media\MediaApiController::class, 'reject']);

    // Folders
    Route::get('/folders/tree',                  [\App\Http\Controllers\Api\Media\MediaFolderApiController::class, 'index']);
    Route::post('/folders',                      [\App\Http\Controllers\Api\Media\MediaFolderApiController::class, 'store']);
    Route::put('/folders/{uuid}',                [\App\Http\Controllers\Api\Media\MediaFolderApiController::class, 'update']);
    Route::delete('/folders/{uuid}',             [\App\Http\Controllers\Api\Media\MediaFolderApiController::class, 'destroy']);
    Route::post('/folders/{uuid}/move',          [\App\Http\Controllers\Api\Media\MediaFolderApiController::class, 'move']);

    // Tags
    Route::get('/tags',                          [\App\Http\Controllers\Api\Media\MediaTagApiController::class, 'index']);
    Route::get('/tags/suggestions',              [\App\Http\Controllers\Api\Media\MediaTagApiController::class, 'suggestions']);

    // Version management
    Route::get('/{uuid}/versions',               [\App\Http\Controllers\Api\Media\MediaVersionApiController::class, 'index']);
    Route::post('/{uuid}/versions',              [\App\Http\Controllers\Api\Media\MediaVersionApiController::class, 'store']);
    Route::post('/{uuid}/versions/{version}/restore', [\App\Http\Controllers\Api\Media\MediaVersionApiController::class, 'restore']);

    // Bulk operations
    Route::prefix('bulk')->group(function () {
        Route::post('/delete',    [\App\Http\Controllers\Api\Media\MediaBulkApiController::class, 'delete']);
        Route::post('/move',      [\App\Http\Controllers\Api\Media\MediaBulkApiController::class, 'move']);
        Route::post('/tag',       [\App\Http\Controllers\Api\Media\MediaBulkApiController::class, 'tag']);
        Route::post('/approve',   [\App\Http\Controllers\Api\Media\MediaBulkApiController::class, 'approve']);
        Route::post('/favorite',  [\App\Http\Controllers\Api\Media\MediaBulkApiController::class, 'favorite']);
        Route::post('/duplicates',[\App\Http\Controllers\Api\Media\MediaBulkApiController::class, 'duplicate']);
    });
});

// ── Post Scheduler API ────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('posts')->group(function () {
    Route::get('/',                                  [\App\Http\Controllers\API\Post\PostApiController::class,    'index']);
    Route::post('/',                                 [\App\Http\Controllers\API\Post\PostApiController::class,    'store']);
    Route::get('/bulk/sample-csv',                   [\App\Http\Controllers\API\Post\PostApiController::class,    'sampleCsv']);
    Route::post('/bulk/import',                      [\App\Http\Controllers\API\Post\PostApiController::class,    'bulkImport']);
    Route::get('/{uuid}',                            [\App\Http\Controllers\API\Post\PostApiController::class,    'show']);
    Route::put('/{uuid}',                            [\App\Http\Controllers\API\Post\PostApiController::class,    'update']);
    Route::delete('/{uuid}',                         [\App\Http\Controllers\API\Post\PostApiController::class,    'destroy']);
    Route::post('/{uuid}/duplicate',                 [\App\Http\Controllers\API\Post\PostApiController::class,    'duplicate']);
    Route::post('/{uuid}/schedule',                  [\App\Http\Controllers\API\Post\PostApiController::class,    'schedule']);
    Route::post('/{uuid}/cancel-schedule',           [\App\Http\Controllers\API\Post\PostApiController::class,    'cancelSchedule']);

    // Media
    Route::post('/{uuid}/media',                     [\App\Http\Controllers\API\Post\MediaApiController::class,  'upload']);
    Route::delete('/{uuid}/media/{mediaUuid}',       [\App\Http\Controllers\API\Post\MediaApiController::class,  'destroy']);
    Route::put('/{uuid}/media/reorder',              [\App\Http\Controllers\API\Post\MediaApiController::class,  'reorder']);
    Route::post('/{uuid}/media/{mediaUuid}/watermark',[\App\Http\Controllers\API\Post\MediaApiController::class, 'applyWatermark']);
});

// ── Calendar API ──────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('calendar')->group(function () {
    Route::get('/events', [\App\Http\Controllers\API\Post\CalendarApiController::class, 'events']);
});

// ── AI Caption API ────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('ai')->group(function () {
    Route::post('/caption',           [\App\Http\Controllers\API\Post\AiCaptionApiController::class, 'generate']);
    Route::post('/hashtags',          [\App\Http\Controllers\API\Post\AiCaptionApiController::class, 'hashtags']);
    Route::get('/best-time',          [\App\Http\Controllers\API\Post\AiCaptionApiController::class, 'bestTime']);
});

// ── Hashtag API ───────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('hashtags')->group(function () {
    Route::get('/suggestions',        [\App\Http\Controllers\API\Post\HashtagApiController::class, 'suggestions']);
    Route::get('/trending',           [\App\Http\Controllers\API\Post\HashtagApiController::class, 'trending']);
    Route::get('/groups',             [\App\Http\Controllers\API\Post\HashtagApiController::class, 'groups']);
    Route::get('/groups/{group}',     [\App\Http\Controllers\API\Post\HashtagApiController::class, 'byGroup']);
});

// ── Analytics API ─────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('analytics')->group(function () {
    // Dashboard overview
    Route::get('/overview',              [\App\Http\Controllers\Api\Analytics\AnalyticsDashboardApiController::class, 'overview']);
    Route::get('/predict',               [\App\Http\Controllers\Api\Analytics\AnalyticsDashboardApiController::class, 'predict']);
    Route::get('/viral',                 [\App\Http\Controllers\Api\Analytics\AnalyticsDashboardApiController::class, 'viral']);

    // Engagement
    Route::get('/engagement',            [\App\Http\Controllers\Api\Analytics\EngagementApiController::class, 'summary']);
    Route::get('/engagement/top-posts',  [\App\Http\Controllers\Api\Analytics\EngagementApiController::class, 'topPosts']);
    Route::get('/engagement/platforms',  [\App\Http\Controllers\Api\Analytics\EngagementApiController::class, 'byPlatform']);

    // Followers
    Route::get('/followers',             [\App\Http\Controllers\Api\Analytics\FollowerApiController::class, 'summary']);
    Route::get('/followers/growth-rate', [\App\Http\Controllers\Api\Analytics\FollowerApiController::class, 'growthRate']);

    // Campaigns
    Route::get('/campaigns',             [\App\Http\Controllers\Api\Analytics\CampaignApiController::class, 'index']);
    Route::get('/campaigns/summary',     [\App\Http\Controllers\Api\Analytics\CampaignApiController::class, 'summary']);
    Route::get('/campaigns/top',         [\App\Http\Controllers\Api\Analytics\CampaignApiController::class, 'topPerformers']);
    Route::post('/campaigns',            [\App\Http\Controllers\Api\Analytics\CampaignApiController::class, 'store']);
    Route::put('/campaigns/{uuid}',      [\App\Http\Controllers\Api\Analytics\CampaignApiController::class, 'update']);

    // Demographics
    Route::get('/demographics',          [\App\Http\Controllers\Api\Analytics\DemographicsApiController::class, 'summary']);

    // ROI
    Route::get('/roi',                   [\App\Http\Controllers\Api\Analytics\RoiApiController::class, 'summary']);
    Route::get('/roi/platforms',         [\App\Http\Controllers\Api\Analytics\RoiApiController::class, 'byPlatform']);

    // Reports
    Route::get('/reports',               [\App\Http\Controllers\Api\Analytics\ReportApiController::class, 'index']);
    Route::post('/reports',              [\App\Http\Controllers\Api\Analytics\ReportApiController::class, 'store']);
    Route::get('/reports/{uuid}',        [\App\Http\Controllers\Api\Analytics\ReportApiController::class, 'show']);
    Route::delete('/reports/{uuid}',     [\App\Http\Controllers\Api\Analytics\ReportApiController::class, 'destroy']);

    // Conversions
    Route::get('/conversions',           [\App\Http\Controllers\Api\Analytics\ConversionsApiController::class, 'index']);

    // Analytics Accounts
    Route::get('/accounts',              [\App\Http\Controllers\Api\Analytics\AnalyticsAccountApiController::class, 'index']);
    Route::post('/accounts',             [\App\Http\Controllers\Api\Analytics\AnalyticsAccountApiController::class, 'store']);
    Route::get('/accounts/{uuid}',       [\App\Http\Controllers\Api\Analytics\AnalyticsAccountApiController::class, 'show']);
    Route::delete('/accounts/{uuid}',    [\App\Http\Controllers\Api\Analytics\AnalyticsAccountApiController::class, 'destroy']);
    Route::get('/accounts/{uuid}/metrics', [\App\Http\Controllers\Api\Analytics\AnalyticsAccountApiController::class, 'metrics']);
});

// ── Notifications API ─────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/',                [\App\Http\Controllers\Api\Notifications\NotificationApiController::class, 'index']);
    Route::get('/unread-count',    [\App\Http\Controllers\Api\Notifications\NotificationApiController::class, 'unreadCount']);
    Route::post('/read-all',       [\App\Http\Controllers\Api\Notifications\NotificationApiController::class, 'markAllRead']);
    Route::delete('/all',          [\App\Http\Controllers\Api\Notifications\NotificationApiController::class, 'clearAll']);
    Route::patch('/{id}/read',     [\App\Http\Controllers\Api\Notifications\NotificationApiController::class, 'markRead']);
    Route::delete('/{id}',         [\App\Http\Controllers\Api\Notifications\NotificationApiController::class, 'destroy']);

    // Preferences
    Route::get('/preferences',     [\App\Http\Controllers\Api\Notifications\NotificationPreferenceApiController::class, 'show']);
    Route::put('/preferences',     [\App\Http\Controllers\Api\Notifications\NotificationPreferenceApiController::class, 'update']);

    // Contact numbers (SMS/WhatsApp)
    Route::get('/contacts',        [\App\Http\Controllers\Api\Notifications\NotificationContactApiController::class, 'show']);
    Route::put('/contacts',        [\App\Http\Controllers\Api\Notifications\NotificationContactApiController::class, 'update']);

    // Device tokens (FCM push)
    Route::post('/device-token',           [\App\Http\Controllers\Api\Notifications\DeviceTokenApiController::class, 'store']);
    Route::delete('/device-token/{token}', [\App\Http\Controllers\Api\Notifications\DeviceTokenApiController::class, 'destroy']);

    // Send (programmatic dispatch)
    Route::post('/send',           [\App\Http\Controllers\Api\Notifications\NotificationSendApiController::class, 'send']);

    // Templates
    Route::get('/templates',                              [\App\Http\Controllers\Api\Notifications\NotificationTemplateApiController::class, 'index']);
    Route::post('/templates',                             [\App\Http\Controllers\Api\Notifications\NotificationTemplateApiController::class, 'store']);
    Route::get('/templates/{notificationTemplate}',       [\App\Http\Controllers\Api\Notifications\NotificationTemplateApiController::class, 'show']);
    Route::put('/templates/{notificationTemplate}',       [\App\Http\Controllers\Api\Notifications\NotificationTemplateApiController::class, 'update']);
    Route::delete('/templates/{notificationTemplate}',    [\App\Http\Controllers\Api\Notifications\NotificationTemplateApiController::class, 'destroy']);
    Route::post('/templates/{notificationTemplate}/preview', [\App\Http\Controllers\Api\Notifications\NotificationTemplateApiController::class, 'preview']);

    // Analytics
    Route::get('/analytics',       [\App\Http\Controllers\Api\Notifications\NotificationAnalyticsApiController::class, 'stats']);
});

// ── Dashboard Widget API ──────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('dashboard')->group(function () {
    // Layout
    Route::get('/layout',         [\App\Http\Controllers\Api\Dashboard\LayoutApiController::class, 'show']);
    Route::put('/layout',         [\App\Http\Controllers\Api\Dashboard\LayoutApiController::class, 'update']);
    Route::post('/layout/reset',  [\App\Http\Controllers\Api\Dashboard\LayoutApiController::class, 'reset']);

    // Widget data endpoints
    Route::prefix('widgets')->group(function () {
        Route::get('/engagement',          [\App\Http\Controllers\Api\Dashboard\WidgetApiController::class, 'engagement']);
        Route::get('/followers',           [\App\Http\Controllers\Api\Dashboard\WidgetApiController::class, 'followers']);
        Route::get('/post-performance',    [\App\Http\Controllers\Api\Dashboard\WidgetApiController::class, 'postPerformance']);
        Route::get('/platform-comparison', [\App\Http\Controllers\Api\Dashboard\WidgetApiController::class, 'platformComparison']);
        Route::get('/revenue',             [\App\Http\Controllers\Api\Dashboard\WidgetApiController::class, 'revenue']);
        Route::get('/ai-insights',         [\App\Http\Controllers\Api\Dashboard\WidgetApiController::class, 'aiInsights']);
    });
});

// ── AI Assistant API ──────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('ai/assistant')->group(function () {
    // Generation endpoints
    Route::post('/caption',               [\App\Http\Controllers\Api\AI\AiGenerateApiController::class, 'caption']);
    Route::post('/hashtags',              [\App\Http\Controllers\Api\AI\AiGenerateApiController::class, 'hashtags']);
    Route::post('/content-ideas',         [\App\Http\Controllers\Api\AI\AiGenerateApiController::class, 'contentIdeas']);
    Route::post('/seo-optimize',          [\App\Http\Controllers\Api\AI\AiGenerateApiController::class, 'seoOptimize']);
    Route::post('/ad-copy',               [\App\Http\Controllers\Api\AI\AiGenerateApiController::class, 'adCopy']);
    Route::post('/response-suggestions',  [\App\Http\Controllers\Api\AI\AiGenerateApiController::class, 'responseSuggestions']);
    Route::post('/campaign',              [\App\Http\Controllers\Api\AI\AiGenerateApiController::class, 'campaign']);

    // Conversations (chat)
    Route::get('/conversations',                     [\App\Http\Controllers\Api\AI\AiConversationApiController::class, 'index']);
    Route::post('/conversations',                    [\App\Http\Controllers\Api\AI\AiConversationApiController::class, 'store']);
    Route::get('/conversations/{uuid}',              [\App\Http\Controllers\Api\AI\AiConversationApiController::class, 'show']);
    Route::post('/conversations/{uuid}/messages',    [\App\Http\Controllers\Api\AI\AiConversationApiController::class, 'message']);
    Route::delete('/conversations/{uuid}',           [\App\Http\Controllers\Api\AI\AiConversationApiController::class, 'destroy']);

    // Templates
    Route::get('/templates',         [\App\Http\Controllers\Api\AI\AiTemplateApiController::class, 'index']);
    Route::post('/templates',        [\App\Http\Controllers\Api\AI\AiTemplateApiController::class, 'store']);
    Route::put('/templates/{uuid}',    [\App\Http\Controllers\Api\AI\AiTemplateApiController::class, 'update']);
    Route::delete('/templates/{uuid}', [\App\Http\Controllers\Api\AI\AiTemplateApiController::class, 'destroy']);

    // Brand voices
    Route::get('/brand-voices',              [\App\Http\Controllers\Api\AI\AiBrandVoiceApiController::class, 'index']);
    Route::post('/brand-voices',             [\App\Http\Controllers\Api\AI\AiBrandVoiceApiController::class, 'store']);
    Route::post('/brand-voices/analyze',     [\App\Http\Controllers\Api\AI\AiBrandVoiceApiController::class, 'analyze']);
    Route::put('/brand-voices/{uuid}',       [\App\Http\Controllers\Api\AI\AiBrandVoiceApiController::class, 'update']);
    Route::delete('/brand-voices/{uuid}',    [\App\Http\Controllers\Api\AI\AiBrandVoiceApiController::class, 'destroy']);

    // Usage
    Route::get('/usage',         [\App\Http\Controllers\Api\AI\AiUsageApiController::class, 'show']);
    Route::get('/usage/recent',  [\App\Http\Controllers\Api\AI\AiUsageApiController::class, 'recent']);
});

// ── Team API (requires auth + tenant header) ──────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('team')->group(function () {
    Route::get('/',                           [\App\Http\Controllers\TeamController::class, 'index']);
    Route::post('/invite',                    [\App\Http\Controllers\TeamController::class, 'invite']);
    Route::put('/{userId}/role',              [\App\Http\Controllers\TeamController::class, 'updateRole']);
    Route::delete('/{userId}',                [\App\Http\Controllers\TeamController::class, 'removeMember']);
    Route::delete('/invitations/{id}',        [\App\Http\Controllers\TeamController::class, 'cancelInvitation']);
    Route::post('/invitations/{id}/resend',   [\App\Http\Controllers\TeamController::class, 'resendInvitation']);
});

// ── Collaboration API ─────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('collaboration')->group(function () {

    // Tasks
    Route::get('/tasks',                  [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'index']);
    Route::post('/tasks',                 [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'store']);
    Route::get('/tasks/kanban',           [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'kanban']);
    Route::get('/tasks/stats',            [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'stats']);
    Route::post('/tasks/reorder',         [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'reorder']);
    Route::get('/tasks/{uuid}',           [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'show']);
    Route::put('/tasks/{uuid}',           [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'update']);
    Route::delete('/tasks/{uuid}',        [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'destroy']);
    Route::get('/tasks/{uuid}/comments',  [\App\Http\Controllers\Api\Collaboration\TaskApiController::class, 'comments']);

    // Post approvals
    Route::post('/approvals/request',       [\App\Http\Controllers\Api\Collaboration\PostApprovalApiController::class, 'request']);
    Route::get('/approvals',                [\App\Http\Controllers\Api\Collaboration\PostApprovalApiController::class, 'index']);
    Route::get('/approvals/pending',        [\App\Http\Controllers\Api\Collaboration\PostApprovalApiController::class, 'pending']);
    Route::get('/approvals/stats',          [\App\Http\Controllers\Api\Collaboration\PostApprovalApiController::class, 'stats']);
    Route::get('/approvals/{uuid}',         [\App\Http\Controllers\Api\Collaboration\PostApprovalApiController::class, 'show']);
    Route::post('/approvals/{uuid}/approve',[\App\Http\Controllers\Api\Collaboration\PostApprovalApiController::class, 'approve']);
    Route::post('/approvals/{uuid}/reject', [\App\Http\Controllers\Api\Collaboration\PostApprovalApiController::class, 'reject']);

    // Comments
    Route::post('/posts/{postId}/comments',       [\App\Http\Controllers\Api\Collaboration\InternalCommentApiController::class, 'storeForPost']);
    Route::get('/posts/{postId}/comments',        [\App\Http\Controllers\Api\Collaboration\InternalCommentApiController::class, 'forPost']);
    Route::post('/tasks/{taskUuid}/comments',     [\App\Http\Controllers\Api\Collaboration\InternalCommentApiController::class, 'storeForTask']);
    Route::get('/tasks/{taskUuid}/comments',      [\App\Http\Controllers\Api\Collaboration\InternalCommentApiController::class, 'forTask']);
    Route::put('/comments/{uuid}',                [\App\Http\Controllers\Api\Collaboration\InternalCommentApiController::class, 'update']);
    Route::delete('/comments/{uuid}',             [\App\Http\Controllers\Api\Collaboration\InternalCommentApiController::class, 'destroy']);
    Route::post('/comments/{uuid}/react',         [\App\Http\Controllers\Api\Collaboration\InternalCommentApiController::class, 'react']);

    // Activity logs
    Route::get('/activity',        [\App\Http\Controllers\Api\Collaboration\ActivityLogApiController::class, 'index']);
    Route::get('/activity/recent', [\App\Http\Controllers\Api\Collaboration\ActivityLogApiController::class, 'recent']);

    // ── Social Inbox ──────────────────────────────────────────────────────
    Route::prefix('inbox')->group(function () {
        Route::get('/',              [\App\Http\Controllers\Api\Social\InboxApiController::class, 'index']);
        Route::post('/sync',         [\App\Http\Controllers\Api\Social\InboxApiController::class, 'sync']);
        Route::post('/{id}/read',    [\App\Http\Controllers\Api\Social\InboxApiController::class, 'markRead']);
        Route::post('/{id}/reply',   [\App\Http\Controllers\Api\Social\InboxApiController::class, 'reply']);
        Route::post('/{id}/archive', [\App\Http\Controllers\Api\Social\InboxApiController::class, 'archive']);
        Route::post('/{id}/assign',  [\App\Http\Controllers\Api\Social\InboxApiController::class, 'assign']);
    });
});

// ── Agency Client Management API ─────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('clients')->group(function () {
    // Agency-level stats
    Route::get('/stats', [\App\Http\Controllers\Api\Client\AgencyClientApiController::class, 'stats']);

    // CRUD
    Route::get('/',             [\App\Http\Controllers\Api\Client\AgencyClientApiController::class, 'index']);
    Route::post('/',            [\App\Http\Controllers\Api\Client\AgencyClientApiController::class, 'store']);
    Route::get('/{uuid}',       [\App\Http\Controllers\Api\Client\AgencyClientApiController::class, 'show']);
    Route::put('/{uuid}',       [\App\Http\Controllers\Api\Client\AgencyClientApiController::class, 'update']);
    Route::delete('/{uuid}',    [\App\Http\Controllers\Api\Client\AgencyClientApiController::class, 'destroy']);

    // Onboarding
    Route::get('/{uuid}/onboarding',            [\App\Http\Controllers\Api\Client\ClientOnboardingApiController::class, 'show']);
    Route::post('/{uuid}/onboarding/complete',  [\App\Http\Controllers\Api\Client\ClientOnboardingApiController::class, 'completeStep']);
    Route::post('/{uuid}/onboarding/skip',      [\App\Http\Controllers\Api\Client\ClientOnboardingApiController::class, 'skipStep']);

    // Billing
    Route::get('/billing/revenue',                    [\App\Http\Controllers\Api\Client\ClientBillingApiController::class, 'revenue']);
    Route::get('/{clientUuid}/billing',               [\App\Http\Controllers\Api\Client\ClientBillingApiController::class, 'index']);
    Route::post('/{clientUuid}/billing',              [\App\Http\Controllers\Api\Client\ClientBillingApiController::class, 'store']);
    Route::get('/billing/{uuid}',                     [\App\Http\Controllers\Api\Client\ClientBillingApiController::class, 'show']);
    Route::post('/billing/{uuid}/pay/stripe',         [\App\Http\Controllers\Api\Client\ClientBillingApiController::class, 'payWithStripe']);
    Route::post('/billing/{uuid}/pay/razorpay',       [\App\Http\Controllers\Api\Client\ClientBillingApiController::class, 'payWithRazorpay']);
    Route::post('/billing/{uuid}/mark-paid',          [\App\Http\Controllers\Api\Client\ClientBillingApiController::class, 'markPaid']);
    Route::post('/billing/{uuid}/void',               [\App\Http\Controllers\Api\Client\ClientBillingApiController::class, 'voidInvoice']);
});

// ── Client Workspace API ──────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('workspaces')->group(function () {
    // Reports
    Route::get('/{workspaceUuid}/reports',                  [\App\Http\Controllers\Api\Client\ClientReportApiController::class, 'index']);
    Route::post('/{workspaceUuid}/reports',                 [\App\Http\Controllers\Api\Client\ClientReportApiController::class, 'store']);
    Route::get('/reports/{uuid}',                           [\App\Http\Controllers\Api\Client\ClientReportApiController::class, 'show']);
    Route::get('/reports/{uuid}/download',                  [\App\Http\Controllers\Api\Client\ClientReportApiController::class, 'download']);
    Route::delete('/reports/{uuid}',                        [\App\Http\Controllers\Api\Client\ClientReportApiController::class, 'destroy']);

    // White-label
    Route::get('/{workspaceUuid}/white-label',              [\App\Http\Controllers\Api\Client\WhiteLabelApiController::class, 'show']);
    Route::put('/{workspaceUuid}/white-label',              [\App\Http\Controllers\Api\Client\WhiteLabelApiController::class, 'update']);
    Route::post('/{workspaceUuid}/white-label/logo',        [\App\Http\Controllers\Api\Client\WhiteLabelApiController::class, 'uploadLogo']);
    Route::post('/{workspaceUuid}/white-label/verify-domain',[\App\Http\Controllers\Api\Client\WhiteLabelApiController::class, 'verifyDomain']);

    // Activity logs
    Route::get('/{workspaceUuid}/activity',   [\App\Http\Controllers\Api\Client\ClientActivityLogApiController::class, 'index']);
    Route::post('/{workspaceUuid}/activity',  [\App\Http\Controllers\Api\Client\ClientActivityLogApiController::class, 'store']);
});

// ── Subscription & Billing API ────────────────────────────────────────
// Public: plans list (no auth)
Route::get('/billing/plans',        [\App\Http\Controllers\Api\Billing\BillingPlanApiController::class, 'index']);
Route::get('/billing/plans/{plan}', [\App\Http\Controllers\Api\Billing\BillingPlanApiController::class, 'show']);

// Authenticated billing endpoints
Route::middleware(['auth:sanctum', 'resolve.tenant'])->prefix('billing')->group(function () {
    // Subscription management
    Route::get('/subscription',  [\App\Http\Controllers\Api\Billing\SubscriptionApiController::class, 'current']);
    Route::post('/subscribe',    [\App\Http\Controllers\Api\Billing\SubscriptionApiController::class, 'subscribe']);
    Route::post('/upgrade',      [\App\Http\Controllers\Api\Billing\SubscriptionApiController::class, 'upgrade']);
    Route::post('/downgrade',    [\App\Http\Controllers\Api\Billing\SubscriptionApiController::class, 'downgrade']);
    Route::post('/cancel',       [\App\Http\Controllers\Api\Billing\SubscriptionApiController::class, 'cancel']);
    Route::post('/pause',        [\App\Http\Controllers\Api\Billing\SubscriptionApiController::class, 'pause']);
    Route::post('/resume',       [\App\Http\Controllers\Api\Billing\SubscriptionApiController::class, 'resume']);

    // Coupons
    Route::post('/apply-coupon',   [\App\Http\Controllers\Api\Billing\CouponApiController::class, 'apply']);
    Route::get('/validate-coupon', [\App\Http\Controllers\Api\Billing\CouponApiController::class, 'validate']);

    // Invoices
    Route::get('/invoices',                     [\App\Http\Controllers\Api\Billing\InvoiceApiController::class, 'index']);
    Route::get('/invoices/{invoice}',           [\App\Http\Controllers\Api\Billing\InvoiceApiController::class, 'show']);
    Route::get('/invoices/{invoice}/download',  [\App\Http\Controllers\Api\Billing\InvoiceApiController::class, 'download']);

    // Payments
    Route::get('/payments',                       [\App\Http\Controllers\Api\Billing\PaymentApiController::class, 'index']);
    Route::post('/payments/{payment}/retry',      [\App\Http\Controllers\Api\Billing\PaymentApiController::class, 'retry']);

    // Usage tracking
    Route::get('/usage',           [\App\Http\Controllers\Api\Billing\UsageApiController::class, 'index']);
    Route::get('/usage/{feature}', [\App\Http\Controllers\Api\Billing\UsageApiController::class, 'show']);
});

// ── Admin API ─────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Analytics
    Route::get('/stats',              [\App\Http\Controllers\Api\Admin\AdminAnalyticsApiController::class, 'platformStats']);
    Route::get('/analytics/revenue',  [\App\Http\Controllers\Api\Admin\AdminAnalyticsApiController::class, 'revenueByMonth']);
    Route::get('/analytics/users',    [\App\Http\Controllers\Api\Admin\AdminAnalyticsApiController::class, 'userGrowth']);
    Route::get('/analytics/tenants',  [\App\Http\Controllers\Api\Admin\AdminAnalyticsApiController::class, 'tenantGrowth']);

    // Users
    Route::get('/users',              [\App\Http\Controllers\Api\Admin\AdminUserApiController::class, 'index']);
    Route::get('/users/{user}',       [\App\Http\Controllers\Api\Admin\AdminUserApiController::class, 'show']);
    Route::post('/users/{user}/suspend',  [\App\Http\Controllers\Api\Admin\AdminUserApiController::class, 'suspend']);
    Route::post('/users/{user}/activate', [\App\Http\Controllers\Api\Admin\AdminUserApiController::class, 'activate']);

    // Tickets
    Route::get('/tickets',                        [\App\Http\Controllers\Api\Admin\AdminTicketApiController::class, 'index']);
    Route::get('/tickets/stats',                  [\App\Http\Controllers\Api\Admin\AdminTicketApiController::class, 'stats']);
    Route::post('/tickets/{ticket}/reply',         [\App\Http\Controllers\Api\Admin\AdminTicketApiController::class, 'reply']);
    Route::patch('/tickets/{ticket}/status',       [\App\Http\Controllers\Api\Admin\AdminTicketApiController::class, 'updateStatus']);
});
