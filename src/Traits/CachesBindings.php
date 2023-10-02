<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait CachesBindings
{
    /**
     * Determines if the caching is enabled.
     */
    public bool $caching = true;

    /**
     * Disables the caching.
     */
    public function withoutCaching(): static
    {
        $this->caching = false;

        return $this;
    }

    /**
     * Get the clue to access the cache.
     */
    public function cacheClue(): string
    {
        return config('app.name') . static::CACHE_KEY . $this->classFolder;
    }

    /**
     * Check if the caching is enabled.
     */
    protected function cacheEnabled(): bool
    {
        return isset($this->caching) && $this->caching && ! app()->isLocal();
    }

    /**
     * Check if the caching is enabled.
     */
    protected function hasCache(): bool
    {
        return $this->cacheEnabled() && cache()->has($this->cacheClue());
    }

    /**
     * Use the bindings from the cache.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function fromCache(): void
    {
        collect(cache()->get($this->cacheClue()))->each(
            fn ($concrete, $interface) => app()->{$this->bindingType}($interface, $concrete)
        );
    }

    /**
     * Cache the binding.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function cacheBindingFor(string $interface, Closure|string $concrete): void
    {
        $clue = $this->cacheClue();

        $cache = cache()->get($clue);

        $cache[$interface] = $concrete;

        cache()->put($clue, $cache);
    }
}
