<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder\Tests\Boilerplate\Services;

use MichaelRubel\AutoBinder\Tests\Boilerplate\Services\Interfaces\AnotherServiceInterface;

class AnotherService implements AnotherServiceInterface
{
    public function __construct(public bool $injected = false)
    {
    }
}
