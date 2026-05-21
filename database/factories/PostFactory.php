<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    private static array $platforms = [
        'instagram', 'facebook', 'twitter', 'linkedin',
        'tiktok', 'youtube', 'threads', 'pinterest',
    ];

    public function definition(): array
    {
        $status = fake()->randomElement([
            Post::STATUS_DRAFT,
            Post::STATUS_SCHEDULED,
            Post::STATUS_PUBLISHED,
            Post::STATUS_FAILED,
        ]);

        $scheduledAt  = null;
        $publishedAt  = null;
        $failureReason = null;

        if ($status === Post::STATUS_SCHEDULED) {
            $scheduledAt = fake()->dateTimeBetween('now', '+30 days');
        }

        if ($status === Post::STATUS_PUBLISHED) {
            $scheduledAt = fake()->dateTimeBetween('-30 days', '-1 hour');
            $publishedAt = fake()->dateTimeBetween($scheduledAt, 'now');
        }

        if ($status === Post::STATUS_FAILED) {
            $failureReason = fake()->randomElement([
                'Rate limit exceeded',
                'Invalid access token',
                'Media format not supported',
                'Caption exceeds character limit',
            ]);
        }

        $selectedPlatforms = fake()->randomElements(self::$platforms, fake()->numberBetween(1, 3));

        return [
            'tenant_id'      => Tenant::factory(),
            'user_id'        => User::factory(),
            'title'          => fake()->boolean(50) ? fake()->sentence(4) : null,
            'content'        => fake()->paragraph(2),
            'media_urls'     => fake()->boolean(70) ? [
                fake()->imageUrl(1080, 1080),
                fake()->imageUrl(1080, 1080),
            ] : null,
            'platforms'      => $selectedPlatforms,
            'status'         => $status,
            'scheduled_at'   => $scheduledAt,
            'published_at'   => $publishedAt,
            'failure_reason' => $failureReason,
            'post_ids'       => $status === Post::STATUS_PUBLISHED
                ? collect($selectedPlatforms)->mapWithKeys(fn ($p) => [$p => fake()->numerify('##########')])->all()
                : null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status'       => Post::STATUS_DRAFT,
            'scheduled_at' => null,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status'       => Post::STATUS_SCHEDULED,
            'scheduled_at' => fake()->dateTimeBetween('now', '+14 days'),
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => Post::STATUS_PUBLISHED,
            'scheduled_at' => now()->subHours(2),
            'published_at' => now()->subHour(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status'         => Post::STATUS_FAILED,
            'failure_reason' => 'Rate limit exceeded — retrying in 15 minutes.',
        ]);
    }
}
