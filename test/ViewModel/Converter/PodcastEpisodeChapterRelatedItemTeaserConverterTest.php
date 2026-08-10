<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PodcastEpisodeChapterRelatedItemTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use PHPUnit\Framework\Attributes\Before;

final class PodcastEpisodeChapterRelatedItemTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode-chapter'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'relatedItem', 'from' => 'insight'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PodcastEpisodeChapterRelatedItemTeaserConverter($this->stubUrlGenerator());
    }
}
