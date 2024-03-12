<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MichaelRubel\AutoBinder\Contracts\ShouldCache;
use MichaelRubel\AutoBinder\Traits\BindsToContainer;
use MichaelRubel\AutoBinder\Traits\CachesBindings;

class AutoBinder implements ShouldCache
{
    use BindsToContainer, CachesBindings;

    /**
     * Base class namespace.
     */
    public string $classNamespace = 'App';

    /**
     * Target class folder.
     */
    public string $classFolder = 'Services';

    /**
     * Interface namespace (can be fully qualified).
     */
    public string $interfaceNamespace = 'Interfaces';

    /**
     * Postfix convention for interfaces.
     */
    public string $interfaceNaming = 'Interface';

    /**
     * Base class folder.
     */
    public string $basePath = 'app';

    /**
     * The type of bindings.
     */
    public string $bindingType = 'bind';

    /**
     * When the class name is met, these dependencies are passed to the concrete.
     */
    public array $dependencies = [];

    /**
     * Subdirectories to ignore when scanning.
     */
    public array $excludesFolders = [];

    /**
     * Assign a new class folder.
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
     * @return static|Collection<int, static>
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
     */
    public function exclude(string|array $folders): static
    {
        $folders = is_array($folders) ? $folders : func_get_args();

        collect($folders)->each(
            fn ($folder) => $this->excludesFolders[] = $folder
        );

        return $this;
    }

    /**
     * Set the class base path.
     */
    public function basePath(string $basePath): static
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Define the class namespace.
     */
    public function classNamespace(string $path): static
    {
        $this->classNamespace = $this->namespaceFrom($path);

        return $this;
    }

    /**
     * Define the interface namespace.
     */
    public function interfaceNamespace(string $path): static
    {
        $this->interfaceNamespace = $this->namespaceFrom($path);

        return $this;
    }

    /**
     * Define the interface postfix.
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
     */
    public function when(string $abstract, Closure $callback): static
    {
        $this->dependencies[$abstract] = $callback;

        return $this;
    }

    /**
     * Bind the result as a specific type of binding.
     */
    public function as(string $type): static
    {
        if (! Str::is(['bind', 'scoped', 'singleton'], $type)) {
            throw new InvalidArgumentException('Invalid binding type.');
        }

        $this->bindingType = $type;

        return $this;
    }

    /**
     * Perform the scan & binding.
     */
    public function bind(): void
    {
        if ($this->hasCache()) {
            $this->usesCache = true;

            $this->fromCache();

            return;
        }

        $this->scan();
    }
}
