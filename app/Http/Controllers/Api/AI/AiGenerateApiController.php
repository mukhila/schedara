<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AdCopyService;
use App\Services\AI\CaptionService;
use App\Services\AI\CampaignService;
use App\Services\AI\ContentIdeasService;
use App\Services\AI\HashtagService;
use App\Services\AI\ResponseSuggestionService;
use App\Services\AI\SeoService;
use App\Exceptions\AI\UsageLimitExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AiGenerateApiController extends Controller
{
    public function __construct(
        private readonly CaptionService            $captions,
        private readonly HashtagService            $hashtags,
        private readonly ContentIdeasService       $ideas,
        private readonly SeoService                $seo,
        private readonly AdCopyService             $adCopy,
        private readonly ResponseSuggestionService $responses,
        private readonly CampaignService           $campaigns,
    ) {}

    // POST /api/ai/assistant/caption
    public function caption(Request $request): JsonResponse
    {
        $data = $request->validate([
            'topic'           => 'required|string|max:500',
            'platform'        => 'required|in:instagram,facebook,twitter,linkedin,tiktok,youtube,pinterest,threads',
            'tone'            => 'nullable|in:professional,viral,funny,emotional,luxury,educational,product',
            'brand'           => 'nullable|string|max:128',
            'audience'        => 'nullable|string|max:255',
            'keywords'        => 'nullable|string|max:500',
            'count'           => 'nullable|integer|min:1|max:5',
            'brand_voice_id'  => 'nullable|uuid',
            'provider'        => 'nullable|in:openai,claude,gemini',
            'model'           => 'nullable|string',
        ]);

        return $this->run(fn () => $this->captions->generate(
            $data,
            app('current.tenant')->id,
            $request->user()->id,
            $data['provider'] ?? null,
            $data['model']    ?? null,
        ));
    }

    // POST /api/ai/assistant/hashtags
    public function hashtags(Request $request): JsonResponse
    {
        $data = $request->validate([
            'topic'    => 'required|string|max:500',
            'platform' => 'required|in:instagram,facebook,twitter,linkedin,tiktok,youtube,pinterest,threads',
            'industry' => 'nullable|string|max:128',
            'count'    => 'nullable|integer|min:5|max:50',
            'provider' => 'nullable|in:openai,claude,gemini',
            'model'    => 'nullable|string',
        ]);

        return $this->run(fn () => $this->hashtags->generate(
            $data,
            app('current.tenant')->id,
            $request->user()->id,
            $data['provider'] ?? null,
            $data['model']    ?? null,
        ));
    }

    // POST /api/ai/assistant/content-ideas
    public function contentIdeas(Request $request): JsonResponse
    {
        $data = $request->validate([
            'industry' => 'required|string|max:128',
            'platform' => 'required|in:instagram,facebook,twitter,linkedin,tiktok,youtube,all',
            'tone'     => 'nullable|in:professional,viral,funny,educational,inspirational',
            'goal'     => 'nullable|string|max:128',
            'period'   => 'nullable|in:day,week,month',
            'count'    => 'nullable|integer|min:3|max:20',
            'provider' => 'nullable|in:openai,claude,gemini',
            'model'    => 'nullable|string',
        ]);

        return $this->run(fn () => $this->ideas->generate(
            $data,
            app('current.tenant')->id,
            $request->user()->id,
            $data['provider'] ?? null,
            $data['model']    ?? null,
        ));
    }

    // POST /api/ai/assistant/seo-optimize
    public function seoOptimize(Request $request): JsonResponse
    {
        $data = $request->validate([
            'content'  => 'required|string|max:10000',
            'platform' => 'nullable|in:instagram,facebook,twitter,linkedin,youtube,blog,general',
            'keywords' => 'nullable|string|max:500',
            'type'     => 'nullable|in:caption,blog,description,ad_copy',
            'provider' => 'nullable|in:openai,claude,gemini',
            'model'    => 'nullable|string',
        ]);

        return $this->run(fn () => $this->seo->optimize(
            $data,
            app('current.tenant')->id,
            $request->user()->id,
            $data['provider'] ?? null,
            $data['model']    ?? null,
        ));
    }

    // POST /api/ai/assistant/ad-copy
    public function adCopy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product'           => 'required|string|max:500',
            'value_proposition' => 'nullable|string|max:1000',
            'platform'          => 'required|in:facebook,instagram,google,linkedin,youtube,tiktok,twitter,twitterx',
            'goal'              => 'nullable|in:conversions,awareness,traffic,leads',
            'style'             => 'nullable|in:sales,emotional,luxury,urgency,storytelling,persuasive,bold,urgent,friendly,professional',
            'tone'              => 'nullable|string|max:64',
            'audience'          => 'nullable|string|max:255',
            'budget'            => 'nullable|string|max:64',
            'usp'               => 'nullable|string|max:500',
            'variations'        => 'nullable|integer|min:1|max:5',
            'count'             => 'nullable|integer|min:1|max:5',
            'brand_voice_id'    => 'nullable|uuid',
            'provider'          => 'nullable|in:openai,claude,gemini',
            'model'             => 'nullable|string',
        ]);

        return $this->run(fn () => $this->adCopy->generate(
            $data,
            app('current.tenant')->id,
            $request->user()->id,
            $data['provider'] ?? null,
            $data['model']    ?? null,
        ));
    }

    // POST /api/ai/assistant/response-suggestions
    public function responseSuggestions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'comment'    => 'required_without:original_message|nullable|string|max:2000',
            'original_message' => 'required_without:comment|nullable|string|max:2000',
            'context'    => 'nullable|in:comment,review,dm,inquiry,question,complaint',
            'tone'       => 'nullable|in:friendly,professional,empathetic,sales,playful,formal,enthusiastic',
            'brand'      => 'nullable|string|max:128',
            'key_points' => 'nullable|string|max:500',
            'count'      => 'nullable|integer|min:1|max:5',
            'provider'   => 'nullable|in:openai,claude,gemini',
            'model'      => 'nullable|string',
        ]);
        // Normalise to original_message so downstream services have a consistent key
        $data['original_message'] ??= $data['comment'] ?? '';

        return $this->run(fn () => $this->responses->generate(
            $data,
            app('current.tenant')->id,
            $request->user()->id,
            $data['provider'] ?? null,
            $data['model']    ?? null,
        ));
    }

    // POST /api/ai/assistant/campaign
    public function campaign(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'nullable|string|max:200',
            'product'    => 'nullable|string|max:500',
            'goal'       => 'required|string|max:500',
            'platforms'  => 'nullable|array',
            'platforms.*'=> 'string|max:64',
            'budget'     => 'nullable|string|max:64',
            'duration'   => 'nullable|string|max:64',
            'audience'   => 'nullable|string|max:500',
            'tone'       => 'nullable|string|max:64',
            'brand_voice_id' => 'nullable|uuid',
            'provider'   => 'nullable|in:openai,claude,gemini',
            'model'      => 'nullable|string',
        ]);

        return $this->run(fn () => $this->campaigns->generate(
            $data,
            app('current.tenant')->id,
            $request->user()->id,
            $data['provider'] ?? null,
            $data['model']    ?? null,
        ));
    }

    // ── Helper ────────────────────────────────────────────────────

    private function run(callable $action): JsonResponse
    {
        try {
            return response()->json(['data' => $action()]);
        } catch (UsageLimitExceededException $e) {
            return response()->json(['error' => $e->getMessage()], 429);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Generation failed. Please try again.'], 500);
        }
    }
}
