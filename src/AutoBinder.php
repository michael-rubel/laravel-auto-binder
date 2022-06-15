<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder;

use Illuminate\Support\Collection;
use MichaelRubel\AutoBinder\Traits\AutoBindsToContainer;

class AutoBinder
{
    use AutoBindsToContainer;

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
    public string $interfacePostfix = 'Interface';

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
    public string $bindingType = 'singleton';

    /**
     * Assign a new class folder.
     *
     * @param string $classFolder
     */
    final public function __construct(string $classFolder)
    {
        $this->classFolder = $classFolder;
    }

    /**
     * Create the object with target folder assigned.
     *
     * @param string|array $folder
     *
     * @return static|Collection
     */
    public static function from(string|array $folder): static|Collection
    {
        $folders = is_array($folder) ? $folder : func_get_args();

        return count($folders) === 1 ? new static($folder) : collect($folders)->map(
            fn ($folder) => new static($folder)
        );
    }

    /**
     * Set the class base path.
     *
     * @param string $basePath
     *
     * @return $this
     */
    public function basePath(string $basePath): static
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Define the class namespace.
     *
     * @param string $path
     *
     * @return $this
     */
    public function classNamespace(string $path): static
    {
        $this->classNamespace = $this->namespaceFrom($path);

        return $this;
    }

    /**
     * Define the interface namespace.
     *
     * @param string $path
     *
     * @return $this
     */
    public function interfaceNamespace(string $path): static
    {
        $this->interfaceNamespace = $this->namespaceFrom($path);

        return $this;
    }

    /**
     * Bind the result as a specific type of binding.
     *
     * @param string $bindingType
     *
     * @return $this
     */
    public function as(string $bindingType): static
    {
        $this->bindingType = $bindingType;

        return $this;
    }

    /**
     * Perform the bindings.
     *
     * @return void
     */
    public function bind(): void
    {
        $this->run();
    }
}
