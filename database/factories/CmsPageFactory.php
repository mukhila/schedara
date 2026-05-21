<?php

namespace Database\Factories;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CmsPageFactory extends Factory
{
    protected $model = CmsPage::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'title'       => $title,
            'slug'        => Str::slug($title),
            'content'     => $this->faker->paragraphs(5, true),
            'excerpt'     => $this->faker->sentence(12),
            'page_type'   => $this->faker->randomElement(['page', 'post', 'legal', 'faq']),
            'status'      => 'draft',
            'created_by'  => User::factory(),
            'sort_order'  => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published', 'published_at' => now()]);
    }
}
