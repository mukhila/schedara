<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AiBrandVoice;
use App\Models\AiGeneratedContent;
use App\Models\AiTemplate;
use App\Services\AI\AiProviderFactory;
use App\Services\AI\UsageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function __construct(private readonly UsageService $usage) {}

    public function dashboard(Request $request): View
    {
        $tenant  = app('current.tenant');
        $usage   = $this->usage->summary($tenant->id);
        $recent  = AiGeneratedContent::where('tenant_id', $tenant->id)
            ->where('user_id', $request->user()->id)
            ->latest()->limit(8)->get();

        $configured = AiProviderFactory::configured();

        return view('backend.ai.dashboard', compact('usage', 'recent', 'configured'));
    }

    public function caption(Request $request): View
    {
        $tenant      = app('current.tenant');
        $brandVoices = AiBrandVoice::forTenant($tenant->id)->get(['uuid', 'name', 'is_default']);
        $configured  = AiProviderFactory::configured();

        return view('backend.ai.caption', compact('brandVoices', 'configured'));
    }

    public function hashtags(): View
    {
        return view('backend.ai.hashtag', ['configured' => AiProviderFactory::configured()]);
    }

    public function contentIdeas(): View
    {
        return view('backend.ai.content-ideas', ['configured' => AiProviderFactory::configured()]);
    }

    public function seo(): View
    {
        return view('backend.ai.seo', ['configured' => AiProviderFactory::configured()]);
    }

    public function adCopy(Request $request): View
    {
        $tenant      = app('current.tenant');
        $brandVoices = AiBrandVoice::forTenant($tenant->id)->get(['uuid', 'name', 'is_default']);
        $configured  = AiProviderFactory::configured();

        return view('backend.ai.ad-copy', compact('brandVoices', 'configured'));
    }

    public function responseSuggestions(): View
    {
        return view('backend.ai.response-suggestions', ['configured' => AiProviderFactory::configured()]);
    }

    public function campaign(Request $request): View
    {
        $tenant      = app('current.tenant');
        $brandVoices = AiBrandVoice::forTenant($tenant->id)->get(['uuid', 'name', 'is_default']);
        $configured  = AiProviderFactory::configured();

        return view('backend.ai.campaign', compact('brandVoices', 'configured'));
    }

    public function chat(Request $request): View
    {
        $tenant      = app('current.tenant');
        $brandVoices = AiBrandVoice::forTenant($tenant->id)->get(['id', 'name']);
        $configured  = AiProviderFactory::configured();

        return view('backend.ai.chat', compact('brandVoices', 'configured'));
    }

    public function templates(Request $request): View
    {
        $tenant    = app('current.tenant');
        $templates = AiTemplate::forTenant($tenant->id)->orderByDesc('usage_count')->get();

        return view('backend.ai.templates', compact('templates'));
    }

    public function brandVoice(Request $request): View
    {
        $configured = AiProviderFactory::configured();

        return view('backend.ai.brand-voice', compact('configured'));
    }

    public function usage(Request $request): View
    {
        $configured = AiProviderFactory::configured();

        return view('backend.ai.usage', compact('configured'));
    }
}
