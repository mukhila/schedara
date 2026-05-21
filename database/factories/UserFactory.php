<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => static::$password ??= Hash::make('password'),
            'avatar'            => fake()->boolean(40) ? 'https://i.pravatar.cc/150?u=' . fake()->uuid() : null,
            'timezone'          => fake()->randomElement([
                'UTC', 'America/New_York', 'Europe/London',
                'Asia/Kolkata', 'Asia/Tokyo', 'Australia/Sydney',
            ]),
            'mfa_enabled'       => false,
            'mfa_secret'        => null,
            'email_verified_at' => now(),
            'last_login_at'     => fake()->dateTimeBetween('-30 days', 'now'),
            'remember_token'    => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function withMfa(): static
    {
        return $this->state(fn () => [
            'mfa_enabled' => true,
            'mfa_secret'  => Str::random(32),
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['is_super_admin' => true]);
    }
}
