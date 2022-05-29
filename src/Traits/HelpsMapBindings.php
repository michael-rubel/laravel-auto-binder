<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

use Illuminate\Support\Str;

trait HelpsMapBindings
{
    /**
     * @param string $path
     *
     * @return string
     */
    protected function cleanupPath(string $path): string
    {
        return Str::ucfirst(
            strtr($path, '/', '\\')
        );
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function cleanupFilename(string $filename): string
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
    protected function getInterface(string $folder, string $filenameWithoutExtension): string
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
    protected function getImplementation(string $folder, string $filenameWithRelativePath): string
    {
        return $this->namespace
            . self::CLASS_SEPARATOR
            . $folder
            . self::CLASS_SEPARATOR
            . $filenameWithRelativePath;
    }
}
