<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Tests\Boilerplate\Providers;

use Illuminate\Support\ServiceProvider;
use MichaelRubel\AutoBinder\AutoBinder;

class BootServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Services\\Interfaces')
            ->as('singleton')
            ->bind();
    }
}
