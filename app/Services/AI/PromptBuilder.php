<?php

namespace App\Services\AI;

use App\Models\AiBrandVoice;

class PromptBuilder
{
    private string $systemBase = 'You are an expert AI marketing assistant for a social media management platform. Always return well-structured, actionable content.';

    public function system(string $role, ?AiBrandVoice $brandVoice = null): string
    {
        $prompt = $this->systemBase . ' ' . $role;

        if ($brandVoice) {
            $prompt .= ' ' . $brandVoice->toSystemPromptFragment();
        }

        return trim($prompt);
    }

    // ── Caption ───────────────────────────────────────────────────

    public function caption(array $inputs): string
    {
        $platform = $inputs['platform'] ?? 'instagram';
        $tone     = $inputs['tone']     ?? 'professional';
        $topic    = $inputs['topic']    ?? '';
        $brand    = $inputs['brand']    ?? '';
        $audience = $inputs['audience'] ?? 'general audience';
        $keywords = $inputs['keywords'] ?? '';
        $count    = $inputs['count']    ?? 3;

        return <<<PROMPT
        Generate {$count} {$tone} social media captions for {$platform}.
        Topic: {$topic}
        Brand name: {$brand}
        Target audience: {$audience}
        Keywords to include: {$keywords}

        Return a JSON object with a "captions" array. Each caption must have:
        - "short" (under 100 chars)
        - "long" (under 300 chars)
        - "cta" (clear call-to-action)
        - "emoji_version" (add relevant emojis)
        - "hashtags" (5-10 relevant hashtags)
        - "tone_used" (the specific tone applied)

        Optimize for {$platform} algorithm and engagement.
        PROMPT;
    }

    // ── Hashtag ───────────────────────────────────────────────────

    public function hashtags(array $inputs): string
    {
        $topic    = $inputs['topic']    ?? '';
        $platform = $inputs['platform'] ?? 'instagram';
        $count    = $inputs['count']    ?? 30;
        $niche    = $inputs['industry'] ?? '';

        return <<<PROMPT
        Generate {$count} highly effective hashtags for a {$platform} post about: {$topic}
        Industry/niche: {$niche}

        Return a JSON object with a "hashtags" array. Each hashtag must have:
        - "tag" (the hashtag, with #)
        - "category" (trending | niche | branded | industry | community)
        - "estimated_reach" (high | medium | low)
        - "competition" (high | medium | low)

        Mix: 20% trending (wide reach), 40% niche (targeted), 30% industry, 10% long-tail.
        Avoid banned or shadowbanned hashtags.
        PROMPT;
    }

    // ── Content Ideas ─────────────────────────────────────────────

    public function contentIdeas(array $inputs): string
    {
        $industry = $inputs['industry'] ?? '';
        $platform = $inputs['platform'] ?? 'instagram';
        $tone     = $inputs['tone']     ?? 'professional';
        $goal     = $inputs['goal']     ?? 'engagement';
        $count    = $inputs['count']    ?? 10;
        $period   = $inputs['period']   ?? 'week';

        return <<<PROMPT
        Generate {$count} creative content ideas for a {$industry} brand on {$platform}.
        Goal: {$goal} | Tone: {$tone} | Planning period: {$period}

        Return a JSON object with an "ideas" array. Each idea must have:
        - "title" (catchy content title)
        - "description" (what the post is about, 2-3 sentences)
        - "format" (reel | carousel | static | story | live)
        - "platform" (best platform for this)
        - "hook" (opening hook to stop the scroll)
        - "cta" (call to action)
        - "estimated_engagement" (high | medium | low)
        - "difficulty" (easy | medium | hard to produce)
        PROMPT;
    }

    // ── SEO ───────────────────────────────────────────────────────

    public function seoOptimize(array $inputs): string
    {
        $content  = $inputs['content']  ?? '';
        $platform = $inputs['platform'] ?? 'general';
        $keywords = $inputs['keywords'] ?? '';
        $type     = $inputs['type']     ?? 'caption';

        return <<<PROMPT
        Analyze and SEO-optimize this {$type} for {$platform}:

        ---
        {$content}
        ---

        Target keywords: {$keywords}

        Return a JSON object with:
        - "seo_score" (0-100)
        - "readability_score" (0-100)
        - "optimized_content" (the improved version)
        - "keyword_density" (object: keyword → percentage)
        - "missing_keywords" (array of keywords not used)
        - "meta_description" (160-char meta description)
        - "recommendations" (array of specific improvements)
        - "cta_suggestions" (array of stronger CTA options)
        PROMPT;
    }

    // ── Ad Copy ───────────────────────────────────────────────────

    public function adCopy(array $inputs): string
    {
        $product  = $inputs['product']  ?? '';
        $platform = $inputs['platform'] ?? 'facebook';
        $style    = $inputs['style']    ?? 'sales';
        $audience = $inputs['audience'] ?? '';
        $usp      = $inputs['usp']      ?? '';
        $count    = $inputs['count']    ?? 3;

        return <<<PROMPT
        Create {$count} {$style}-style ad copy variations for {$platform}.
        Product/Service: {$product}
        Unique Selling Proposition: {$usp}
        Target Audience: {$audience}

        Return a JSON object with a "variations" array. Each variation must have:
        - "headline" (max 40 chars for Facebook, 30 for Google)
        - "primary_text" (main ad body, 125 chars ideal)
        - "description" (supporting text)
        - "cta_button" (Shop Now | Learn More | Get Started | Sign Up)
        - "hook" (attention-grabbing opening)
        - "pain_point" (problem this solves)
        - "conversion_focus" (what drives the click)

        Make each variation test a different angle (price, urgency, social proof, benefit).
        PROMPT;
    }

    // ── Response Suggestion ───────────────────────────────────────

    public function responseSuggestion(array $inputs): string
    {
        $original = $inputs['original_message'] ?? '';
        $context  = $inputs['context']          ?? 'comment';
        $tone     = $inputs['tone']             ?? 'friendly';
        $brand    = $inputs['brand']            ?? '';
        $count    = $inputs['count']            ?? 3;

        return <<<PROMPT
        Generate {$count} {$tone} response suggestions for this {$context}:

        "{$original}"

        Brand: {$brand}

        Return a JSON object with a "responses" array. Each response must have:
        - "text" (the actual response to post)
        - "tone" (friendly | professional | empathetic | sales | playful)
        - "sentiment_match" (how well it matches the original sentiment)
        - "personalization_tips" (how to customize further)

        Responses should feel authentic, avoid clichés, and reflect the brand voice.
        PROMPT;
    }

    // ── Campaign ──────────────────────────────────────────────────

    public function campaign(array $inputs): string
    {
        $product  = $inputs['product']   ?? '';
        $goal     = $inputs['goal']      ?? 'awareness';
        $platform = implode(', ', (array) ($inputs['platforms'] ?? ['instagram']));
        $budget   = $inputs['budget']    ?? 'unspecified';
        $duration = $inputs['duration']  ?? '30 days';
        $audience = $inputs['audience']  ?? '';

        return <<<PROMPT
        Create a complete marketing campaign strategy.
        Product/Service: {$product}
        Campaign Goal: {$goal}
        Platforms: {$platform}
        Budget: {$budget}
        Duration: {$duration}
        Target Audience: {$audience}

        Return a JSON object with:
        - "campaign_name" (catchy campaign name)
        - "tagline" (memorable slogan)
        - "strategy_summary" (overview paragraph)
        - "phases" (array of campaign phases, each with: name, duration, focus, tactics)
        - "content_calendar" (array of post ideas, each with: day, platform, format, topic, caption_hint)
        - "kpis" (array of metrics to track)
        - "budget_breakdown" (suggested allocation by platform/phase)
        - "hashtag_strategy" (primary, secondary, branded hashtags)
        - "cta_funnel" (awareness → interest → desire → action CTAs)
        PROMPT;
    }

    // ── Brand Voice ───────────────────────────────────────────────

    public function brandVoiceAnalysis(array $inputs): string
    {
        $examples = $inputs['examples'] ?? '';
        $industry = $inputs['industry'] ?? '';

        return <<<PROMPT
        Analyze these content examples and extract the brand voice profile:

        ---
        {$examples}
        ---

        Industry: {$industry}

        Return a JSON object with:
        - "tone_attributes" (array: top 5 tone descriptors)
        - "vocabulary_style" (formal | casual | technical | conversational)
        - "personality_traits" (array: brand personality characteristics)
        - "do_list" (array: writing guidelines to follow)
        - "dont_list" (array: things to avoid)
        - "example_phrases" (array: signature phrases that match the brand)
        - "emoji_usage" (none | minimal | moderate | heavy)
        - "punctuation_style" (description of punctuation habits)
        PROMPT;
    }

    // ── Chat ──────────────────────────────────────────────────────

    public function chatSystem(?AiBrandVoice $brandVoice = null): string
    {
        return $this->system(
            'You are an AI marketing assistant. Help users with social media strategy, content creation, copywriting, SEO, ad copy, and marketing campaigns. Be concise, creative, and action-oriented.',
            $brandVoice
        );
    }
}
