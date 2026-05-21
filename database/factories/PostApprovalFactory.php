<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostApproval;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostApprovalFactory extends Factory
{
    protected $model = PostApproval::class;

    public function definition(): array
    {
        return [
            'tenant_id'       => Tenant::factory(),
            'post_id'         => Post::factory(),
            'requested_by'    => User::factory(),
            'approved_by'     => null,
            'status'          => 'pending',
            'request_comment' => $this->faker->optional()->sentence(),
            'reviewer_comment'=> null,
            'reviewed_at'     => null,
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'status'           => 'approved',
            'approved_by'      => User::factory(),
            'reviewer_comment' => $this->faker->sentence(),
            'reviewed_at'      => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status'           => 'rejected',
            'approved_by'      => User::factory(),
            'reviewer_comment' => $this->faker->sentence(),
            'reviewed_at'      => now(),
        ]);
    }
}
