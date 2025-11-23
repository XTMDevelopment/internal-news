<?php

namespace XTraMile\News\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use XTraMile\News\Traits\BelongsToTenant;

/**
 * XTraMile\News\Models\PostView
 *
 * @property int $id
 * @property int $post_id
 * @property int $tenant_id
 * @property string|null $session_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $referer
 * @property Carbon $viewed_at
 * @property-read Post $post
 * @property-read Tenant $tenant
 * @method static Builder|PostView newModelQuery()
 * @method static Builder|PostView newQuery()
 * @method static Builder|PostView query()
 * @method static Builder|PostView whereId($value)
 * @method static Builder|PostView whereIpAddress($value)
 * @method static Builder|PostView wherePostId($value)
 * @method static Builder|PostView whereReferer($value)
 * @method static Builder|PostView whereSessionId($value)
 * @method static Builder|PostView whereTenantId($value)
 * @method static Builder|PostView whereUserAgent($value)
 * @method static Builder|PostView whereViewedAt($value)
 * @mixin Model
 */
class PostView extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $table = 'post_views';

    protected $guarded = [];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}