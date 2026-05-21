<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['setting_key', 'setting_value', 'category', 'label', 'type', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    // ── Static helpers ───────────────────────────────────────────

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 600, function () use ($key, $default) {
            $row = static::where('setting_key', $key)->first();
            return $row ? $row->typedValue() : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => is_array($value) ? json_encode($value) : (string) $value]
        );
        Cache::forget("setting_{$key}");
    }

    public static function grouped(): \Illuminate\Support\Collection
    {
        return static::orderBy('category')->orderBy('setting_key')
            ->get()
            ->groupBy('category');
    }

    public function typedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->setting_value,
            'json'    => json_decode($this->setting_value, true),
            default   => $this->setting_value,
        };
    }
}
