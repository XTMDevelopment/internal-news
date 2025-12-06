<?php

namespace XTraMile\News\Enums;

/**
 * Enum representing the status of a post.
 *
 * @method static self DRAFT()
 * @method static self PUBLISHED()
 * @method static self SCHEDULED()
 */
enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case SCHEDULED = 'scheduled';
}