<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Tests\Boilerplate\Providers;

use Illuminate\Support\ServiceProvider;
use MichaelRubel\AutoBinder\AutoBinder;

class RegisterServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Services\\Interfaces')
            ->as('singleton')
            ->bind();
    }
}
