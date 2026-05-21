<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Models\AiRequest;
use App\Services\AI\UsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiUsageApiController extends Controller
{
    public function __construct(private readonly UsageService $usage) {}

    // GET /api/ai/assistant/usage
    public function show(Request $request): JsonResponse
    {
        $tenant  = app('current.tenant');
        $summary = $this->usage->summary($tenant->id);

        // Per-type breakdown for current month
        $byType = AiRequest::forTenant($tenant->id)
            ->completed()
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('request_type, count(*) as count, sum(tokens_used) as tokens')
            ->groupBy('request_type')
            ->pluck('tokens', 'request_type');

        // Per-provider breakdown for current month
        $byProvider = AiRequest::forTenant($tenant->id)
            ->completed()
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('ai_provider, count(*) as count, sum(tokens_used) as tokens, sum(cost_estimate) as cost')
            ->groupBy('ai_provider')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->ai_provider => ['tokens' => $r->tokens, 'cost' => $r->cost]]);

        return response()->json([
            'data' => array_merge($summary, [
                'by_type'             => $byType,
                'by_provider'         => $byProvider,
                'providers_available' => \App\Services\AI\AiProviderFactory::configured(),
            ]),
        ]);
    }

    // GET /api/ai/assistant/usage/recent
    public function recent(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');

        $query = AiRequest::forTenant($tenant->id)->latest()->limit(50);

        if ($provider = $request->query('provider')) {
            $query->where('ai_provider', $provider);
        }
        if ($type = $request->query('type')) {
            $query->where('request_type', $type);
        }

        $rows = $query->get(['uuid', 'ai_provider', 'ai_model', 'request_type', 'tokens_used', 'cost_estimate', 'status', 'created_at'])
            ->map(fn ($r) => [
                'uuid'            => $r->uuid,
                'provider'        => $r->ai_provider,
                'model'           => $r->ai_model,
                'type'            => $r->request_type,
                'tokens_used'     => $r->tokens_used,
                'cost_usd'        => $r->cost_estimate,
                'status'          => $r->status,
                'created_at'      => $r->created_at,
                'created_at_human'=> $r->created_at?->diffForHumans(),
            ]);

        return response()->json(['data' => $rows]);
    }
}
