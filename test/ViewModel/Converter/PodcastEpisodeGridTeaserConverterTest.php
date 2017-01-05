<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeGridTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class PodcastEpisodeGridTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $class = PodcastEpisode::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'grid'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeGridTeaserConverter($this->stubUrlGenerator(), $this->stubTranslator());
    }
}
