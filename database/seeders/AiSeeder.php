<?php

namespace Database\Seeders;

use App\Models\AiBrandVoice;
use App\Models\AiTemplate;
use App\Models\AiUsageLimit;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AiSeeder extends Seeder
{
    public function run(): void
    {
        $monthlyLimit = config('ai.usage.monthly_token_limit', 100_000);
        $resetDate    = now()->startOfMonth()->addMonth()->toDateString();

        // Create usage limit records for every tenant
        foreach (Tenant::all() as $tenant) {
            AiUsageLimit::firstOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'monthly_limit'       => $monthlyLimit,
                    'current_month_usage' => 0,
                    'total_tokens_used'   => 0,
                    'openai_tokens_used'  => 0,
                    'claude_tokens_used'  => 0,
                    'gemini_tokens_used'  => 0,
                    'total_cost_estimate' => 0,
                    'limit_reached'       => false,
                    'reset_date'          => $resetDate,
                ]
            );
        }

        // Seed a default brand voice for NorthSails
        $northsails = Tenant::where('slug', 'northsails')->first();

        if ($northsails) {
            AiBrandVoice::firstOrCreate(
                ['tenant_id' => $northsails->id, 'name' => 'NorthSails Voice'],
                [
                    'description'         => 'Official brand voice for NorthSails Apparel',
                    'industry'            => 'Fashion / Apparel',
                    'tone_attributes'     => ['energetic', 'adventurous', 'authentic', 'sustainable', 'premium'],
                    'brand_keywords'      => ['NorthSails', 'adventure', 'quality', 'sustainable', 'performance'],
                    'example_content'     => 'Built for the journey, designed to last. Discover our new collection — where performance meets style.',
                    'custom_instructions' => 'Use active voice. Emphasize sustainability and performance. Avoid clichés.',
                    'is_default'          => true,
                ]
            );
        }

        // Seed system-level AI prompt templates available to all tenants
        $this->seedSystemTemplates($northsails?->id ?? Tenant::first()?->id ?? 1);

        $this->command->info('✓ AI usage limits, brand voices, and templates seeded');
    }

    private function seedSystemTemplates(int $tenantId): void
    {
        $templates = [
            [
                'template_name'   => 'Product Launch Caption',
                'template_type'   => 'caption',
                'description'     => 'Announce a new product with excitement and a clear CTA.',
                'prompt_template' => 'Write a {tone} caption for {platform} announcing the launch of {product}. Target audience: {audience}. Include a strong CTA.',
                'variables'       => ['tone', 'platform', 'product', 'audience'],
                'is_public'       => true,
                'is_system'       => true,
            ],
            [
                'template_name'   => 'Flash Sale Urgency Ad',
                'template_type'   => 'ad_copy',
                'description'     => 'Urgency-driven ad copy for limited-time offers.',
                'prompt_template' => 'Write a high-converting {platform} ad for a {duration} flash sale on {product}. Discount: {discount}. Use urgency and scarcity.',
                'variables'       => ['platform', 'duration', 'product', 'discount'],
                'is_public'       => true,
                'is_system'       => true,
            ],
            [
                'template_name'   => 'Weekly Content Calendar',
                'template_type'   => 'content_ideas',
                'description'     => 'Generate a full week of social media post ideas.',
                'prompt_template' => 'Create 7 social media post ideas for a {industry} brand on {platform}. Goal: {goal}. Each idea should specify format, hook, and CTA.',
                'variables'       => ['industry', 'platform', 'goal'],
                'is_public'       => true,
                'is_system'       => true,
            ],
            [
                'template_name'   => 'SEO Blog Description',
                'template_type'   => 'seo',
                'description'     => 'Optimize a blog post description for search engines.',
                'prompt_template' => 'Optimize the following blog post description for SEO targeting the keyword "{keyword}": {content}',
                'variables'       => ['keyword', 'content'],
                'is_public'       => true,
                'is_system'       => true,
            ],
            [
                'template_name'   => 'Friendly Customer Reply',
                'template_type'   => 'response',
                'description'     => 'Generate warm, professional responses to customer comments.',
                'prompt_template' => 'Generate 3 friendly, {tone} responses to this customer {context}: "{message}". Brand name: {brand}.',
                'variables'       => ['tone', 'context', 'message', 'brand'],
                'is_public'       => true,
                'is_system'       => true,
            ],
        ];

        foreach ($templates as $tpl) {
            AiTemplate::firstOrCreate(
                ['template_name' => $tpl['template_name'], 'is_system' => true],
                array_merge($tpl, [
                    'tenant_id'  => $tenantId,
                    'created_by' => 1,
                    'usage_count'=> 0,
                ])
            );
        }
    }
}
