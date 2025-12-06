<?php

namespace XTraMile\News\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use XTraMile\News\Traits\AppSluggable;
use XTraMile\News\Traits\BelongsToTenant;

/**
 * XTraMile\News\Models\Tag
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Tenant $tenant
 * @property-read Collection<int, Post> $posts
 * @property-read int|null $posts_count
 * @method static Builder|Tag newModelQuery()
 * @method static Builder|Tag newQuery()
 * @method static Builder|Tag onlyTrashed()
 * @method static Builder|Tag query()
 * @method static Builder|Tag whereCreatedAt($value)
 * @method static Builder|Tag whereDeletedAt($value)
 * @method static Builder|Tag whereId($value)
 * @method static Builder|Tag whereName($value)
 * @method static Builder|Tag whereSlug($value)
 * @method static Builder|Tag whereTenantId($value)
 * @method static Builder|Tag whereUpdatedAt($value)
 * @method static Builder|Tag withTrashed()
 * @method static Builder|Tag withoutTrashed()
 * @mixin Model
 */
class Tag extends Model
{
    use SoftDeletes;
    use AppSluggable;
    use BelongsToTenant;

    protected $table = 'tags';

    protected $guarded = [];

    /**
     * Get the configuration array for generating slugs.
     *
     * @return array<string, array<string, string>>
     */
    public function sluggable(): array
    {
        return $this->appSlugConfig('name', 0);
    }

    /**
     * Get the posts that belong to this tag.
     *
     * @return BelongsToMany<Post, Tag>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tags');
    }
}