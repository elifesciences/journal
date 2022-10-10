<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\ModelName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class ModelNameTest extends TestCase
{
    use Providers;

    /**
     * @test
     * @dataProvider validModelProvider
     */
    public function it_providers_a_singular(string $id, string $singular)
    {
        $this->assertSame($singular, ModelName::singular($id));
    }

    /**
     * @test
     * @dataProvider validModelProvider
     */
    public function it_providers_a_plural(string $id, string $singular, string $plural)
    {
        $this->assertSame($plural, ModelName::plural($id));
    }

    public function validModelProvider() : Traversable
    {
        return $this->arrayProvider([
            'correction' => ['Correction', 'Corrections'],
            'editorial' => ['Editorial', 'Editorials'],
            'feature' => ['Feature Article', 'Feature Articles'],
            'insight' => ['Insight', 'Insights'],
            'research-advance' => ['Research Advance', 'Research Advances'],
            'research-article' => ['Research Article', 'Research Articles'],
            'research-communication' => ['Research Communication', 'Research Communication'],
            'retraction' => ['Retraction', 'Retractions'],
            'registered-report' => ['Registered Report', 'Registered Reports'],
            'replication-study' => ['Replication Study', 'Replication Studies'],
            'review-article' => ['Review Article', 'Review Articles'],
            'scientific-correspondence' => ['Scientific Correspondence', 'Scientific Correspondence'],
            'short-report' => ['Short Report', 'Short Reports'],
            'tools-resources' => ['Tools and Resources', 'Tools and Resources'],
            'blog-article' => ['Inside eLife', 'Inside eLife'],
            'collection' => ['Collection', 'Collections'],
            'digest' => ['Digest', 'Digests'],
            'event' => ['Event', 'Events'],
            'labs-post' => ['Labs Post', 'Labs Posts'],
            'interview' => ['Interview', 'Interviews'],
            'job-advert' => ['eLife Jobs', 'eLife Jobs'],
            'podcast-episode' => ['Podcast', 'Podcasts'],
            'press-package' => ['Press Pack', 'Press Packs'],
            'promotional-collection' => ['Highlight', 'Highlights'],
            'reviewed-preprint' => ['Reviewed Preprint', 'Reviewed Preprints'],
        ]);
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
