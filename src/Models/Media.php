<?php

namespace XTraMile\News\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * XTraMile\News\Models\Media
 *
 * @property int $id
 * @property string $file_name
 * @property string $path
 * @property int $tenant_id
 * @property int $post_id
 * @property string $r2_path
 * @property int $file_size
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Tenant $tenant
 * @property-read Post|null $post
 * @method static Builder|Media newModelQuery()
 * @method static Builder|Media newQuery()
 * @method static Builder|Media query()
 * @method static Builder|Media whereCreatedAt($value)
 * @method static Builder|Media whereFileName($value)
 * @method static Builder|Media whereFileSize($value)
 * @method static Builder|Media whereId($value)
 * @method static Builder|Media wherePath($value)
 * @method static Builder|Media wherePostId($value)
 * @method static Builder|Media whereR2Path($value)
 * @method static Builder|Media whereTenantId($value)
 * @method static Builder|Media whereUpdatedAt($value)
 * @mixin Model
 */
class Media extends Model
{
    protected $table = 'medias';

    protected $fillable = [
        'file_name',
        'path',
        'tenant_id',
        'r2_path',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'tenant_id' => 'integer',
        'post_id' => 'integer',
    ];

    /**
     * Get the tenant that this media belongs to.
     *
     * @return BelongsTo<Tenant, Media>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the post that this media belongs to.
     *
     * @return BelongsTo<Post, Media>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}