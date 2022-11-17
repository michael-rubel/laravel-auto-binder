<?php

namespace MichaelRubel\AutoBinder\Tests\Boilerplate;

use Illuminate\Support\LazyCollection;
use MichaelRubel\AutoBinder\AutoBinder;
use Symfony\Component\Finder\SplFileInfo;

class ExtendedAutoBinder extends AutoBinder
{
    public function bind(): void
    {
        $this->hasCache();
        $this->scan();
        $this->fromCache();

        parent::hasCache();
        parent::scan();
        parent::fromCache();
    }

    protected function scan(): void
    {
        $this->getFolderFiles()->each(
            fn (array $files, string $actualFolder) => LazyCollection::make($files)->each(
                function (SplFileInfo $file) use ($actualFolder) {
                    $relativePath = $file->getRelativePathname();
                    $filenameWithoutExtension = $file->getFilenameWithoutExtension();
                    $filenameWithRelativePath = parent::prepareFilename($relativePath);

                    $interface = parent::interfaceFrom($filenameWithoutExtension);
                    $concrete = parent::concreteFrom($actualFolder, $filenameWithRelativePath);

                    $dependencies = collect($this->dependencies);

                    $concrete = match (true) {
                        $dependencies->has($interface) => $dependencies->get($interface),
                        $dependencies->has($concrete) => $dependencies->get($concrete),
                        default => $concrete,
                    };

                    if (parent::cacheEnabled()) {
                        parent::cacheBindingFor($interface, $concrete);
                    }

                    if (! interface_exists($interface)) {
                        return;
                    }

                    app()->{$this->bindingType}($interface, $concrete);
                }
            )
        );
    }

    public function classNamespace(string $path): static
    {
        $this->classNamespace = parent::namespaceFrom($path);

        return $this;
    }

    protected function namespaceFrom(string $path): string
    {
        return parent::namespaceFrom($path);
    }

    protected function concreteFrom(string $folder, string $filenameWithRelativePath): string
    {
        return parent::prepareActual($folder . '\\')
            . parent::prepareNamingFor($filenameWithRelativePath);
    }

    protected function interfaceFrom(string $filenameWithoutExtension): string
    {
        parent::guessInterfaceBy($filenameWithoutExtension);

        return parent::buildInterfaceBy($filenameWithoutExtension);
    }

    protected function guessInterfaceBy(string $filenameWithoutExtension): ?string
    {
        return parent::buildInterfaceFromClassBy($filenameWithoutExtension);
    }
}
