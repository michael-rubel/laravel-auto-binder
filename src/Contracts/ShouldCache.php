<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Contracts;

interface ShouldCache
{
    /**
     * Container binding prefix used for caching purposes.
     *
     * @const string
     */
    public const CACHE_KEY = 'binder_';

    /**
     * Cache access key.
     *
     * @return string
     */
    public function cacheClue(): string;
}
