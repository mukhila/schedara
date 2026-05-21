<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'tenant_id'   => null,
            'assigned_to' => null,
            'subject'     => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'category'    => $this->faker->randomElement(['billing', 'technical', 'general', 'feature']),
            'status'      => 'open',
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => 'open']);
    }

    public function resolved(): static
    {
        return $this->state(['status' => 'resolved', 'resolved_at' => now()]);
    }

    public function critical(): static
    {
        return $this->state(['priority' => 'critical']);
    }
}
