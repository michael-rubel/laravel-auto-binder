<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

trait AutoBindsToContainer
{
    /**
     * Run the folders scanning & bind the results.
     *
     * @return void
     */
    protected function run(): void
    {
        collect($this->getFolderFiles())
            ->each(function (SplFileInfo $file) {
                $relativePath             = $file->getRelativePathname();
                $filenameWithoutExtension = $file->getFilenameWithoutExtension();
                $filenameWithRelativePath = $this->prepareFilename($relativePath);

                $interface = $this->interfaceFrom($filenameWithoutExtension);
                $concrete  = $this->concreteFrom($filenameWithRelativePath);

                if (! interface_exists($interface) || ! class_exists($concrete)) {
                    return;
                }

                $dependencies = collect($this->dependencies);

                $concrete = match (true) {
                    $dependencies->has($interface) => $dependencies->get($interface),
                    $dependencies->has($concrete)  => $dependencies->get($concrete),
                    default                        => $concrete,
                };

                app()->{$this->bindingType}($interface, $concrete);
            });
    }

    /**
     * Get the folder files.
     *
     * @return array
     */
    protected function getFolderFiles(): array
    {
        $filesystem = app('files');

        $folder = base_path($this->basePath . DIRECTORY_SEPARATOR . $this->classFolder);

        return $filesystem->isDirectory($folder)
            ? $filesystem->allFiles($folder)
            : [];
    }

    /**
     * Prepare the filename.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function prepareFilename(string $filename): string
    {
        return (string) Str::of($filename)
            ->replace('/', '\\')
            ->substr(0, (int) strrpos($filename, '.'));
    }

    /**
     * Get the namespace from a given path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function namespaceFrom(string $path): string
    {
        return (string) Str::of($path)
            ->replace('/', '\\')
            ->ucfirst();
    }

    /**
     * Get the interface from filename.
     *
     * @param string $filenameWithoutExtension
     *
     * @return string
     */
    protected function interfaceFrom(string $filenameWithoutExtension): string
    {
        $interface = $this->guessInterfaceWith($filenameWithoutExtension);

        if (is_null($interface)) {
            return $this->interfaceNamespace
                . '\\'
                . $filenameWithoutExtension
                . ($this->interfaceNaming);
        }

        return $interface;
    }

    /**
     * Guess the interface with a given filename.
     *
     * @param string $filenameWithoutExtension
     *
     * @return string|null
     */
    protected function guessInterfaceWith(string $filenameWithoutExtension): ?string
    {
        if (! Str::contains($this->interfaceNamespace, '\\')) {
            return $this->classNamespace
                . '\\'
                . $this->classFolder
                . '\\'
                . $this->interfaceNamespace
                . '\\'
                . $filenameWithoutExtension
                . ($this->interfaceNaming);
        }

        return null;
    }

    /**
     * Get the concrete from filename.
     *
     * @param string $filenameWithRelativePath
     *
     * @return string
     */
    protected function concreteFrom(string $filenameWithRelativePath): string
    {
        return $this->classNamespace . '\\' . $this->classFolder . '\\' . $filenameWithRelativePath;
    }
}
