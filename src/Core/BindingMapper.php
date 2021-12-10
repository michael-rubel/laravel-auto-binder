<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Core;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MichaelRubel\AutoBinder\Traits\HelpsMapBindings;
use Symfony\Component\Finder\SplFileInfo;

class BindingMapper
{
    use HelpsMapBindings;

    /**
     * Internal constants.
     *
     * @const
     */
    public const CLASS_SEPARATOR = '\\';

    /**
     * @var string
     */
    protected string $startNamespace;

    /**
     * Auto-maps all the classes.
     */
    public function __construct()
    {
        $namespace = config('auto-binder.start_namespace') ?? 'App';

        $this->startNamespace = Str::ucfirst(
            $this->cleanupPath(
                is_string($namespace)
                    ? $namespace
                    : 'App'
            )
        );

        collect(config('auto-binder.scan_folders') ?? ['Services'])
            ->each(
                fn (string $folder) => $this->getFolderFiles($folder)
                    ->each(function (SplFileInfo $file) use ($folder) {
                        $relativePath             = $file->getRelativePathname();
                        $filenameWithoutExtension = $file->getFilenameWithoutExtension();
                        $filenameWithRelativePath = $this->cleanupFilename($relativePath);

                        $interface = $this->startNamespace
                            . self::CLASS_SEPARATOR
                            . $folder
                            . self::CLASS_SEPARATOR
                            . (config('auto-binder.interface_folder') ?? 'Interfaces')
                            . self::CLASS_SEPARATOR
                            . $filenameWithoutExtension
                            . (config('auto-binder.interface_postfix') ?? 'Interface');

                        $implementation = $this->startNamespace
                            . self::CLASS_SEPARATOR
                            . $folder
                            . self::CLASS_SEPARATOR
                            . $filenameWithRelativePath;

                        app()->{
                            config('auto-binder.binding_type') ?? 'singleton'
                        }($interface, $implementation);
                    })
            );
    }

    /**
     * @param string $folder
     *
     * @return Collection
     */
    public function getFolderFiles(string $folder): Collection
    {
        $directory = config('auto-binder.start_folder') ?? 'app';

        $path = base_path(is_string($directory) ? $directory : 'app')
                . DIRECTORY_SEPARATOR
                . $folder;

        $files = app('files')->isDirectory($path)
            ? app('files')->allFiles($path)
            : [];

        return collect($files)->reject(
            fn (SplFileInfo $file) => collect(config('auto-binder.exclude_from_scan') ?? [
                'Interfaces',
                'Contracts',
                'Traits',
            ])->map(
                fn (string $folder) => str_contains(
                    $file->getRelativePath(),
                    $folder
                )
            )->contains(true)
        );
    }
}
