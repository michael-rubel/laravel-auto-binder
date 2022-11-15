<?php

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\AutoBinder;
use MichaelRubel\AutoBinder\Commands\AutoBinderClearCommand;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Example;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Interfaces\ExampleInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\AnotherService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\ExampleService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\AnotherServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\ExampleServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\TestServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Test\TestService;

class CacheTest extends TestCase
{
    /** @test */
    public function testCachesBindings()
    {
        AutoBinder::from('Services', 'Models')->each(
            fn ($binder) => $binder->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->as('singleton')
                ->bind()
        );

        $this->assertTrue(cache()->has(AutoBinder::CACHE_KEY . 'Services'));
        $services = [
            AnotherServiceInterface::class => AnotherService::class,
            ExampleServiceInterface::class => ExampleService::class,
            TestServiceInterface::class => TestService::class,
        ];
        $this->assertSame($services, cache()->get(AutoBinder::CACHE_KEY . 'Services'));

        $this->assertTrue(cache()->has(AutoBinder::CACHE_KEY . 'Models'));
        $models = [ExampleInterface::class => Example::class];
        $this->assertSame($models, cache()->get(AutoBinder::CACHE_KEY . 'Models'));
    }

    /** @test */
    public function testAvoidsCaching()
    {
        AutoBinder::from('Services', 'Models')->each(
            fn ($binder) => $binder->basePath('tests/Boilerplate')
                ->withoutCaching()
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->as('singleton')
                ->bind()
        );

        $this->assertFalse(cache()->has(AutoBinder::CACHE_KEY . 'Services'));
        $this->assertFalse(cache()->has(AutoBinder::CACHE_KEY . 'Models'));
    }

    /** @test */
    public function testCanClearCache()
    {
        AutoBinder::from('Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->bind();

        $services = [
            AnotherServiceInterface::class => AnotherService::class,
            ExampleServiceInterface::class => ExampleService::class,
            TestServiceInterface::class => TestService::class,
        ];

        collect($services)->each(
            fn ($service, $interface) => $this->assertTrue($this->app->bound($interface))
        );

        $this->assertTrue(cache()->has(AutoBinder::CACHE_KEY . 'Services'));
        $this->artisan(AutoBinderClearCommand::class, ['folder' => 'Services'])
            ->expectsOutputToContain('Container bindings and cache were cleared successfully!');
        $this->assertFalse(cache()->has(AutoBinder::CACHE_KEY . 'Services'));

        collect($services)->each(
            fn ($service, $interface) => $this->assertFalse($this->app->bound($interface))
        );
    }

    /** @test */
    public function testFetchesFromCache()
    {
        AutoBinder::from('Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->bind();

        $this->assertTrue(cache()->has(AutoBinder::CACHE_KEY . 'Services'));

        AutoBinder::from('Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->bind();

        $this->assertTrue(cache()->has(AutoBinder::CACHE_KEY . 'Services'));
    }
}
