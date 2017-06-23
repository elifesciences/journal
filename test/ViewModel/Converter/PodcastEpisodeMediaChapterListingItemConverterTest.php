<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeMediaChapterListingItemConverter;
use eLife\Patterns\ViewModel\MediaChapterListingItem;
use Traversable;

final class PodcastEpisodeMediaChapterListingItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $viewModelClasses = [MediaChapterListingItem::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeMediaChapterListingItemConverter($this->stubUrlGenerator());
    }

    /**
     * @param PodcastEpisode $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getChapters();
    }
}
