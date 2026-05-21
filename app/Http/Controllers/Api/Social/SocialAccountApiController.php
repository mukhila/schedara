<?php

namespace App\Http\Controllers\API\Social;

use App\Http\Controllers\Controller;
use App\Http\Resources\Social\SocialAccountResource;
use App\Http\Resources\Social\SocialPageResource;
use App\Jobs\Social\RefreshSocialTokenJob;
use App\Jobs\Social\SyncSocialAccountJob;
use App\Models\SocialAccount;
use App\Services\Social\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SocialAccountApiController extends Controller
{
    public function __construct(private readonly SocialAuthService $socialAuth) {}

    /** GET /api/social/accounts */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenant  = app('current.tenant');
        $query   = SocialAccount::with('platform')
            ->withCount('pages')
            ->forTenant($tenant->id);

        if ($request->has('platform')) {
            $query->whereHas('platform', fn ($q) => $q->where('slug', $request->platform));
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return SocialAccountResource::collection($query->latest()->paginate(20));
    }

    /** GET /api/social/accounts/{account} */
    public function show(SocialAccount $account): SocialAccountResource
    {
        $this->authorizeAccount($account);
        $account->load('platform', 'pages');

        return new SocialAccountResource($account);
    }

    /** DELETE /api/social/accounts/{account} */
    public function destroy(SocialAccount $account): JsonResponse
    {
        $this->authorizeAccount($account);
        $this->socialAuth->disconnect($account);

        return response()->json(['message' => 'Account disconnected successfully.']);
    }

    /** POST /api/social/accounts/{account}/refresh */
    public function refresh(SocialAccount $account): JsonResponse
    {
        $this->authorizeAccount($account);
        RefreshSocialTokenJob::dispatch($account)->onQueue('social');

        return response()->json(['message' => 'Token refresh queued.']);
    }

    /** POST /api/social/accounts/{account}/sync */
    public function sync(SocialAccount $account): JsonResponse
    {
        $this->authorizeAccount($account);
        SyncSocialAccountJob::dispatch($account)->onQueue('social');

        return response()->json(['message' => 'Account sync queued.']);
    }

    /** GET /api/social/accounts/{account}/pages */
    public function pages(SocialAccount $account): AnonymousResourceCollection
    {
        $this->authorizeAccount($account);
        $pages = $account->pages()->get();

        return SocialPageResource::collection($pages);
    }

    private function authorizeAccount(SocialAccount $account): void
    {
        $tenant = app('current.tenant');
        abort_if($account->tenant_id !== $tenant->id, 403, 'Access denied.');
    }
}
