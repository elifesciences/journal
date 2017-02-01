<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeMediaChapterListingItemConverter;
use eLife\Patterns\ViewModel\MediaChapterListingItem;

final class PodcastEpisodeMediaChapterListingItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['podcast-episode'];
    protected $class = PodcastEpisode::class;
    protected $viewModelClass = MediaChapterListingItem::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeMediaChapterListingItemConverter($this->stubUrlGenerator());
    }

    protected function modelHook(Model $model)
    {
        return $model->getChapters()[0];
    }
}
