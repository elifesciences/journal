<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeGridTeaserConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param PodcastEpisode $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\Teaser::withGrid(
            $object->getTitle(),
            $this->urlGenerator->generate('podcast-episode', ['number' => $object->getNumber()]),
            $object->getImpactStatement(),
            'Episode '.$object->getNumber(),
            ViewModel\TeaserImage::prominent(
                $object->getThumbnail()->getSize('16:9')->getImage(250),
                $object->getThumbnail()->getAltText(),
                [
                    500 => $object->getThumbnail()->getSize('16:9')->getImage(500),
                    250 => $object->getThumbnail()->getSize('16:9')->getImage(250),
                ]
            ),
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link('Podcast', $this->urlGenerator->generate('podcast')),
                    new ViewModel\Date($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\Teaser::class === $viewModel && 'grid' === ($context['variant'] ?? null);
    }
}
