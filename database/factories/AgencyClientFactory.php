<?php

namespace Database\Factories;

use App\Models\AgencyClient;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AgencyClientFactory extends Factory
{
    protected $model = AgencyClient::class;

    public function definition(): array
    {
        return [
            'uuid'         => (string) Str::uuid(),
            'agency_id'    => Tenant::factory(),
            'client_name'  => $this->faker->name(),
            'company_name' => $this->faker->company(),
            'email'        => $this->faker->unique()->safeEmail(),
            'phone'        => $this->faker->phoneNumber(),
            'website'      => $this->faker->url(),
            'industry'     => $this->faker->randomElement(['Technology','E-Commerce','Healthcare','Finance','Education']),
            'timezone'     => 'UTC',
            'status'       => 'active',
        ];
    }

    public function onboarding(): static
    {
        return $this->state(fn () => ['status' => 'onboarding']);
    }
}
