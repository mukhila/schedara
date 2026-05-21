<?php

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    private static array $platforms = [
        'instagram', 'facebook', 'twitter', 'linkedin',
        'tiktok', 'youtube', 'threads', 'pinterest',
    ];

    public function definition(): array
    {
        $platform = fake()->randomElement(self::$platforms);

        return [
            'tenant_id'     => Tenant::factory(),
            'platform'      => $platform,
            'account_id'    => fake()->numerify('##########'),
            'account_name'  => '@' . fake()->userName(),
            'avatar'        => 'https://i.pravatar.cc/150?u=' . fake()->uuid(),
            'access_token'  => fake()->sha256(),  // encrypted by model cast
            'refresh_token' => fake()->sha256(),
            'scopes'        => $this->scopesFor($platform),
            'expires_at'    => now()->addDays(60),
            'status'        => 'active',
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay(),
            'status'     => 'expired',
        ]);
    }

    public function forPlatform(string $platform): static
    {
        return $this->state(fn () => ['platform' => $platform]);
    }

    private function scopesFor(string $platform): array
    {
        return match ($platform) {
            'instagram' => ['instagram_basic', 'instagram_content_publish', 'pages_read_engagement'],
            'facebook'  => ['pages_manage_posts', 'pages_read_engagement', 'public_profile'],
            'twitter'   => ['tweet.read', 'tweet.write', 'users.read'],
            'linkedin'  => ['r_liteprofile', 'r_emailaddress', 'w_member_social'],
            default     => ['read', 'write'],
        };
    }
}
