<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;

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
        return Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('podcast-episode', ['number' => $object->getNumber()]),
            'Episode '.$object->getNumber(),
            $this->createContextLabel($object),
            TeaserImage::small(
                $object->getThumbnail()->getSize('1:1')->getImage(70),
                $object->getThumbnail()->getAltText(),
                [
                    140 => $object->getThumbnail()->getSize('1:1')->getImage(140),
                    70 => $object->getThumbnail()->getSize('1:1')->getImage(70),
                ]
            ),
            TeaserFooter::forNonArticle(
                Meta::withText(
                    'Podcast',
                    new Date($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
