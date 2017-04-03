<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PodcastEpisodeTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class PodcastEpisodeTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $viewModelClasses = [Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeTeaserConverter($this->stubUrlGenerator());
    }
}
