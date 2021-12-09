<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-binder configuration
    |--------------------------------------------------------------------------
    |
    | The package automatically creates the Service Container binding for an interface from the
    | subsequent folder to implementation with an identical class name structure from the base folder.
    |
    | For example: App\Services\YourService => App\Services\Interfaces\YourServiceInterface
    |
    | This helps avoid manually registering container bindings when the project needs to
    | bind a lot of interfaces to its implementations without any additional dependencies.
    |
    | Determine if you want to enable the package.
    |
    */

    'enabled' => true,

    /*
     * Determine which type of bindings you want to use.
     *
     * Available binding types: `bind`, `singleton`, `scoped`.
     *
     * Default: `singleton`
     */

    'binding_type' => 'singleton',

    /*
     * You can customize the folder/interface postfix.
     */

    'interface_postfix' => 'Interface',
    'interface_folder'  => 'Interfaces',

    /*
     * Define any folder/namespace structure you want.
     */

    'start_namespace' => 'App',
    'start_folder'    => 'app',
    'class_folders'   => [
        'Services',
        'Models',
    ],

    /*
     * Ignored folders when scanning files.
     *
     * Add any folders you want to exclude while searching your implementations.
     */

    'ignored_class_folders' => [
        'Interfaces',
        'Contracts',
        'Traits',
    ],
];
