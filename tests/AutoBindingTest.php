<?php

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\AutoBinder;
use MichaelRubel\AutoBinder\Commands\AutoBinderClearCommand;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Example;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Interfaces\ExampleInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\AnotherService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Contracts\ExampleServiceContract;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\ExampleService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\AnotherServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\ExampleServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\TestServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Test\TestService;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class AutoBindingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app()->setBasePath(__DIR__ . '/../');
    }

    /** @test */
    public function testCanConfigureServiceBindings()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Services\\Interfaces')
            ->as('singleton')
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
    }

    /** @test */
    public function testCanConfigureModelBindings()
    {
        AutoBinder::from(folder: 'Models')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Models\\Interfaces')
            ->as('singleton')
            ->bind();

        $this->assertTrue(app()->bound(ExampleInterface::class));
        $this->assertInstanceOf(Example::class, app(ExampleInterface::class));
    }

    /** @test */
    public function testConfiguresServicesByDefault()
    {
        (new AutoBinder)
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Services\\Interfaces')
            ->as('singleton')
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
    }

    /** @test */
    public function testCanConfigureServiceAndModelBindings()
    {
        collect([
            AutoBinder::from(folder: 'Services'),
            AutoBinder::from(folder: 'Models'),
        ])->each(
            fn ($binder) => $binder
                ->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->interfaceNamespace("MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\$binder->classFolder\\Interfaces")
                ->as('singleton')
                ->bind()
        );

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));

        $this->assertTrue(app()->bound(ExampleInterface::class));
        $this->assertInstanceOf(Example::class, app(ExampleInterface::class));
    }

    /** @test */
    public function testCanGuessInterfacesBasedOnClass()
    {
        AutoBinder::from('Services', 'Models')->each(
            fn ($binder) => $binder
                ->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->as('singleton')
                ->bind()
        );

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));

        $this->assertTrue(app()->bound(ExampleInterface::class));
        $this->assertInstanceOf(Example::class, app(ExampleInterface::class));
    }

    /** @test */
    public function testCanPassMultipleFolders()
    {
        AutoBinder::from('Services', 'Models')->each(
            fn ($binder) => $binder->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->as('singleton')
                ->bind()
        );

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));

        $this->assertTrue(app()->bound(ExampleInterface::class));
        $this->assertInstanceOf(Example::class, app(ExampleInterface::class));
    }

    /** @test */
    public function testFailsToBindWithInvalidBindingType()
    {
        $this->expectException(\InvalidArgumentException::class);

        AutoBinder::from(folder: 'Services')
            ->as('test')
            ->bind();
    }

    /** @test */
    public function testCanModifyInterfaceNaming()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNaming('contract')
            ->as(type: 'bind')
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceContract::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceContract::class));
    }

    /** @test */
    public function testCanUseWhenToSetClassDependenciesByInterface()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->when(ExampleServiceInterface::class, function ($app, $service) {
                return new ExampleService(true);
            })
            ->when(AnotherServiceInterface::class, function ($app, $service) {
                return new AnotherService(true);
            })
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
        $this->assertTrue(app(ExampleServiceInterface::class)->injected);
        $this->assertTrue(app()->bound(AnotherServiceInterface::class));
        $this->assertInstanceOf(AnotherService::class, app(AnotherServiceInterface::class));
        $this->assertTrue(app(AnotherServiceInterface::class)->injected);
    }

    /** @test */
    public function testCanUseWhenToSetClassDependenciesByConcrete()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->when(ExampleService::class, function ($app, $service) {
                return new ExampleService(true);
            })
            ->when(AnotherService::class, function ($app, $service) {
                return new AnotherService(true);
            })
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
        $this->assertTrue(app(ExampleServiceInterface::class)->injected);
        $this->assertTrue(app()->bound(AnotherServiceInterface::class));
        $this->assertInstanceOf(AnotherService::class, app(AnotherServiceInterface::class));
        $this->assertTrue(app(AnotherServiceInterface::class)->injected);
    }

    /** @test */
    public function testInterfaceTakenFirstInWhen()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->when(ExampleService::class, function ($app, $service) {
                return new ExampleService(false);
            })
            ->when(ExampleServiceInterface::class, function ($app, $service) {
                return new ExampleService(true);
            })
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
        $this->assertTrue(app(ExampleServiceInterface::class)->injected);
    }

    /** @test */
    public function testCanExcludeSubFolders()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->exclude('Contracts', 'Test')
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
        $this->assertFalse(app()->bound(ExampleServiceContract::class));
    }

    /** @test */
    public function testCanExcludeSubFoldersUsingArray()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->exclude(['Contracts', 'Test'])
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
        $this->assertFalse(app()->bound(ExampleServiceContract::class));
    }

    /** @test */
    public function testBindsFromSubdirectories()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Services\\Interfaces')
            ->as('singleton')
            ->bind();

        $this->assertTrue(app()->bound(TestServiceInterface::class));
        $this->assertInstanceOf(TestService::class, app(TestServiceInterface::class));
    }

    /** @test */
    public function testThrowsExceptionIfDirectoryNotFound()
    {
        $this->expectException(DirectoryNotFoundException::class);

        AutoBinder::from(folder: 'SomeFolder')->bind();
    }

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
            fn ($service, $interface) => $this->assertTrue(app()->bound($interface))
        );

        $this->assertTrue(cache()->has(AutoBinder::CACHE_KEY . 'Services'));
        $this->artisan(AutoBinderClearCommand::class, ['folder' => 'Services']);
        $this->assertFalse(cache()->has(AutoBinder::CACHE_KEY . 'Services'));

        collect($services)->each(
            fn ($service, $interface) => $this->assertFalse(app()->bound($interface))
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
