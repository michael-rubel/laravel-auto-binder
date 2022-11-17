<?php

namespace MichaelRubel\AutoBinder\Tests;

use MichaelRubel\AutoBinder\Tests\Boilerplate\ExtendedAutoBinder;

class ExtendingTest extends TestCase
{
    /** @test */
    public function testCanExtendAutoBinder()
    {
        (new ExtendedAutoBinder)
            ->basePath('tests/Boilerplate')
            ->classNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate')
            ->interfaceNamespace('MichaelRubel\\AutoBinder\\Tests\\Boilerplate\\Services\\Interfaces')
            ->bind();

        $this->assertTrue(true);
    }
}
