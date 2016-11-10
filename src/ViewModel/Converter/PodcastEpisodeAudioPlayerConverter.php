<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\Patterns\ViewModel;

final class PodcastEpisodeAudioPlayerConverter implements ViewModelConverter
{
    private $viewModelConverter;

    public function __construct(ViewModelConverter $viewModelConverter)
    {
        $this->viewModelConverter = $viewModelConverter;
    }

    /**
     * @param PodcastEpisode $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\AudioPlayer(
            $object->getNumber(),
            'Episode '.$object->getNumber(),
            array_map(function (PodcastEpisodeSource $source) {
                return new ViewModel\MediaSource($source->getUri(), new ViewModel\MediaType($source->getMediaType()));
            }, $object->getSources()),
            $object->getChapters()->map(function (PodcastEpisodeChapter $chapter) {
                return $this->viewModelConverter->convert($chapter);
            })->toArray()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\AudioPlayer::class === $viewModel;
    }
}
