<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

trait HelpsBindingMapper
{
    /**
     * @param string $filename
     *
     * @return string
     */
    public function cleanupFilename(string $filename): string
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
     * @param string $path
     *
     * @return string
     */
    public function cleanupPath(string $path): string
    {
        return strtr($path, '/', '\\');
    }
}
