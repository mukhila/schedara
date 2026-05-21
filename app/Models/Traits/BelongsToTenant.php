<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;

/**
 * Attach TenantScope globally and expose a helper to bypass it.
 *
 * Usage: add `use BelongsToTenant;` to any tenant-scoped model.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        // Auto-fill tenant_id on create when the scope is active
        static::creating(function (self $model) {
            if (empty($model->tenant_id)) {
                $tenantId = TenantScope::resolveTenantId();
                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    /** Query across ALL tenants (super-admin / console use). */
    public static function allTenants(): \Illuminate\Database\Eloquent\Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
