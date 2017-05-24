<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PodcastEpisodeChapterRelatedItemTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class PodcastEpisodeChapterRelatedItemTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode-chapter'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'relatedItem', 'from' => 'insight'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeChapterRelatedItemTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }
}
