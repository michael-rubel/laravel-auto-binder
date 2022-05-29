<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

trait AutoBinds
{
    /**
     * @var string
     */
    private string $namespace;

    /**
     * @return void
     */
    private function prepareNamespace(): void
    {
        $this->namespace = Str::ucfirst(
            strtr(config('auto-binder.start_namespace', self::DEFAULT_NAMESPACE), '/', '\\')
        );
    }

    /**
     * @param string $folder
     *
     * @return Collection
     */
    private function getFolderFiles(string $folder): Collection
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
     * @param string $filename
     *
     * @return string
     */
    private function cleanupFilename(string $filename): string
    {
        return strtr(
            substr(
                $filename,
                0,
                (int) strrpos($filename, '.')
            ),
            '/',
            '\\'
        );
    }

    /**
     * @param string $folder
     * @param string $filenameWithoutExtension
     *
     * @return string
     */
    private function getInterface(string $folder, string $filenameWithoutExtension): string
    {
        return $this->namespace
            . self::CLASS_SEPARATOR
            . $folder
            . self::CLASS_SEPARATOR
            . (config('auto-binder.interface_folder', self::DEFAULT_INTERFACE_FOLDER))
            . self::CLASS_SEPARATOR
            . $filenameWithoutExtension
            . (config('auto-binder.interface_postfix', self::DEFAULT_INTERFACE_POSTFIX));
    }

    /**
     * @param string $folder
     * @param string $filenameWithRelativePath
     *
     * @return string
     */
    private function getImplementation(string $folder, string $filenameWithRelativePath): string
    {
        return $this->namespace
            . self::CLASS_SEPARATOR
            . $folder
            . self::CLASS_SEPARATOR
            . $filenameWithRelativePath;
    }

    /**
     * @param string $interface
     * @param string $implementation
     *
     * @return void
     */
    private function bind(string $interface, string $implementation): void
    {
        app()->{config('auto-binder.binding_type', self::DEFAULT_BINDING_TYPE)}($interface, $implementation);
    }
}
