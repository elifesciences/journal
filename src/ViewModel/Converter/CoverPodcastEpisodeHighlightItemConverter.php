<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverPodcastEpisodeHighlightItemConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesCoverPicture;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var PodcastEpisode $podcastEpisode */
        $podcastEpisode = $object->getItem();

        return new ViewModel\HighlightItem(
            [],
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('podcast-episode', [$podcastEpisode])),
            ViewModel\Meta::withLink(new ViewModel\Link(
                ModelName::singular('podcast-episode'),
                $this->urlGenerator->generate('podcast')), $this->simpleDate($podcastEpisode, $context)
            ),
            $this->createHighlightPicture($object),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HighlightItem::class === $viewModel && $object->getItem() instanceof PodcastEpisode;
    }
}
