<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\SubscriptionFeature;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Free',
                'slug'          => 'free',
                'description'   => 'Perfect for getting started. No credit card required.',
                'price_monthly' => 0,
                'price_yearly'  => 0,
                'currency'      => 'USD',
                'trial_days'    => 0,
                'is_popular'    => false,
                'sort_order'    => 1,
                'features'      => ['ai_captions' => false, 'analytics' => false, 'white_label' => false, 'api_access' => false],
                'limits'        => ['social_accounts' => 3, 'scheduled_posts' => 30, 'team_members' => 1, 'storage_mb' => 512, 'ai_credits' => 0, 'analytics_reports' => 0],
                'feature_list'  => [
                    ['name' => 'social_accounts',   'value' => '3',         'label' => '3 Social Accounts'],
                    ['name' => 'scheduled_posts',    'value' => '30',        'label' => '30 Scheduled Posts/month'],
                    ['name' => 'team_members',       'value' => '1',         'label' => '1 Team Member'],
                    ['name' => 'storage_mb',         'value' => '512',       'label' => '512 MB Storage'],
                    ['name' => 'analytics_reports',  'value' => '0',         'label' => 'Basic Analytics'],
                ],
            ],
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'description'   => 'For small teams growing fast. Everything you need to go pro.',
                'price_monthly' => 2900,
                'price_yearly'  => 29000,
                'currency'      => 'USD',
                'trial_days'    => 14,
                'is_popular'    => false,
                'sort_order'    => 2,
                'features'      => ['ai_captions' => true, 'analytics' => true, 'white_label' => false, 'api_access' => false],
                'limits'        => ['social_accounts' => 10, 'scheduled_posts' => 200, 'team_members' => 3, 'storage_mb' => 10240, 'ai_credits' => 100, 'analytics_reports' => 10],
                'feature_list'  => [
                    ['name' => 'social_accounts',   'value' => '10',        'label' => '10 Social Accounts'],
                    ['name' => 'scheduled_posts',    'value' => '200',       'label' => '200 Scheduled Posts/month'],
                    ['name' => 'team_members',       'value' => '3',         'label' => '3 Team Members'],
                    ['name' => 'ai_credits',         'value' => '100',       'label' => '100 AI Credits/month', 'highlighted' => true],
                    ['name' => 'storage_mb',         'value' => '10240',     'label' => '10 GB Storage'],
                    ['name' => 'analytics_reports',  'value' => '10',        'label' => 'Advanced Analytics'],
                ],
            ],
            [
                'name'          => 'Professional',
                'slug'          => 'pro',
                'description'   => 'Everything you need to scale. For growing businesses.',
                'price_monthly' => 7900,
                'price_yearly'  => 79000,
                'currency'      => 'USD',
                'trial_days'    => 14,
                'is_popular'    => true,
                'sort_order'    => 3,
                'features'      => ['ai_captions' => true, 'analytics' => true, 'white_label' => false, 'api_access' => true],
                'limits'        => ['social_accounts' => 25, 'scheduled_posts' => 1000, 'team_members' => 10, 'storage_mb' => 51200, 'ai_credits' => 500, 'analytics_reports' => 0],
                'feature_list'  => [
                    ['name' => 'social_accounts',   'value' => '25',        'label' => '25 Social Accounts'],
                    ['name' => 'scheduled_posts',    'value' => 'unlimited', 'label' => 'Unlimited Posts', 'highlighted' => true],
                    ['name' => 'team_members',       'value' => '10',        'label' => '10 Team Members'],
                    ['name' => 'ai_credits',         'value' => '500',       'label' => '500 AI Credits/month', 'highlighted' => true],
                    ['name' => 'storage_mb',         'value' => '51200',     'label' => '50 GB Storage'],
                    ['name' => 'api_access',         'value' => 'true',      'label' => 'API Access'],
                    ['name' => 'analytics_reports',  'value' => 'unlimited', 'label' => 'Full Analytics Suite'],
                ],
            ],
            [
                'name'          => 'Agency',
                'slug'          => 'agency',
                'description'   => 'Enterprise-grade power for agencies managing multiple clients.',
                'price_monthly' => 19900,
                'price_yearly'  => 199000,
                'currency'      => 'USD',
                'trial_days'    => 14,
                'is_popular'    => false,
                'sort_order'    => 4,
                'features'      => ['ai_captions' => true, 'analytics' => true, 'white_label' => true, 'api_access' => true],
                'limits'        => ['social_accounts' => 0, 'scheduled_posts' => 0, 'team_members' => 0, 'storage_mb' => 204800, 'ai_credits' => 2000, 'analytics_reports' => 0],
                'feature_list'  => [
                    ['name' => 'social_accounts',   'value' => 'unlimited', 'label' => 'Unlimited Social Accounts', 'highlighted' => true],
                    ['name' => 'scheduled_posts',    'value' => 'unlimited', 'label' => 'Unlimited Posts'],
                    ['name' => 'team_members',       'value' => 'unlimited', 'label' => 'Unlimited Team Members'],
                    ['name' => 'ai_credits',         'value' => '2000',      'label' => '2,000 AI Credits/month'],
                    ['name' => 'storage_mb',         'value' => '204800',    'label' => '200 GB Storage'],
                    ['name' => 'white_label',        'value' => 'true',      'label' => 'White-Label Dashboard', 'highlighted' => true],
                    ['name' => 'api_access',         'value' => 'true',      'label' => 'Full API Access'],
                    ['name' => 'client_management',  'value' => 'true',      'label' => 'Client Management Portal'],
                ],
            ],
        ];

        foreach ($plans as $data) {
            $features = $data['feature_list'];
            unset($data['feature_list']);

            $plan = Plan::updateOrCreate(['slug' => $data['slug']], $data);

            foreach ($features as $i => $feature) {
                SubscriptionFeature::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_name' => $feature['name']],
                    [
                        'feature_value' => $feature['value'],
                        'feature_label' => $feature['label'],
                        'is_highlighted' => $feature['highlighted'] ?? false,
                        'sort_order'    => $i,
                    ]
                );
            }
        }
    }
}
