<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\Patterns\ViewModel;

final class PodcastEpisodeMediaChapterListingItemConverter implements ViewModelConverter
{
    /**
     * @param PodcastEpisodeChapter $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\MediaChapterListingItem($object->getTitle(), $object->getTime(), $object->getNumber(), $object->getImpactStatement());
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisodeChapter;
    }
}
