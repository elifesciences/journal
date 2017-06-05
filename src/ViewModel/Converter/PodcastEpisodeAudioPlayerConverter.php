<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeAudioPlayerConverter implements ViewModelConverter
{
    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param PodcastEpisode $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($context['link'] ?? false) {
            $link = $this->urlGenerator->generate('podcast-episode', ['number' => $object->getNumber()]);
        }

        return new ViewModel\AudioPlayer(
            $object->getNumber(),
            new ViewModel\Link($object->getTitle(), $link ?? null),
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
