<?php

namespace Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Models\Post;
use Models\Tenant;

class PostQueryService
{
    public function latestForTenant(Tenant|int $tenant, int $limit = 10): Collection
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return Post::whereTenantId($tenantId)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }


    public function popularForTenant(Tenant|int $tenant, int $limit = 5): Collection
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return Post::whereTenantId($tenantId)
            ->where('status', 'published')
            ->orderByDesc('views_total')
            ->limit($limit)
            ->get();
    }

    public function trendingForTenant(Tenant|int $tenant, int $days = 7, int $limit = 5): Collection
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
        $since = Carbon::now()->subDays($days);

        return Post::whereTenantId($tenantId)
            ->where('status', 'published')
            ->where('published_at', '>=', $since)
            ->orderByDesc('views_weekly')
            ->limit($limit)
            ->get();
    }
}