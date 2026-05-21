<?php

namespace Database\Factories;

use App\Models\AccountAnalytic;
use App\Models\SocialAccount;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountAnalyticFactory extends Factory
{
    protected $model = AccountAnalytic::class;

    public function definition(): array
    {
        $followers = fake()->numberBetween(1000, 100000);

        return [
            'tenant_id'         => Tenant::factory(),
            'social_account_id' => SocialAccount::factory(),
            'date'              => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'followers'         => $followers,
            'following'         => fake()->numberBetween(100, 2000),
            'unfollows'         => fake()->numberBetween(0, 50),
            'posts_count'       => fake()->numberBetween(10, 200),
            'reach'             => (int) ($followers * fake()->randomFloat(2, 0.05, 0.3)),
            'impressions'       => (int) ($followers * fake()->randomFloat(2, 0.1, 0.5)),
            'profile_views'     => fake()->numberBetween(50, 5000),
            'likes'             => fake()->numberBetween(0, 5000),
            'comments'          => fake()->numberBetween(0, 500),
            'shares'            => fake()->numberBetween(0, 300),
            'clicks'            => fake()->numberBetween(0, 1000),
            'website_clicks'    => fake()->numberBetween(0, 200),
            'engagement_rate'   => fake()->randomFloat(2, 0.5, 8.0),
            'revenue'           => 0,
        ];
    }
}
