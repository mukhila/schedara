<?php

namespace Database\Factories;

use App\Models\PostAnalytic;
use App\Models\SocialAccount;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostAnalyticFactory extends Factory
{
    protected $model = PostAnalytic::class;

    public function definition(): array
    {
        $platform = fake()->randomElement(['instagram', 'facebook', 'twitter', 'linkedin', 'tiktok']);
        $reach    = fake()->numberBetween(500, 50000);
        $likes    = (int) ($reach * fake()->randomFloat(2, 0.01, 0.15));
        $comments = (int) ($reach * fake()->randomFloat(2, 0.001, 0.03));
        $shares   = (int) ($reach * fake()->randomFloat(2, 0.001, 0.05));

        return [
            'tenant_id'         => Tenant::factory(),
            'social_account_id' => SocialAccount::factory(),
            'platform'          => $platform,
            'platform_post_id'  => fake()->numerify('##########'),
            'likes'             => $likes,
            'comments'          => $comments,
            'shares'            => $shares,
            'saves'             => (int) ($reach * fake()->randomFloat(2, 0, 0.02)),
            'reach'             => $reach,
            'impressions'       => (int) ($reach * fake()->randomFloat(2, 1.1, 2.5)),
            'clicks'            => (int) ($reach * fake()->randomFloat(2, 0, 0.05)),
            'video_views'       => 0,
            'conversions'       => fake()->numberBetween(0, 20),
            'engagement_rate'   => round(($likes + $comments + $shares) / $reach * 100, 4),
            'ctr'               => 0,
            'spend'             => 0,
            'revenue'           => 0,
            'fetched_at'        => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
