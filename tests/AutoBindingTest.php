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
    public function testCanConfigureServiceAndModelBindings()
    {
        collect([
            AutoBinder::from(folder: 'Services'),
            AutoBinder::from(folder: 'Models'),
        ])->each(
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
}
