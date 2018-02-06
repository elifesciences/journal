<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\ModelRelationship;
use PHPUnit\Framework\TestCase;
use Traversable;

final class ModelRelationshipTest extends TestCase
{
    /**
     * @test
     * @dataProvider typesProvider
     */
    public function it_determines_relationship_text(string $from, string $to, bool $unrelated, string $expected)
    {
        $this->assertSame($expected, ModelRelationship::get($from, $to, $unrelated));
    }

    public function typesProvider() : Traversable
    {
        yield ['foo', 'bar', true, 'Of interest'];
        yield ['foo', 'bar', false, 'Of interest'];

        yield ['registered-report', 'external-article', false, 'Original article'];
        yield ['registered-report', 'external-article', true, 'Of interest'];
        yield ['registered-report', 'research-article', false, 'Of interest'];

        yield ['research-advance', 'research-article', false, 'Builds upon'];
        yield ['research-advance', 'research-article', true, 'Of interest'];
        yield ['research-advance', 'correction', true, 'Of interest'];

        yield ['research-article', 'collection', false, 'Part of'];
        yield ['correction', 'collection', false, 'Part of'];
        yield ['research-article', 'collection', true, 'Of interest'];

        yield ['research-article', 'podcast-episode-chapter', false, 'Discussed in'];
        yield ['correction', 'podcast-episode-chapter', false, 'Discussed in'];
        yield ['research-article', 'podcast-episode-chapter', true, 'Of interest'];

        yield ['research-article', 'research-advance', false, 'Built upon by'];
        yield ['research-article', 'research-advance', true, 'Of interest'];
        yield ['correction', 'research-advance', true, 'Of interest'];
    }
}
