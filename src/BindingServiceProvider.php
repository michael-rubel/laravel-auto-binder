<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder;

use MichaelRubel\AutoBinder\Core\BindingMapper;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BindingServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     *
     * @param Package $package
     *
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-auto-binder')
            ->hasConfigFile();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function packageRegistered(): void
    {
        if (config('auto-binder.enabled') ?? false) {
            $directoryPath = config('auto-binder.start_folder') ?? 'app';

            $directoryExists = is_string($directoryPath)
                && app('files')->isDirectory(
                    base_path($directoryPath)
                );

            if ($directoryExists) {
                app(BindingMapper::class);
            }
        }
    }
}
