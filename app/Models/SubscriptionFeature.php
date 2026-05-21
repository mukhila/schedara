<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionFeature extends Model
{
    protected $table = 'subscription_features';

    protected $fillable = [
        'plan_id', 'feature_name', 'feature_value',
        'feature_label', 'is_highlighted', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_highlighted' => 'boolean', 'sort_order' => 'integer'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isUnlimited(): bool
    {
        return $this->feature_value === 'unlimited' || $this->feature_value === '-1';
    }

    public function numericValue(): ?int
    {
        return is_numeric($this->feature_value) ? (int) $this->feature_value : null;
    }
}
