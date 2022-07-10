<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait CachesBindings
{
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
        $clue = static::CACHE_KEY . $this->classFolder;

        $cache = cache()->get($clue);

        $cache[$interface] = $concrete;

        cache()->put($clue, $cache);
    }

    /**
     * Apply the caching.
     *
     * @param  string  $clue
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function applyCachingBy(string $clue): void
    {
        collect(cache()->get($clue))->each(
            fn ($concrete, $interface) => app()->{$this->bindingType}($interface, $concrete)
        );
    }
}
