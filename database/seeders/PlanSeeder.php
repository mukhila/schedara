<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'price_monthly' => 2300,   // $23/mo
                'price_yearly'  => 18240,  // $19/mo × 12 billed annually
                'features' => [
                    'ai_captions'   => false,
                    'unified_inbox' => false,
                    'approvals'     => false,
                    'analytics_90d' => false,
                    'white_label'   => false,
                    'api_access'    => false,
                    'custom_domain' => false,
                ],
                'limits' => [
                    'posts_per_month' => 100,
                    'channels'        => 5,
                    'users'           => 1,
                    'ai_generations'  => 0,
                    'media_storage_gb'=> 2,
                ],
                'is_active' => true,
            ],
            [
                'name'          => 'Growth',
                'slug'          => 'growth',
                'price_monthly' => 7400,   // $74/mo
                'price_yearly'  => 56640,  // $59/mo × 12
                'features' => [
                    'ai_captions'   => true,
                    'unified_inbox' => true,
                    'approvals'     => true,
                    'analytics_90d' => true,
                    'white_label'   => false,
                    'api_access'    => false,
                    'custom_domain' => false,
                ],
                'limits' => [
                    'posts_per_month' => -1,   // unlimited (-1)
                    'channels'        => 25,
                    'users'           => 10,
                    'ai_generations'  => 500,
                    'media_storage_gb'=> 50,
                ],
                'is_active' => true,
            ],
            [
                'name'          => 'Scale',
                'slug'          => 'scale',
                'price_monthly' => 0,      // custom / contact sales
                'price_yearly'  => 0,
                'features' => [
                    'ai_captions'   => true,
                    'unified_inbox' => true,
                    'approvals'     => true,
                    'analytics_90d' => true,
                    'white_label'   => true,
                    'api_access'    => true,
                    'custom_domain' => true,
                    'sso'           => true,
                    'audit_logs'    => true,
                ],
                'limits' => [
                    'posts_per_month' => -1,
                    'channels'        => -1,
                    'users'           => -1,
                    'ai_generations'  => -1,
                    'media_storage_gb'=> 500,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        $this->command->info('✓ Plans seeded (' . count($plans) . ' plans)');
    }
}
