<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Tests\Boilerplate\Services;

use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\ExampleServiceInterface;

class ExampleService implements ExampleServiceInterface
{
    public function __construct(public bool $injected = false)
    {
    }
}
