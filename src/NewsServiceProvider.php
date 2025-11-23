<?php

namespace XTraMile\News;

use Illuminate\Support\ServiceProvider;
use XTraMile\News\Services\PostQueryService;
use XTraMile\News\Services\ViewCounter;

/**
 * Service provider for the News package.
 * 
 * Registers services and loads migrations for the multi-tenant news platform.
 */
class NewsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(PostQueryService::class, function () {
            return new PostQueryService();
        });

        $this->app->singleton(ViewCounter::class, function () {
            return new ViewCounter();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}