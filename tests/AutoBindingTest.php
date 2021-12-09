<?php

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\BindingServiceProvider;
use MichaelRubel\AutoBinder\Core\BindingMapper;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Example;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Interfaces\ExampleInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\ExampleService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\ExampleServiceInterface;

class AutoBindingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'auto-binder.start_namespace' => 'MichaelRubel\\AutoBinder\\Tests\\Boilerplate',
            'auto-binder.start_folder'    => 'tests' . DIRECTORY_SEPARATOR . 'Boilerplate',
        ]);
    }

    /** @test */
    public function testBindingsAreMappedThroughClass()
    {
        new BindingMapper();

        $bound = app()->bound(ExampleInterface::class);
        $this->assertTrue($bound);

        $hasCorrectImplementation = app(ExampleInterface::class);
        $this->assertInstanceOf(Example::class, $hasCorrectImplementation);
    }

    /** @test */
    public function testBindingsAreMappedThroughRegisteredProvider()
    {
        $registered = app()->register(BindingServiceProvider::class, true);
        $this->assertInstanceOf(BindingServiceProvider::class, $registered);

        $bound = app()->bound(ExampleServiceInterface::class);
        $this->assertTrue($bound);

        $hasCorrectImplementation = app(ExampleServiceInterface::class);
        $this->assertInstanceOf(ExampleService::class, $hasCorrectImplementation);
    }
}
