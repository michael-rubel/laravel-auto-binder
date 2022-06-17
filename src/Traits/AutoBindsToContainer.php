<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
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
        $this->getFolderFiles()->each(function (SplFileInfo $file) {
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
     * Get the folder files except for ignored ones.
     *
     * @return Collection
     */
    protected function getFolderFiles(): Collection
    {
        return collect(File::directories(base_path($this->basePath . DIRECTORY_SEPARATOR . $this->classFolder)))
            ->reject(fn ($folder) => in_array(basename($folder), $this->excludesFolders))
            ->map(fn ($folder) => File::allFiles($folder))
            ->flatten();
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
     * Get the concrete from filename.
     *
     * @param string $filenameWithRelativePath
     *
     * @return string
     */
    protected function concreteFrom(string $filenameWithRelativePath): string
    {
        return $this->classNamespace . '\\'
            . $this->classFolder . '\\'
            . $this->prepareNamingFor($filenameWithRelativePath);
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
        $guessedInterface = $this->guessInterfaceBy($filenameWithoutExtension);

        if (is_null($guessedInterface)) {
            return $this->buildInterfaceBy($filenameWithoutExtension);
        }

        return $guessedInterface;
    }

    /**
     * Guess the interface with a given filename.
     *
     * @param string $filenameWithoutExtension
     *
     * @return string|null
     */
    protected function guessInterfaceBy(string $filenameWithoutExtension): ?string
    {
        if (! Str::contains($this->interfaceNamespace, '\\')) {
            return $this->buildInterfaceFromClassBy($filenameWithoutExtension);
        }

        return null;
    }

    /**
     * Build the interface class-string.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function buildInterfaceBy(string $filename): string
    {
        return $this->interfaceNamespace . '\\'
            . $this->prepareNamingFor($filename)
            . ($this->interfaceNaming);
    }

    /**
     * Build the interface class-string based on the class folder.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function buildInterfaceFromClassBy(string $filename): string
    {
        return $this->classNamespace . '\\'
            . $this->classFolder . '\\'
            . $this->interfaceNamespace . '\\'
            . $this->prepareNamingFor($filename)
            . ($this->interfaceNaming);
    }

    /**
     * Cleans up filename to append the desired interface name.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function prepareNamingFor(string $filename): string
    {
        return Str::replace($this->interfaceNaming, '', $filename);
    }
}
