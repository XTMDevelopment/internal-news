<?php

namespace XTraMile\News\Traits;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Trait for generating slugs from model attributes.
 *
 * Provides a convenient method to configure slug generation with optional random suffixes.
 */
trait AppSluggable
{
    use Sluggable;

    /**
     * Get the configuration array for generating slugs.
     *
     * @param string $source The attribute name to generate the slug from
     * @param int $randomSuffix The length of random suffix to append for uniqueness (0 to disable)
     * @return array<string, array<string, string|bool|callable|null>>
     */
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