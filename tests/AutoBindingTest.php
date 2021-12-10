<?php

namespace MichaelRubel\AutoBinder\Tests;

use Illuminate\Filesystem\Filesystem;
use MichaelRubel\AutoBinder\BindingServiceProvider;
use MichaelRubel\AutoBinder\Core\BindingMapper;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Example;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Interfaces\ExampleInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\ExampleService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\ExampleServiceInterface;
use Mockery\MockInterface;

class AutoBindingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'auto-binder.start_namespace' => 'MichaelRubel\\AutoBinder\\Tests\\Boilerplate',
            'auto-binder.start_folder'    => 'tests' . DIRECTORY_SEPARATOR . 'Boilerplate',
            'auto-binder.scan_folders' => [
                'Services',
                'Models',
            ],
        ]);

        app()->setBasePath(__DIR__ . '/../');
    }

    /** @test */
    public function testBindingsAreMappedThroughClass()
    {
        $mock = $this->partialMock(Filesystem::class, function (MockInterface $mock) {
            $mock->shouldReceive('isDirectory')
                 ->times(2)
                 ->andReturnTrue();
        });

        app()->instance('files', $mock);

        new BindingMapper();

        $bound = app()->bound(ExampleInterface::class);
        $this->assertTrue($bound);

        $hasCorrectImplementation = app(ExampleInterface::class);
        $this->assertInstanceOf(Example::class, $hasCorrectImplementation);
    }

    /** @test */
    public function testBindingsAreMappedThroughRegisteredProvider()
    {
        $mock = $this->partialMock(Filesystem::class, function (MockInterface $mock) {
            $mock->shouldReceive('isDirectory')
                 ->times(3)
                 ->andReturnTrue();
        });

        app()->instance('files', $mock);

        $registered = app()->register(BindingServiceProvider::class, true);
        $this->assertInstanceOf(BindingServiceProvider::class, $registered);

        $bound = app()->bound(ExampleServiceInterface::class);
        $this->assertTrue($bound);

        $hasCorrectImplementation = app(ExampleServiceInterface::class);
        $this->assertInstanceOf(ExampleService::class, $hasCorrectImplementation);
    }
}
