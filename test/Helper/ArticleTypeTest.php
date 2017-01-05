<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\ArticleType;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Traversable;

final class ArticleTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider validArticleTypeProvider
     */
    public function it_providers_a_singular(string $id)
    {
        ArticleType::singular($id);
    }

    /**
     * @test
     * @dataProvider validArticleTypeProvider
     */
    public function it_providers_a_plural(string $id)
    {
        ArticleType::plural($id);
    }

    public function validArticleTypeProvider() : Traversable
    {
        $types = ['correction', 'editorial', 'feature', 'insight', 'research-advance', 'research-article', 'research-exchange', 'retraction', 'registered-report', 'replication-study', 'short-report', 'tools-resources'];

        foreach ($types as $type) {
            yield $type => [$type];
        }
    }

    /**
     * @test
     */
    public function it_fails_on_an_invalid_type_for_singular()
    {
        $this->expectException(InvalidArgumentException::class);

        ArticleType::singular('foo');
    }

    /**
     * @test
     */
    public function it_fails_on_an_invalid_type_for_plural()
    {
        $this->expectException(InvalidArgumentException::class);

        ArticleType::plural('foo');
    }
}
