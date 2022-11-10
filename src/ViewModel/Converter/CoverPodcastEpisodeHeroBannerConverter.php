<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverPodcastEpisodeHeroBannerConverter implements ViewModelConverter
{
    use CreatesDate;

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

        return new ViewModel\HeroBanner(
            [],
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('podcast-episode', [$podcastEpisode])),
            ViewModel\Meta::withLink(
                new ViewModel\Link(
                    ModelName::singular('podcast-episode'),
                    $this->urlGenerator->generate('podcast')
                ),
                $this->simpleDate($podcastEpisode, $context)
            ),
            (new PictureBuilderFactory())->forImage(
                $object->getBanner(), 633, 367
            )->build(),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HeroBanner::class === $viewModel && $object->getItem() instanceof PodcastEpisode;
    }
}
