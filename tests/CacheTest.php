<?php

declare(strict_types=1);

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
        config(['app.name' => 'Testing $env']);

        AutoBinder::from('Services', 'Models')->each(
            fn ($binder) => $binder->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->as('singleton')
                ->bind()
        );

        $this->assertTrue($this->app->bound(ExampleServiceInterface::class));

        $this->assertTrue(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));
        $services = [
            AnotherServiceInterface::class => AnotherService::class,
            ExampleServiceInterface::class => ExampleService::class,
            TestServiceInterface::class => TestService::class,
        ];
        $this->assertSame($services, cache()->get(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));

        $this->assertTrue(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Models'));
        $models = [ExampleInterface::class => Example::class];
        $this->assertSame($models, cache()->get(config('app.name') . AutoBinder::CACHE_KEY . 'Models'));

        $this->app->offsetUnset(ExampleServiceInterface::class);
        $this->assertFalse($this->app->bound(ExampleServiceInterface::class));

        AutoBinder::from('Services', 'Models')->each(
            fn ($binder) => $binder->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->as('singleton')
                ->bind()
        );

        $this->assertTrue($this->app->bound(ExampleServiceInterface::class));
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

        $this->assertFalse(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));
        $this->assertFalse(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Models'));
    }

    /** @test */
    public function testAvoidsCachingInLocalEnv()
    {
        $this->app['env'] = 'local';

        AutoBinder::from('Services', 'Models')->each(
            fn ($binder) => $binder->basePath('tests/Boilerplate')
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
        $services = [
            AnotherServiceInterface::class => AnotherService::class,
            ExampleServiceInterface::class => ExampleService::class,
            TestServiceInterface::class => TestService::class,
        ];

        $models = [
            ExampleInterface::class => Example::class,
        ];

        AutoBinder::from('Services', 'Models')->each(function ($binder) {
            $binder->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->bind();
        });

        collect($services)->each(
            fn ($service, $interface) => $this->assertTrue($this->app->bound($interface))
        );
        collect($models)->each(
            fn ($service, $interface) => $this->assertTrue($this->app->bound($interface))
        );

        $this->assertTrue(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));
        $this->artisan(AutoBinderClearCommand::class, ['folder' => 'Services'])
            ->expectsOutput('Container binding cache cleared successfully!');
        $this->assertFalse(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));
        collect($services)->each(
            fn ($service, $interface) => $this->assertFalse($this->app->bound($interface))
        );

        $this->assertTrue(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Models'));
        $this->artisan(AutoBinderClearCommand::class, ['folder' => 'Models'])
            ->expectsOutput('Container binding cache cleared successfully!');
        $this->assertFalse(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Models'));
        collect($models)->each(
            fn ($model, $interface) => $this->assertFalse($this->app->bound($interface))
        );

        AutoBinder::from('Services', 'Models')->each(function ($binder) {
            $binder->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->bind();
        });
        $this->assertTrue(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));
        $this->assertTrue(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Models'));
        $this->artisan(AutoBinderClearCommand::class, ['folder' => 'Services,Models'])
            ->expectsOutput('Container binding cache cleared successfully!');
        $this->assertFalse(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));
        $this->assertFalse(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Models'));
    }

    /** @test */
    public function testCannotClearCacheForNonExistingFolders()
    {
        $this->artisan(AutoBinderClearCommand::class, ['folder' => 'Test'])
            ->expectsOutput('Cached folder Test not found.');
    }

    /** @test */
    public function testFetchesFromCache()
    {
        AutoBinder::from('Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->bind();

        $this->assertTrue(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));

        AutoBinder::from('Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->bind();

        $this->assertTrue(cache()->has(config('app.name') . AutoBinder::CACHE_KEY . 'Services'));
    }
}
