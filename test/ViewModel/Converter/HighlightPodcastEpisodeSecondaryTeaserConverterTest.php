<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\HighlightPodcastEpisodeSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Traversable;

final class HighlightPodcastEpisodeSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['highlight'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new HighlightPodcastEpisodeSecondaryTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }

    /**
     * @param Highlight $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof PodcastEpisode) {
            yield $model;
        }
    }
}
