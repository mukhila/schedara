<?php

namespace Database\Factories;

use App\Models\CollaborationTask;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollaborationTaskFactory extends Factory
{
    protected $model = CollaborationTask::class;

    public function definition(): array
    {
        return [
            'tenant_id'   => Tenant::factory(),
            'assigned_by' => User::factory(),
            'assigned_to' => User::factory(),
            'post_id'     => null,
            'title'       => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(),
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'status'      => $this->faker->randomElement(['todo', 'in_progress', 'review', 'done']),
            'due_date'    => $this->faker->dateTimeBetween('now', '+30 days'),
            'attachments' => [],
            'labels'      => [],
            'sort_order'  => $this->faker->numberBetween(0, 100),
        ];
    }

    public function forPost(Post $post): static
    {
        return $this->state(['post_id' => $post->id, 'tenant_id' => $post->tenant_id]);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'done', 'completed_at' => now()]);
    }

    public function overdue(): static
    {
        return $this->state(['due_date' => now()->subDays(2), 'status' => 'todo']);
    }
}
