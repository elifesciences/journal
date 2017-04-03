<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PodcastEpisodeChapterReadMoreItemConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\ReadMoreItem;

final class PodcastEpisodeChapterReadMoreItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode-chapter'];
    protected $viewModelClasses = [ReadMoreItem::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeChapterReadMoreItemConverter(
            $this->createMock(PatternRenderer::class),
            $this->stubUrlGenerator()
        );
    }
}
