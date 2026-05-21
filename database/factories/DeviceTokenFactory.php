<?php

namespace Database\Factories;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DeviceTokenFactory extends Factory
{
    protected $model = DeviceToken::class;

    public function definition(): array
    {
        return [
            'uuid'           => (string) Str::uuid(),
            'user_id'        => User::factory(),
            'device_type'    => 'web',
            'fcm_token'      => Str::random(40),
            'browser'        => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari']),
            'platform'       => $this->faker->randomElement(['Windows', 'macOS', 'Android', 'iOS']),
            'last_active_at' => now(),
        ];
    }
}
