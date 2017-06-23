<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use eLife\Journal\ViewModel\Converter\HighlightPodcastEpisodeChapterSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Traversable;

final class HighlightPodcastEpisodeChapterSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['highlight'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new HighlightPodcastEpisodeChapterSecondaryTeaserConverter($this->stubUrlGenerator());
    }

    /**
     * @param Highlight $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof PodcastEpisodeChapterModel) {
            yield $model;
        }
    }
}
