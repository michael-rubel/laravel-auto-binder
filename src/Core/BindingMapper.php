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
    public string $namespace;

    /**
     * Auto-binds all the stuff.
     */
    public function __construct()
    {
        $this->namespace = $this->cleanupPath(
            config('auto-binder.start_namespace', self::DEFAULT_NAMESPACE)
        );

        collect(config('auto-binder.scan_folders', self::DEFAULT_SCAN_FOLDERS))
            ->each(fn (string $folder) => $this->getFolderFiles($folder)
                ->each(function (SplFileInfo $file) use ($folder) {
                    $relativePath             = $file->getRelativePathname();
                    $filenameWithoutExtension = $file->getFilenameWithoutExtension();
                    $filenameWithRelativePath = $this->cleanupFilename($relativePath);

                    $interface      = $this->getInterface($folder, $filenameWithoutExtension);
                    $implementation = $this->getImplementation($folder, $filenameWithRelativePath);

                    $this->bind($interface, $implementation);
                }));
    }

    /**
     * @param string $folder
     *
     * @return Collection
     */
    protected function getFolderFiles(string $folder): Collection
    {
        $path = base_path(config('auto-binder.start_folder', self::DEFAULT_FOLDER))
            . DIRECTORY_SEPARATOR
            . $folder;

        $filesystem = app('files');

        $files = $filesystem->isDirectory($path)
            ? $filesystem->allFiles($path)
            : [];

        return collect($files)->reject(
            fn (SplFileInfo $file) => collect(config('auto-binder.exclude_from_scan', self::DEFAULT_SCAN_EXCLUDES))
                ->map(fn (string $folder) => str_contains($file->getRelativePath(), $folder))
                ->contains(true)
        );
    }

    /**
     * @param string $interface
     * @param string $implementation
     *
     * @return void
     */
    protected function bind(string $interface, string $implementation): void
    {
        app()->{
            config('auto-binder.binding_type', self::DEFAULT_BINDING_TYPE)
        }($interface, $implementation);
    }
}
