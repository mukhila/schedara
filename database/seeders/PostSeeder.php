<?php

namespace Database\Seeders;

use App\Models\AccountAnalytic;
use App\Models\Post;
use App\Models\PostAnalytic;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $northsails = Tenant::where('slug', 'northsails')->first();
        $priya      = User::where('email', 'priya@northsails.dev')->first();
        $mateo      = User::where('email', 'mateo@northsails.dev')->first();
        $accounts   = SocialAccount::where('tenant_id', $northsails->id)->get();

        // ── 10 published posts ─────────────────────────────────────────
        $publishedPosts = Post::factory(10)->published()->create([
            'tenant_id' => $northsails->id,
            'user_id'   => $priya->id,
            'platforms' => ['instagram', 'facebook', 'twitter'],
        ]);

        foreach ($publishedPosts as $post) {
            // Platform config per platform
            foreach ($post->platforms as $platform) {
                PostPlatformConfig::create([
                    'post_id'          => $post->id,
                    'platform'         => $platform,
                    'content_override' => null,
                    'status'           => 'published',
                    'platform_post_id' => fake()->numerify('##########'),
                ]);
            }

            // Analytics per social account
            foreach ($accounts->whereIn('platform', $post->platforms) as $account) {
                PostAnalytic::create([
                    'post_id'           => $post->id,
                    'social_account_id' => $account->id,
                    'platform'          => $account->platform,
                    'likes'             => fake()->numberBetween(100, 5000),
                    'comments'          => fake()->numberBetween(10, 500),
                    'shares'            => fake()->numberBetween(5, 300),
                    'reach'             => fake()->numberBetween(1000, 50000),
                    'impressions'       => fake()->numberBetween(2000, 100000),
                    'clicks'            => fake()->numberBetween(50, 3000),
                    'fetched_at'        => now()->subHours(rand(1, 24)),
                ]);
            }
        }

        // ── 5 scheduled posts ──────────────────────────────────────────
        Post::factory(5)->scheduled()->create([
            'tenant_id' => $northsails->id,
            'user_id'   => $mateo->id,
            'platforms' => ['instagram', 'linkedin'],
        ]);

        // ── 3 draft posts ──────────────────────────────────────────────
        Post::factory(3)->draft()->create([
            'tenant_id' => $northsails->id,
            'user_id'   => $mateo->id,
        ]);

        // ── 1 failed post ──────────────────────────────────────────────
        Post::factory()->failed()->create([
            'tenant_id' => $northsails->id,
            'user_id'   => $priya->id,
        ]);

        // ── Random posts for other tenants ─────────────────────────────
        $otherTenants = Tenant::whereNotIn('slug', ['northsails'])->get();

        foreach ($otherTenants as $tenant) {
            $owner = $tenant->users()->first();
            if ($owner) {
                Post::factory(rand(3, 8))->create([
                    'tenant_id' => $tenant->id,
                    'user_id'   => $owner->id,
                ]);
            }
        }

        // ── Daily account analytics (last 30 days) ─────────────────────
        foreach ($accounts as $account) {
            $followers = fake()->numberBetween(5000, 50000);

            for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
                $followers += fake()->numberBetween(-20, 120);

                AccountAnalytic::create([
                    'social_account_id' => $account->id,
                    'date'              => now()->subDays($daysAgo)->toDateString(),
                    'followers'         => max(0, $followers),
                    'following'         => fake()->numberBetween(200, 800),
                    'posts_count'       => fake()->numberBetween(100, 600),
                    'reach'             => fake()->numberBetween(1000, 30000),
                    'impressions'       => fake()->numberBetween(2000, 60000),
                    'profile_views'     => fake()->numberBetween(50, 2000),
                ]);
            }
        }

        $this->command->info('✓ Posts, platform configs, analytics seeded');
    }
}
