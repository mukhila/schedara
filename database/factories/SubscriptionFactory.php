<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'uuid'                 => (string) Str::uuid(),
            'tenant_id'            => Tenant::factory(),
            'plan_id'              => Plan::factory(),
            'provider'             => 'stripe',
            'provider_id'          => 'sub_' . $this->faker->unique()->regexify('[A-Za-z0-9]{20}'),
            'interval'             => 'monthly',
            'status'               => 'active',
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
            'cancel_at'            => null,
            'trial_ends_at'        => null,
            'paused_at'            => null,
            'metadata'             => null,
        ];
    }

    public function free(): static
    {
        return $this->state([
            'provider'    => 'free',
            'provider_id' => 'free-' . $this->faker->unique()->numberBetween(1, 99999),
            'status'      => 'active',
        ]);
    }

    public function trialing(int $days = 14): static
    {
        return $this->state([
            'status'        => 'trialing',
            'trial_ends_at' => now()->addDays($days),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status'    => 'cancelled',
            'cancel_at' => now()->subDay(),
        ]);
    }

    public function paused(): static
    {
        return $this->state([
            'status'    => 'paused',
            'paused_at' => now(),
        ]);
    }

    public function yearly(): static
    {
        return $this->state([
            'interval'           => 'yearly',
            'current_period_end' => now()->addYear(),
        ]);
    }
}
