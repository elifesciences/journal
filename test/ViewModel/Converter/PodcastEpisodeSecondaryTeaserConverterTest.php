<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class PodcastEpisodeSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    // multiple models
    protected $models = ['podcast-episode'];
    protected $class = PodcastEpisode::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];
    protected $samples = 'complete';
    //protected $samples = '*'; or delete it

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeSecondaryTeaserConverter($this->stubUrlGenerator());
    }
}
