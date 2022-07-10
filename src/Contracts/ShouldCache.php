<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Contracts;

interface ShouldCache
{
    /**
     * Identifies the bindings in the cache.
     *
     * @const string
     */
    public const CACHE_KEY = 'binder_';

    /**
     * Get the clue to access the cache.
     *
     * @return string
     */
    public function cacheClue(): string;
}
