<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;

final class PodcastEpisodeContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $class = PodcastEpisode::class;
    protected $viewModelClass = ContentHeaderNonArticle::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeContentHeaderConverter($this->stubUrlGenerator(), $this->stubTranslator());
    }
}
