<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'uuid'              => (string) Str::uuid(),
            'coupon_code'       => strtoupper($this->faker->unique()->lexify('????##')),
            'name'              => $this->faker->words(3, true),
            'description'       => $this->faker->sentence(),
            'discount_type'     => 'percentage',
            'discount_value'    => $this->faker->randomElement([10, 15, 20, 25, 30]),
            'usage_limit'       => 100,
            'used_count'        => 0,
            'per_workspace_limit' => 1,
            'applicable_plans'  => null,
            'first_time_only'   => false,
            'billing_cycles'    => 'both',
            'status'            => 'active',
            'starts_at'         => null,
            'expires_at'        => now()->addMonths(3),
        ];
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay(), 'status' => 'expired']);
    }

    public function fixed(int $cents = 500): static
    {
        return $this->state(['discount_type' => 'fixed', 'discount_value' => $cents]);
    }

    public function trialExtension(int $days = 7): static
    {
        return $this->state(['discount_type' => 'trial_extension', 'discount_value' => $days]);
    }
}
