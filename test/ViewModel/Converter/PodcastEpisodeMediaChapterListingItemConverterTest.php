<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\PodcastEpisodeMediaChapterListingItemConverter;
use eLife\Patterns\ViewModel\MediaChapterListingItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeMediaChapterListingItemConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['podcast-episode'];
    protected $class = PodcastEpisode::class;
    protected $viewModelClass = MediaChapterListingItem::class;
    protected $samples = 'complete';
    //protected $samples = '*'; or delete it

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PodcastEpisodeMediaChapterListingItemConverter();
    }

    protected function modelHook(Model $model)
    {
        return $model->getChapters()[0];
    }
}
