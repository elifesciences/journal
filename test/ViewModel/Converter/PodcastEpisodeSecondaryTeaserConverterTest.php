<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PodcastEpisodeSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class PodcastEpisodeSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeSecondaryTeaserConverter($this->stubUrlGenerator());
    }
}
