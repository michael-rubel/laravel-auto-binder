<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Core;

use Illuminate\Support\Collection;
use MichaelRubel\AutoBinder\Traits\AutoBinds;
use Symfony\Component\Finder\SplFileInfo;

class AutoBinder implements AutoBinderContract
{
    use AutoBinds;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->performAutoBinding();
    }

    /**
     * @return void
     */
    private function performAutoBinding(): void
    {
        collect(config('auto-binder.scan_folders', self::DEFAULT_SCAN_FOLDERS))
            ->pipe(function (Collection $folders) {
                $this->prepareNamespace();

                return $folders;
            })
            ->each(fn (string $folder) => $this->getFolderFiles($folder)
                ->each(function (SplFileInfo $file) use ($folder) {
                    $relativePath             = $file->getRelativePathname();
                    $filenameWithoutExtension = $file->getFilenameWithoutExtension();
                    $filenameWithRelativePath = $this->cleanupFilename($relativePath);

                    $interface      = $this->getInterface($folder, $filenameWithoutExtension);
                    $implementation = $this->getImplementation($folder, $filenameWithRelativePath);

                    $this->bind($interface, $implementation);
                })
            );
    }
}
