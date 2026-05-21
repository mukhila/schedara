<?php

namespace App\Http\Controllers\Social;

use App\Exceptions\Social\UnsupportedPlatformException;
use App\Http\Controllers\Controller;
use App\Jobs\Social\RefreshSocialTokenJob;
use App\Jobs\Social\SyncSocialAccountJob;
use App\Models\SocialAccount;
use App\Models\SocialPlatform;
use App\Services\Social\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialAccountController extends Controller
{
    public function __construct(private readonly SocialAuthService $socialAuth) {}

    /** GET /social/accounts */
    public function index(): View
    {
        $tenant    = app('current.tenant');
        $accounts  = SocialAccount::with('platform')
            ->withCount('pages')
            ->forTenant($tenant->id)
            ->latest()
            ->get();

        $platforms = SocialPlatform::active()->get();
        $connected = $accounts->groupBy(fn ($a) => $a->platform?->slug ?? '');

        return view('backend.social.index', compact('accounts', 'platforms', 'connected'));
    }

    /** GET /social/accounts/{account} */
    public function show(SocialAccount $account): View
    {
        $this->authorizeAccount($account);
        $account->load('platform', 'pages', 'logs');

        return view('backend.social.show', compact('account'));
    }

    /** GET /social/connect/{platform} */
    public function connect(string $platform): RedirectResponse
    {
        try {
            $url = $this->socialAuth->getRedirectUrl($platform);
            return redirect()->away($url);
        } catch (UnsupportedPlatformException $e) {
            return redirect()->route('social.index')
                ->withErrors(['platform' => $e->getMessage()]);
        }
    }

    /** GET /social/callback/{platform} */
    public function callback(string $platform, Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('social.index')
                ->withErrors(['oauth' => $request->get('error_description', 'Authorization was denied.')]);
        }

        try {
            $user    = auth()->user();
            $tenant  = app('current.tenant');
            $account = $this->socialAuth->handleCallback($platform, $user, $tenant);

            return redirect()->route('social.show', $account->uuid)
                ->with('success', "{$account->platform->name} connected successfully!");
        } catch (UnsupportedPlatformException $e) {
            return redirect()->route('social.index')
                ->withErrors(['platform' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()->route('social.index')
                ->withErrors(['oauth' => 'Connection failed: ' . $e->getMessage()]);
        }
    }

    /** DELETE /social/accounts/{account} */
    public function disconnect(SocialAccount $account): RedirectResponse
    {
        $this->authorizeAccount($account);
        $this->socialAuth->disconnect($account);

        return redirect()->route('social.index')
            ->with('success', 'Account disconnected successfully.');
    }

    /** POST /social/accounts/{account}/sync */
    public function sync(SocialAccount $account): RedirectResponse
    {
        $this->authorizeAccount($account);
        SyncSocialAccountJob::dispatch($account)->onQueue('social');

        return back()->with('success', 'Account sync has been queued.');
    }

    /** POST /social/accounts/{account}/refresh */
    public function refresh(SocialAccount $account): RedirectResponse
    {
        $this->authorizeAccount($account);
        RefreshSocialTokenJob::dispatch($account)->onQueue('social');

        return back()->with('success', 'Token refresh has been queued.');
    }

    /** GET /social/accounts/{account}/pages */
    public function pages(SocialAccount $account): View
    {
        $this->authorizeAccount($account);
        $pages = $account->pages()->get();

        return view('backend.social.pages', compact('account', 'pages'));
    }

    /** POST /social/accounts/{account}/pages/{page}/toggle */
    public function togglePage(SocialAccount $account, string $pageUuid): RedirectResponse
    {
        $this->authorizeAccount($account);
        $page = $account->pages()->where('uuid', $pageUuid)->firstOrFail();
        $page->update(['is_selected' => ! $page->is_selected]);

        return back()->with('success', 'Page selection updated.');
    }

    private function authorizeAccount(SocialAccount $account): void
    {
        abort_if($account->tenant_id !== app('current.tenant')->id, 403);
    }
}
