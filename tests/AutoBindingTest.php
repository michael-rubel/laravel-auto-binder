<?php

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\AutoBinder;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Example;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Interfaces\ExampleInterface;
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

        $bound = app()->bound(ExampleServiceInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleServiceInterface::class);
        $this->assertInstanceOf(ExampleService::class, $hasCorrectImplementation);
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

        $bound = app()->bound(ExampleInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleInterface::class);
        $this->assertInstanceOf(Example::class, $hasCorrectImplementation);
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

        $bound = app()->bound(ExampleServiceInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleServiceInterface::class);
        $this->assertInstanceOf(ExampleService::class, $hasCorrectImplementation);
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

        $bound = app()->bound(ExampleServiceInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleServiceInterface::class);
        $this->assertInstanceOf(ExampleService::class, $hasCorrectImplementation);

        $bound = app()->bound(ExampleInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleInterface::class);
        $this->assertInstanceOf(Example::class, $hasCorrectImplementation);
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

        $bound = app()->bound(ExampleServiceInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleServiceInterface::class);
        $this->assertInstanceOf(ExampleService::class, $hasCorrectImplementation);

        $bound = app()->bound(ExampleInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleInterface::class);
        $this->assertInstanceOf(Example::class, $hasCorrectImplementation);
    }

    /** @test */
    public function testCnaPassMultipleFolders()
    {
        AutoBinder::from('Services', 'Models')->each(
            fn ($binder) => $binder->basePath('tests/Boilerplate')
                ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
                ->as('singleton')
                ->bind()
        );

        $bound = app()->bound(ExampleServiceInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleServiceInterface::class);
        $this->assertInstanceOf(ExampleService::class, $hasCorrectImplementation);

        $bound = app()->bound(ExampleInterface::class);
        $this->assertTrue($bound);
        $hasCorrectImplementation = app(ExampleInterface::class);
        $this->assertInstanceOf(Example::class, $hasCorrectImplementation);
    }
}
