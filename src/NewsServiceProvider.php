<?php

namespace XTraMile\News;

use Illuminate\Support\ServiceProvider;
use XTraMile\News\Services\PostQueryService;
use XTraMile\News\Services\ViewCounter;

class NewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostQueryService::class, function () {
            return new PostQueryService();
        });

        $this->app->singleton(ViewCounter::class, function () {
            return new ViewCounter();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}