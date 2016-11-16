<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Puli\UrlGenerator\Api\UrlGenerator as PuliUrlGenerator;

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
        $this->puliUrlGenerator = $this->createMock(PuliUrlGenerator::class);
        $this->puliUrlGenerator->expects($this->any())
            ->method('generateUrl')
            ->will($this->returnValue('http://...'));
        $this->converter = new PodcastEpisodeContentHeaderConverter($this->stubUrlGenerator(), $this->puliUrlGenerator);
    }
}
