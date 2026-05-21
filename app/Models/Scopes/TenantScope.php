<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Reads the resolved tenant from the service container so the same
     * scope instance works in HTTP, queue, and console contexts.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = static::resolveTenantId();

        if ($tenantId !== null) {
            $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
        }
    }

    /**
     * Resolve the current tenant ID from the container or session.
     * Returns null when no tenant is active (e.g. artisan commands, super-admin).
     */
    public static function resolveTenantId(): ?int
    {
        try {
            return app()->bound('current.tenant.id')
                ? (int) app('current.tenant.id')
                : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
