<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\ModelName;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class ModelNameTest extends PHPUnit_Framework_TestCase
{
    use Providers;

    /**
     * @test
     * @dataProvider validModelProvider
     */
    public function it_providers_a_singular(string $id)
    {
        ModelName::singular($id);
    }

    /**
     * @test
     * @dataProvider validModelProvider
     */
    public function it_providers_a_plural(string $id)
    {
        ModelName::plural($id);
    }

    public function validModelProvider() : Traversable
    {
        return $this->stringProvider('correction', 'editorial', 'feature', 'insight', 'research-advance', 'research-article', 'research-exchange', 'retraction', 'registered-report', 'replication-study', 'short-report', 'tools-resources', 'blog-article', 'collection', 'event', 'labs-experiment', 'interview', 'podcast-episode');
    }

    /**
     * @test
     */
    public function it_fails_on_an_invalid_type_for_singular()
    {
        $this->expectException(InvalidArgumentException::class);

        ModelName::singular('foo');
    }

    /**
     * @test
     */
    public function it_fails_on_an_invalid_type_for_plural()
    {
        $this->expectException(InvalidArgumentException::class);

        ModelName::plural('foo');
    }
}
