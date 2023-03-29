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
    public function it_determines_relationship_text(string $from, string $to, bool $related, string $expected)
    {
        $this->assertSame($expected, ModelRelationship::get($from, $to, $related));
    }

    public function typesProvider() : Traversable
    {
        yield ['foo', 'bar', false, 'Of interest'];
        yield ['foo', 'bar', true, 'Related to'];

        yield ['registered-report', 'external-article', true, 'Original article'];
        yield ['registered-report', 'external-article', false, 'Of interest'];
        yield ['registered-report', 'research-article', true, 'Related to'];

        yield ['research-advance', 'research-article', true, 'Builds upon'];
        yield ['research-advance', 'research-article', false, 'Of interest'];
        yield ['research-advance', 'correction', false, 'Of interest'];
        yield ['research-advance', 'correction', true, 'Related to'];

        yield ['research-article', 'collection', true, 'Part of Collection'];
        yield ['correction', 'collection', true, 'Part of Collection'];
        yield ['research-article', 'collection', false, 'Of interest'];

        yield ['research-article', 'podcast-episode-chapter', true, 'Discussed in'];
        yield ['correction', 'podcast-episode-chapter', true, 'Discussed in'];
        yield ['research-article', 'podcast-episode-chapter', false, 'Of interest'];

        yield ['research-article', 'research-advance', true, 'Built upon by'];
        yield ['research-article', 'research-advance', false, 'Of interest'];
        yield ['correction', 'research-advance', false, 'Of interest'];
        yield ['correction', 'research-advance', true, 'Related to'];
    }
}
