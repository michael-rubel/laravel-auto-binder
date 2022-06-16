<?php

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\AutoBinder;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Example;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Interfaces\ExampleInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Contracts\ExampleServiceContract;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\ExampleService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\ExampleServiceInterface;

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
            ->interfaceNamespace("MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Services\\Interfaces")
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
            ->interfaceNamespace("MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Models\\Interfaces")
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
            ->interfaceNamespace("MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Services\\Interfaces")
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
    public function testCanModifyInterfaceName()
    {
        AutoBinder::from(folder: 'Services')
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNamespace('Contracts')
            ->interfaceName('contract')
            ->as('singleton')
            ->bind();

        $this->assertTrue(app()->bound(ExampleServiceContract::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceContract::class));
    }
}
