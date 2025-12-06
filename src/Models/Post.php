<?php

namespace XTraMile\News\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use XTraMile\News\Traits\AppSluggable;
use XTraMile\News\Traits\BelongsToTenant;

/**
 * XTraMile\News\Models\Post
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $author_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $content
 * @property string $status
 * @property Carbon|null $published_at
 * @property Carbon|null $scheduled_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $featured_image
 * @property int $views_total
 * @property int $views_weekly
 * @property bool $is_pinned
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Tenant $tenant
 * @property-read Collection<int, Category> $categories
 * @property-read int|null $categories_count
 * @property-read Collection<int, Tag> $tags
 * @property-read int|null $tags_count
 * @property-read Collection<int, PostView> $views
 * @property-read int|null $views_count
 * @property-read Collection<int, Media> $medias
 * @property-read int|null $medias_count
 * @method static Builder|Post newModelQuery()
 * @method static Builder|Post newQuery()
 * @method static Builder|Post onlyTrashed()
 * @method static Builder|Post query()
 * @method static Builder|Post whereAuthorId($value)
 * @method static Builder|Post whereContent($value)
 * @method static Builder|Post whereCreatedAt($value)
 * @method static Builder|Post whereDeletedAt($value)
 * @method static Builder|Post whereExcerpt($value)
 * @method static Builder|Post whereFeaturedImage($value)
 * @method static Builder|Post whereId($value)
 * @method static Builder|Post whereIsPinned($value)
 * @method static Builder|Post whereMetaTitle($value)
 * @method static Builder|Post whereMetaDescription($value)
 * @method static Builder|Post wherePublishedAt($value)
 * @method static Builder|Post whereScheduledAt($value)
 * @method static Builder|Post whereSlug($value)
 * @method static Builder|Post whereStatus($value)
 * @method static Builder|Post whereTenantId($value)
 * @method static Builder|Post whereTitle($value)
 * @method static Builder|Post whereUpdatedAt($value)
 * @method static Builder|Post whereViewsTotal($value)
 * @method static Builder|Post whereViewsWeekly($value)
 * @method static Builder|Post withTrashed()
 * @method static Builder|Post withoutTrashed()
 * @mixin Model
 */
class Post extends Model
{
    use SoftDeletes;
    use AppSluggable;
    use BelongsToTenant;

    protected $table = 'posts';

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'is_pinned' => 'boolean',
        'views_total' => 'integer',
        'views_weekly' => 'integer',
    ];

    /**
     * Get the configuration array for generating slugs.
     *
     * @return array<string, array<string, string>>
     */
    public function sluggable(): array
    {
        return $this->appSlugConfig('title');
    }

    /**
     * Get the categories that belong to this post.
     *
     * @return BelongsToMany<Category, Post>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_categories');
    }

    /**
     * Get the tags that belong to this post.
     *
     * @return BelongsToMany<Tag, Post>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    /**
     * Get the views for this post.
     *
     * @return HasMany<PostView>
     */
    public function views(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    /**
     * Get the medias for this post.
     *
     * @return HasMany<Media>
     */
    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}