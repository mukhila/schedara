<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Starter', 'Growth', 'Scale', 'Enterprise']);

        return [
            'name'          => $name,
            'slug'          => str($name)->slug(),
            'price_monthly' => fake()->randomElement([0, 1900, 5900, 19900]),
            'price_yearly'  => fake()->randomElement([0, 18240, 56640, 191040]),
            'features'      => [
                'ai_captions'     => true,
                'unified_inbox'   => true,
                'approvals'       => true,
                'white_label'     => false,
                'custom_domain'   => false,
                'api_access'      => false,
            ],
            'limits' => [
                'posts_per_month' => 100,
                'channels'        => 5,
                'users'           => 1,
                'ai_generations'  => 50,
            ],
            'is_active' => true,
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'name'          => 'Free',
            'slug'          => 'free',
            'price_monthly' => 0,
            'price_yearly'  => 0,
        ]);
    }
}
