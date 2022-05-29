<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Traits;

trait HelpsMapBindings
{
    /**
     * @param string $filename
     *
     * @return string
     */
    private function cleanupFilename(string $filename): string
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
    private function cleanupPath(string $path): string
    {
        return strtr($path, '/', '\\');
    }
}
