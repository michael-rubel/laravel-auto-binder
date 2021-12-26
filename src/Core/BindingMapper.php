<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Core;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MichaelRubel\AutoBinder\Traits\HelpsMapBindings;
use Symfony\Component\Finder\SplFileInfo;

class BindingMapper implements BindingMapperContract
{
    use HelpsMapBindings;

    /**
     * @var string
     */
    protected string $startNamespace;

    /**
     * Auto-maps all the classes.
     */
    public function __construct()
    {
        $namespace = config('auto-binder.start_namespace') ?? self::DEFAULT_NAMESPACE;

        $this->startNamespace = Str::ucfirst(
            $this->cleanupPath(
                is_string($namespace)
                    ? $namespace
                    : self::DEFAULT_NAMESPACE
            )
        );

        collect(config('auto-binder.scan_folders') ?? self::DEFAULT_SCAN_FOLDERS)
            ->each(fn (string $folder) => $this->getFolderFiles($folder)
                ->each(function (SplFileInfo $file) use ($folder) {
                    $relativePath             = $file->getRelativePathname();
                    $filenameWithoutExtension = $file->getFilenameWithoutExtension();
                    $filenameWithRelativePath = $this->cleanupFilename($relativePath);

                    $interface = $this->startNamespace
                        . self::CLASS_SEPARATOR
                        . $folder
                        . self::CLASS_SEPARATOR
                        . (config('auto-binder.interface_folder') ?? self::DEFAULT_INTERFACE_FOLDER)
                        . self::CLASS_SEPARATOR
                        . $filenameWithoutExtension
                        . (config('auto-binder.interface_postfix') ?? self::DEFAULT_INTERFACE_POSTFIX);

                    $implementation = $this->startNamespace
                        . self::CLASS_SEPARATOR
                        . $folder
                        . self::CLASS_SEPARATOR
                        . $filenameWithRelativePath;

                    app()->{
                        config('auto-binder.binding_type') ?? self::DEFAULT_BINDING_TYPE
                    }($interface, $implementation);
                }));
    }

    /**
     * @param string $folder
     *
     * @return Collection
     */
    public function getFolderFiles(string $folder): Collection
    {
        $directory = config('auto-binder.start_folder') ?? self::DEFAULT_FOLDER;

        $path = base_path(is_string($directory) ? $directory : self::DEFAULT_FOLDER)
            . DIRECTORY_SEPARATOR
            . $folder;

        $filesystem = app('files');

        $files = $filesystem->isDirectory($path)
            ? $filesystem->allFiles($path)
            : [];

        return collect($files)->reject(
            fn (SplFileInfo $file) => collect(
                config('auto-binder.exclude_from_scan')
                    ?? self::DEFAULT_SCAN_EXCLUDES
            )->map(fn (string $folder) => str_contains(
                $file->getRelativePath(),
                $folder
            ))->contains(true)
        );
    }
}
