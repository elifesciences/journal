<?php

namespace test\eLife\Journal\Mustache;

use eLife\Journal\Mustache\DynamicValueHelper;
use PHPUnit_Framework_TestCase;

final class DynamicValueHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_value()
    {
        $helper = new DynamicValueHelper('foo');

        $this->assertSame('foo', $helper());
    }
}
