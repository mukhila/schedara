<?php

namespace Database\Factories;

use App\Models\AiBrandVoice;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiBrandVoiceFactory extends Factory
{
    protected $model = AiBrandVoice::class;

    public function definition(): array
    {
        return [
            'tenant_id'           => Tenant::factory(),
            'name'                => $this->faker->words(3, true),
            'description'         => $this->faker->sentence(),
            'industry'            => $this->faker->randomElement(['tech', 'retail', 'healthcare', 'finance', 'education']),
            'tone_attributes'     => $this->faker->randomElements(['professional', 'friendly', 'bold', 'empathetic', 'witty'], 2),
            'brand_keywords'      => $this->faker->words(5),
            'example_content'     => $this->faker->paragraph(),
            'custom_instructions' => $this->faker->optional()->sentence(),
            'is_default'          => false,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
