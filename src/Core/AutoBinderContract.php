<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Core;

interface AutoBinderContract
{
    /**
     * Defaults & misc.
     *
     * @const
     */
    public const CLASS_SEPARATOR           = '\\';
    public const DEFAULT_FOLDER            = 'app';
    public const DEFAULT_NAMESPACE         = 'App';
    public const DEFAULT_SCAN_FOLDERS      = ['Services'];
    public const DEFAULT_INTERFACE_FOLDER  = 'Interfaces';
    public const DEFAULT_INTERFACE_POSTFIX = 'Interface';
    public const DEFAULT_BINDING_TYPE      = 'singleton';
    public const DEFAULT_SCAN_EXCLUDES     = [
        'Interfaces',
        'Contracts',
        'Traits',
    ];
}
