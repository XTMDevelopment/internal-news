<?php

namespace XTraMile\News\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use XTraMile\News\Enums\PostStatus;
use XTraMile\News\Models\Post;
use XTraMile\News\Models\Tenant;

/**
 * Service for querying posts with various filters and sorting options.
 */
class PostQueryService
{
    /**
     * Get the latest published posts for a tenant.
     *
     * @param Tenant|int $tenant The tenant instance or tenant ID
     * @param int $limit Maximum number of posts to return (default: 10)
     * @return Collection<int, Post>
     */
    public function latestForTenant(Tenant|int $tenant, int $limit = 10): Collection
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return Post::whereTenantId($tenantId)
            ->where('status', PostStatus::PUBLISHED->value)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the most popular published posts for a tenant based on total views.
     *
     * @param Tenant|int $tenant The tenant instance or tenant ID
     * @param int $limit Maximum number of posts to return (default: 5)
     * @return Collection<int, Post>
     */
    public function popularForTenant(Tenant|int $tenant, int $limit = 5): Collection
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return Post::whereTenantId($tenantId)
            ->where('status', PostStatus::PUBLISHED->value)
            ->orderByDesc('views_total')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending published posts for a tenant based on weekly views within a specified time period.
     *
     * @param Tenant|int $tenant The tenant instance or tenant ID
     * @param int $days Number of days to look back for trending posts (default: 7)
     * @param int $limit Maximum number of posts to return (default: 5)
     * @return Collection<int, Post>
     */
    public function trendingForTenant(Tenant|int $tenant, int $days = 7, int $limit = 5): Collection
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
        $since = Carbon::now()->subDays($days);

        return Post::whereTenantId($tenantId)
            ->where('status', PostStatus::PUBLISHED->value)
            ->where('published_at', '>=', $since)
            ->orderByDesc('views_weekly')
            ->limit($limit)
            ->get();
    }
}