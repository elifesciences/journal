<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeGridTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeGridTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['podcast-episode'];
    protected $class = PodcastEpisode::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'grid'];
    protected $samples = 'complete';
    //protected $samples = '*'; or delete it

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new PodcastEpisodeGridTeaserConverter($this->urlGenerator);
    }
}
