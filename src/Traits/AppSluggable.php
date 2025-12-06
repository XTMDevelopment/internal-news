<?php

namespace XTraMile\News\Traits;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait AppSluggable
{
    use Sluggable;

    /** @noinspection PhpUnusedParameterInspection */
    public function appSlugConfig(string $source, int $randomSuffix = 12): array
    {
        $uniqueSuffix = null;
        if ($randomSuffix > 0) {
            $uniqueSuffix = function (string $slug, string $separator, Collection $list) use ($randomSuffix) {
                return Str::random($randomSuffix);
            };
        }

        return [
            'slug' => [
                'source' => $source,
                'unique' => true,
                'separator' => '-',
                'onUpdate' => true,
                'uniqueSuffix' => $uniqueSuffix,
            ]
        ];
    }
}