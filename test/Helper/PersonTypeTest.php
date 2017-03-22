<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\PersonType;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class PersonTypeTest extends PHPUnit_Framework_TestCase
{
    use Providers;

    /**
     * @test
     * @dataProvider validPersonTypeProvider
     */
    public function it_providers_a_singular(string $id)
    {
        PersonType::singular($id);
    }

    /**
     * @test
     * @dataProvider validPersonTypeProvider
     */
    public function it_providers_a_plural(string $id)
    {
        PersonType::plural($id);
    }

    public function validPersonTypeProvider() : Traversable
    {
        return $this->stringProvider('director', 'executive', 'leadership', 'reviewing-editor', 'senior-editor');
    }

    /**
     * @test
     */
    public function it_fails_on_an_invalid_type_for_singular()
    {
        $this->expectException(InvalidArgumentException::class);

        PersonType::singular('foo');
    }

    /**
     * @test
     */
    public function it_fails_on_an_invalid_type_for_plural()
    {
        $this->expectException(InvalidArgumentException::class);

        PersonType::plural('foo');
    }
}
