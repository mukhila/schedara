<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(5);

        return [
            'user_id'    => User::factory(),
            'tenant_id'  => null,
            'type'       => 'post.published',
            'category'   => $this->faker->randomElement(['post', 'billing', 'team', 'system']),
            'channel'    => null,
            'status'     => 'sent',
            'priority'   => 'normal',
            'action_url' => null,
            'data'       => ['title' => $title, 'body' => $this->faker->sentence()],
            'read_at'    => null,
        ];
    }

    public function unread(): static
    {
        return $this->state(['read_at' => null]);
    }

    public function read(): static
    {
        return $this->state(['read_at' => now()]);
    }
}
