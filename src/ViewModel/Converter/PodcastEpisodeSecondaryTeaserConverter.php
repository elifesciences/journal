<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class PodcastEpisodeSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
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
        return Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('podcast-episode', ['number' => $object->getNumber()]),
            'Episode '.$object->getNumber(),
            $this->createContextLabel($object),
            $this->smallTeaserImage($object),
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new ViewModel\Link(
                        $this->translator->trans('type.podcast-episode'),
                        $this->urlGenerator->generate('podcast')
                    ),
                    Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
