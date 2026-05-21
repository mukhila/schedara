<?php

namespace Database\Factories;

use App\Models\MediaLibrary;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MediaLibraryFactory extends Factory
{
    protected $model = MediaLibrary::class;

    public function definition(): array
    {
        $ext  = $this->faker->randomElement(['jpg', 'png', 'mp4', 'pdf']);
        $type = match (true) {
            in_array($ext, ['jpg', 'png', 'webp', 'gif']) => 'image',
            in_array($ext, ['mp4', 'mov', 'avi'])          => 'video',
            $ext === 'pdf'                                  => 'document',
            default                                         => 'document',
        };

        return [
            'uuid'                => (string) Str::uuid(),
            'tenant_id'           => Tenant::factory(),
            'user_id'             => User::factory(),
            'name'                => $this->faker->words(2, true),
            'original_name'       => $this->faker->word() . '.' . $ext,
            'disk'                => 'local',
            's3_key'              => "uploads/{$type}s/" . Str::uuid() . ".{$ext}",
            'url'                 => "http://localhost/storage/uploads/{$type}s/" . Str::uuid() . ".{$ext}",
            'mime_type'           => $this->faker->mimeType(),
            'extension'           => $ext,
            'file_hash'           => bin2hex(random_bytes(32)),
            'type'                => $type,
            'size'                => $this->faker->numberBetween(10240, 5242880),
            'alt_text'            => null,
            'optimization_status' => $type === 'image' ? 'done' : 'na',
            'compression_status'  => $type === 'video' ? 'done' : 'na',
            'approval_status'     => 'draft',
            'is_favorite'         => false,
            'version'             => 1,
        ];
    }

    public function image(): static
    {
        return $this->state([
            'extension'           => 'jpg',
            'type'                => 'image',
            'mime_type'           => 'image/jpeg',
            'optimization_status' => 'done',
            'compression_status'  => 'na',
        ]);
    }

    public function video(): static
    {
        return $this->state([
            'extension'           => 'mp4',
            'type'                => 'video',
            'mime_type'           => 'video/mp4',
            'optimization_status' => 'na',
            'compression_status'  => 'done',
            'duration'            => $this->faker->numberBetween(10, 600),
        ]);
    }

    public function pending(): static
    {
        return $this->state(['approval_status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state(['approval_status' => 'approved']);
    }

    public function favorite(): static
    {
        return $this->state(['is_favorite' => true]);
    }
}
