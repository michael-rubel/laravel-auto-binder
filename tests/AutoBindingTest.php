<?php

namespace MichaelRubel\AutoBinder\Tests;

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
    public function testBindingsAreMapped()
    {
        new BindingMapper();

        $bound = app()->bound(ExampleInterface::class);
        $this->assertTrue($bound);

        $hasCorrectImplementation = app(ExampleInterface::class);
        $this->assertInstanceOf(Example::class, $hasCorrectImplementation);

        $bound = app()->bound(ExampleServiceInterface::class);
        $this->assertTrue($bound);

        $hasCorrectImplementation = app(ExampleServiceInterface::class);
        $this->assertInstanceOf(ExampleService::class, $hasCorrectImplementation);
    }
}
