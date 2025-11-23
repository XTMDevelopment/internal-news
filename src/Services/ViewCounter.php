<?php

namespace Services;

use Models\Post;
use Models\PostView;

class ViewCounter
{
    public function record(Post $post): void
    {
        $sessionKey = 'viewed_post_' . $post->id;

        if (session()->has($sessionKey)) {
            return;
        }

        session()->put($sessionKey, true);

        PostView::created([
            'tenant_id' => $post->tenant->id,
            'post_id' => $post->id,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->headers->get('referer'),
            'viewed_at' => now(),
        ]);

        $post->increment('views_total');
    }
}