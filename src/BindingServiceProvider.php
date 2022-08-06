<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder;

use Illuminate\Cache\CacheServiceProvider;
use MichaelRubel\AutoBinder\Commands\AutoBinderClearCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BindingServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     *
     * @param  Package  $package
     *
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-auto-binder')
            ->hasCommand(AutoBinderClearCommand::class);
    }

    /**
     * @return void
     */
    public function packageRegistered(): void
    {
        if (! app()->bound('cache')) {
            app()->register(CacheServiceProvider::class, true);
        }
    }
}
