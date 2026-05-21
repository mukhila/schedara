<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\ClickTrackingService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShortLinkController extends Controller
{
    public function __construct(private ClickTrackingService $tracking) {}

    public function redirect(Request $request, string $shortCode): Response
    {
        $meta = [
            'device'   => $this->detectDevice($request->userAgent() ?? ''),
            'referrer' => $request->header('referer'),
            'country'  => null, // populated by geo-IP middleware if configured
        ];

        $link = $this->tracking->recordClick($shortCode, $meta);

        if (!$link) {
            abort(404);
        }

        return redirect()->away($link->url, 301);
    }

    private function detectDevice(string $ua): string
    {
        if (preg_match('/mobile|android|iphone|ipad/i', $ua)) {
            return 'mobile';
        }
        if (preg_match('/tablet/i', $ua)) {
            return 'tablet';
        }
        return 'desktop';
    }
}
