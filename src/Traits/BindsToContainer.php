<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Finder\SplFileInfo;

trait BindsToContainer
{
    /**
     * Run the directory scanning & bind the results.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function scan(): void
    {
        $this->getFolderFiles()->each(
            fn (array $files, string $actualFolder) => LazyCollection::make($files)->each(
                function (SplFileInfo $file) use ($actualFolder) {
                    $relativePath = $file->getRelativePathname();
                    $filenameWithoutExtension = $file->getFilenameWithoutExtension();
                    $filenameWithRelativePath = $this->prepareFilename($relativePath);

                    $interface = $this->interfaceFrom($filenameWithoutExtension);
                    $concrete = $this->concreteFrom($actualFolder, $filenameWithRelativePath);

                    if (! interface_exists($interface) || ! class_exists($concrete)) {
                        return;
                    }

                    $dependencies = collect($this->dependencies);

                    $concrete = match (true) {
                        $dependencies->has($interface) => $dependencies->get($interface),
                        $dependencies->has($concrete) => $dependencies->get($concrete),
                        default => $concrete,
                    };

                    if (isset($this->caching) && $this->caching) {
                        $this->cacheBindingFor($interface, $concrete);
                    }

                    app()->{$this->bindingType}($interface, $concrete);
                }
            )
        );
    }

    /**
     * Get the folder files except for ignored ones.
     *
     * @return LazyCollection
     */
    protected function getFolderFiles(): LazyCollection
    {
        return LazyCollection::make(File::directories(base_path($this->basePath . DIRECTORY_SEPARATOR . $this->classFolder)))
            ->reject(fn (string $folder) => in_array(basename($folder), $this->excludesFolders))
            ->mapWithKeys(fn (string $folder) => [basename($folder) => File::allFiles($folder)]);
    }

    /**
     * Prepare the filename.
     *
     * @param  string  $filename
     *
     * @return string
     */
    protected function prepareFilename(string $filename): string
    {
        return str($filename)
            ->replace('/', '\\')
            ->substr(0, strrpos($filename, '.'))
            ->value();
    }

    /**
     * Get the namespace from a given path.
     *
     * @param  string  $path
     *
     * @return string
     */
    protected function namespaceFrom(string $path): string
    {
        return str($path)
            ->replace('/', '\\')
            ->ucfirst()
            ->value();
    }

    /**
     * Get the concrete from filename.
     *
     * @param  string  $folder
     * @param  string  $filenameWithRelativePath
     *
     * @return string
     */
    protected function concreteFrom(string $folder, string $filenameWithRelativePath): string
    {
        return $this->classNamespace . '\\'
            . $this->classFolder . '\\'
            . $this->prepareActual($folder . '\\')
            . $this->prepareNamingFor($filenameWithRelativePath);
    }

    /**
     * Get the interface from filename.
     *
     * @param  string  $filenameWithoutExtension
     *
     * @return string
     */
    protected function interfaceFrom(string $filenameWithoutExtension): string
    {
        $guessedInterface = $this->guessInterfaceBy($filenameWithoutExtension);

        return ! is_null($guessedInterface)
            ? $guessedInterface
            : $this->buildInterfaceBy($filenameWithoutExtension);
    }

    /**
     * Guess the interface with a given filename.
     *
     * @param  string  $filenameWithoutExtension
     *
     * @return string|null
     */
    protected function guessInterfaceBy(string $filenameWithoutExtension): ?string
    {
        return ! Str::contains($this->interfaceNamespace, '\\')
            ? $this->buildInterfaceFromClassBy($filenameWithoutExtension)
            : null;
    }

    /**
     * Build the interface class-string.
     *
     * @param  string  $filename
     *
     * @return string
     */
    protected function buildInterfaceBy(string $filename): string
    {
        return $this->interfaceNamespace . '\\'
            . $this->prepareNamingFor($filename)
            . $this->interfaceNaming;
    }

    /**
     * Build the interface class-string based on the class folder.
     *
     * @param  string  $filename
     *
     * @return string
     */
    protected function buildInterfaceFromClassBy(string $filename): string
    {
        return $this->classNamespace . '\\'
            . $this->classFolder . '\\'
            . $this->interfaceNamespace . '\\'
            . $this->prepareNamingFor($filename)
            . $this->interfaceNaming;
    }

    /**
     * Cleans up filename to append the desired interface name.
     *
     * @param  string  $filename
     *
     * @return string
     */
    protected function prepareNamingFor(string $filename): string
    {
        return Str::replace($this->interfaceNaming, '', $filename);
    }

    /**
     * prepares an actual folder.
     *
     * @param  string  $folder
     *
     * @return string
     */
    protected function prepareActual(string $folder): string
    {
        return Str::replace(Str::plural($this->interfaceNaming) . '\\', '', $folder);
    }
}
