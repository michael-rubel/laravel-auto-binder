<?php

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\AutoBinder;
use MichaelRubel\AutoBinder\BindingServiceProvider;
use MichaelRubel\AutoBinder\Commands\AutoBinderClearCommand;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Example;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Models\Interfaces\ExampleInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Providers\BootServiceProvider;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Providers\RegisterServiceProvider;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\AnotherService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Contracts\ExampleServiceContract;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\ExampleService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\AnotherServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\ExampleServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\TestServiceInterface;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Test\TestService;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function testCanUseAutoBinderInRegisterProvidersMethod()
    {
        app()->offsetUnset('cache');
        app()->register(BindingServiceProvider::class, true);
        app()->register(RegisterServiceProvider::class);

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
    }

    /** @test */
    public function testCanUseAutoBinderInBootProvidersMethod()
    {
        app()->offsetUnset('cache');
        app()->register(BindingServiceProvider::class, true);
        app()->register(BootServiceProvider::class);

        $this->assertTrue(app()->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
    }
}
