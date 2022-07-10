<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

trait InteractsWithCache
{
    /**
     * Determines if the caching is enabled.
     *
     * @var bool
     */
    public bool $caching = true;

    /**
     * Disables the caching.
     *
     * @return static
     */
    public function withoutCaching(): static
    {
        $this->caching = false;

        return $this;
    }

    /**
     * Get the clue to access the cache.
     *
     * @return string
     */
    public function cacheClue(): string
    {
        return static::CACHE_KEY . $this->classFolder;
    }

    /**
     * Check if the caching is enabled.
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function hasCache(): bool
    {
        return $this->caching && cache()->has($this->cacheClue());
    }

    /**
     * Use the bindings from the cache.
     *
     * @return void
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
     * @param  string  $interface
     * @param  \Closure|string  $concrete
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function cacheBindingFor(string $interface, \Closure|string $concrete): void
    {
        $clue = $this->cacheClue();

        $cache = cache()->get($clue);

        $cache[$interface] = $concrete;

        cache()->put($clue, $cache);
    }
}
