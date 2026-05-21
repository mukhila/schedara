<?php

namespace Database\Factories;

use App\Models\AnalyticsCampaign;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsCampaignFactory extends Factory
{
    protected $model = AnalyticsCampaign::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-3 months', 'now');
        $end   = $this->faker->dateTimeBetween($start, '+3 months');

        return [
            'tenant_id'   => Tenant::factory(),
            'created_by'  => User::factory(),
            'name'        => $this->faker->words(4, true) . ' Campaign',
            'status'      => $this->faker->randomElement(['draft', 'active', 'paused', 'completed']),
            'platform'    => $this->faker->randomElement(['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok']),
            'start_date'  => $start,
            'end_date'    => $end,
            'budget'      => $this->faker->randomFloat(2, 100, 10000),
            'spend'       => $this->faker->randomFloat(2, 0, 5000),
            'revenue'     => $this->faker->randomFloat(2, 0, 20000),
            'impressions' => $this->faker->numberBetween(0, 500000),
            'clicks'      => $this->faker->numberBetween(0, 50000),
            'conversions' => $this->faker->numberBetween(0, 5000),
            'reach'       => $this->faker->numberBetween(0, 300000),
            'engagement'  => $this->faker->numberBetween(0, 30000),
            'tags'        => null,
            'meta'        => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function completed(): static
    {
        return $this->state([
            'status'   => 'completed',
            'end_date' => now()->subDay(),
        ]);
    }
}
