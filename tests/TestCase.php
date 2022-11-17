<?php

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\BindingServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']['cache.default'] = 'array';

        $this->app->setBasePath(__DIR__ . '/../');
    }

    protected function getPackageProviders($app): array
    {
        return [
            BindingServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('testing');
    }
}
