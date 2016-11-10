<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class PodcastEpisodeTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $class = PodcastEpisode::class;
    protected $viewModelClass = Teaser::class;
    protected $samples = '*';

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeTeaserConverter($this->stubUrlGenerator());
    }
}
