<?php

namespace XTraMile\News\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * XTraMile\News\Models\Tenant
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $domain
 * @property string $theme
 * @property string|null $logo_path
 * @property array|null $settings
 * @property bool $is_active
 * @property Collection<int, Post> $posts
 * @property int|null $posts_count
 * @property Collection<int, Category> $categories
 * @property int|null $categories_count
 * @property Collection<int, Tag> $tags
 * @property int|null $tags_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Tenant newModelQuery()
 * @method static Builder|Tenant newQuery()
 * @method static Builder|Tenant onlyTrashed()
 * @method static Builder|Tenant query()
 * @method static Builder|Tenant whereDomain($value)
 * @method static Builder|Tenant whereId($value)
 * @method static Builder|Tenant whereIsActive($value)
 * @method static Builder|Tenant whereLogoPath($value)
 * @method static Builder|Tenant whereName($value)
 * @method static Builder|Tenant whereSettings($value)
 * @method static Builder|Tenant whereSlug($value)
 * @method static Builder|Tenant whereTheme($value)
 * @method static Builder|Tenant withTrashed()
 * @method static Builder|Tenant withoutTrashed()
 * @mixin Model
 */
class Tenant extends Model
{
    use SoftDeletes;

    protected $table = 'tenants';

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}