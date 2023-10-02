<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\BindingServiceProvider;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Providers\BootServiceProvider;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Providers\RegisterServiceProvider;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\ExampleService;
use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\ExampleServiceInterface;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function testCanUseAutoBinderInRegisterProvidersMethod()
    {
        $this->app->offsetUnset('cache');
        $this->app->register(BindingServiceProvider::class, true);
        $this->app->register(RegisterServiceProvider::class);

        $this->assertTrue($this->app->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
    }

    /** @test */
    public function testCanUseAutoBinderInBootProvidersMethod()
    {
        $this->app->offsetUnset('cache');
        $this->app->register(BindingServiceProvider::class, true);
        $this->app->register(BootServiceProvider::class);

        $this->assertTrue($this->app->bound(ExampleServiceInterface::class));
        $this->assertInstanceOf(ExampleService::class, app(ExampleServiceInterface::class));
    }
}
