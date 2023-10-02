<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Contracts;

interface ShouldCache
{
    /**
     * Container binding prefix used for caching purposes.
     */
    public const CACHE_KEY = 'binder_';

    /**
     * Cache access key.
     */
    public function cacheClue(): string;
}
