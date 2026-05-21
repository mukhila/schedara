<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name'          => $name,
            'slug'          => Str::slug($name) . '-' . fake()->unique()->numerify('###'),
            'logo'          => null,
            'custom_domain' => null,
            'plan_id'       => Plan::inRandomOrder()->value('id'),
            'trial_ends_at' => now()->addDays(14),
            'status'        => 'trialing',
            'settings'      => [
                'brand_color'  => '#65a1d8',
                'timezone'     => 'UTC',
                'language'     => 'en',
                'week_starts'  => 'monday',
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status'        => 'active',
            'trial_ends_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'suspended']);
    }
}
