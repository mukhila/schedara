<?php

namespace Database\Factories;

use App\Models\MediaFolder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MediaFolderFactory extends Factory
{
    protected $model = MediaFolder::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        $slug = Str::slug($name);

        return [
            'uuid'       => (string) Str::uuid(),
            'tenant_id'  => Tenant::factory(),
            'created_by' => User::factory(),
            'name'       => $name,
            'slug'       => $slug,
            'path'       => '/' . $slug,
            'color'      => '#' . $this->faker->hexColor(),
            'is_shared'  => false,
        ];
    }
}
