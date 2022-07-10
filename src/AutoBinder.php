<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MichaelRubel\AutoBinder\Traits\BindsToContainer;

class AutoBinder
{
    use BindsToContainer;

    /**
     * Base class namespace.
     *
     * @var string
     */
    public string $classNamespace = 'App';

    /**
     * Target class folder.
     *
     * @var string
     */
    public string $classFolder = 'Services';

    /**
     * Interface namespace (can be fully qualified).
     *
     * @var string
     */
    public string $interfaceNamespace = 'Interfaces';

    /**
     * Postfix convention for interfaces.
     *
     * @var string
     */
    public string $interfaceNaming = 'Interface';

    /**
     * Base class folder.
     *
     * @var string
     */
    public string $basePath = 'app';

    /**
     * The type of bindings.
     *
     * @var string
     */
    public string $bindingType = 'bind';

    /**
     * When the class name is met, these dependencies are passed to the concrete.
     *
     * @var array
     */
    public array $dependencies = [];

    /**
     * Subdirectories to ignore when scanning.
     *
     * @var array
     */
    public array $excludesFolders = [];

    /**
     * Determines if the caching is enabled.
     *
     * @var bool
     */
    public bool $caching = true;

    /**
     * Identifies the bindings in the cache.
     *
     * @const string
     */
    public const CACHE_KEY = 'binder_';

    /**
     * Assign a new class folder.
     *
     * @param  string|null  $classFolder
     */
    final public function __construct(?string $classFolder = null)
    {
        if ($classFolder) {
            $this->classFolder = $classFolder;
        }
    }

    /**
     * Create the object with target folder assigned.
     *
     * @param  string|array  $folder
     *
     * @return static|Collection
     */
    public static function from(string|array $folder): static|Collection
    {
        if (func_num_args() > 1) {
            $folders = is_array($folder) ? $folder : func_get_args();

            return collect($folders)->map(fn ($folder) => new static($folder));
        }

        $folder = is_string($folder) ? $folder : current($folder);

        return new static($folder);
    }

    /**
     * Exclude specified subdirectory from scanning.
     *
     * @param  string|array  $folders
     *
     * @return static
     */
    public function exclude(string|array $folders): static
    {
        $folders = is_array($folders) ? $folders : func_get_args();

        func_num_args() > 1
            ? collect($folders)->map(fn ($folder) => $this->excludesFolders[] = $folder)
            : $this->excludesFolders[] = current($folders);

        return $this;
    }

    /**
     * Disables the caching.
     *
     * @return static
     */
    public function withoutCaching(): static
    {
        $this->caching = false;

        return $this;
    }

    /**
     * Set the class base path.
     *
     * @param  string  $basePath
     *
     * @return static
     */
    public function basePath(string $basePath): static
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Define the class namespace.
     *
     * @param  string  $path
     *
     * @return static
     */
    public function classNamespace(string $path): static
    {
        $this->classNamespace = $this->namespaceFrom($path);

        return $this;
    }

    /**
     * Define the interface namespace.
     *
     * @param  string  $path
     *
     * @return static
     */
    public function interfaceNamespace(string $path): static
    {
        $this->interfaceNamespace = $this->namespaceFrom($path);

        return $this;
    }

    /**
     * Define the interface postfix.
     *
     * @param  string  $name
     *
     * @return static
     */
    public function interfaceNaming(string $name): static
    {
        $this->interfaceNaming = Str::ucfirst($name);

        if (! Str::contains($this->interfaceNamespace, '\\')) {
            $this->interfaceNamespace(Str::plural($this->interfaceNaming));
        }

        return $this;
    }

    /**
     * Adds dependencies to the class when the class name is met.
     *
     * @param  string  $abstract
     * @param  \Closure  $callback
     *
     * @return static
     */
    public function when(string $abstract, \Closure $callback): static
    {
        $this->dependencies[$abstract] = $callback;

        return $this;
    }

    /**
     * Bind the result as a specific type of binding.
     *
     * @param  string  $type
     *
     * @return static
     */
    public function as(string $type): static
    {
        if (! Str::is(['bind', 'scoped', 'singleton'], $type)) {
            throw new \InvalidArgumentException('Invalid binding type.');
        }

        $this->bindingType = $type;

        return $this;
    }

    /**
     * Perform the scan & binding.
     *
     * @return void
     */
    public function bind(): void
    {
        $clue = static::CACHE_KEY . $this->classFolder;

        $this->caching && cache()->has($clue)
            ? $this->applyCacheBy($clue)
            : $this->scan();
    }
}
