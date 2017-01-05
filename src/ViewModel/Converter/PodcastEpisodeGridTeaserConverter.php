<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class PodcastEpisodeGridTeaserConverter implements ViewModelConverter
{
    use CreatesTeaserImage;

    private $urlGenerator;
    private $translator;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
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
            $this->prominentTeaserImage($object),
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        $this->translator->trans('type.podcast-episode'),
                        $this->urlGenerator->generate('podcast')
                    ),
                    ViewModel\Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\Teaser::class === $viewModel && 'grid' === ($context['variant'] ?? null);
    }
}
