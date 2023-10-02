<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder;

use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use MichaelRubel\AutoBinder\Commands\AutoBinderClearCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BindingServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-auto-binder')
            ->hasCommand(AutoBinderClearCommand::class);
    }

    /**
     * Register bindings.
     */
    public function registeringPackage(): void
    {
        if (! $this->app->bound('cache')) {
            $this->app->register(CacheServiceProvider::class, true);
        }

        if (! $this->app->bound('redis')) {
            $this->app->register(RedisServiceProvider::class);
        }
    }
}
