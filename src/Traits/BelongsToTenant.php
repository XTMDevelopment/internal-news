<?php

namespace XTraMile\News\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use XTraMile\News\Models\Tenant;

/**
 * Trait for models that belong to a tenant.
 * 
 * Provides a tenant relationship and a scope for filtering by tenant.
 */
trait BelongsToTenant
{
    /**
     * Get the tenant that this model belongs to.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include models for a specific tenant.
     *
     * @param Builder $query The query builder instance
     * @param Tenant|int $tenant The tenant instance or tenant ID
     * @return Builder
     */
    public function scopeForTenant(Builder $query, Tenant|int $tenant): Builder
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $query->where('tenant_id', $tenantId);
    }
}