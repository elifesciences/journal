<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class PodcastEpisodeContentHeaderConverter implements ViewModelConverter
{
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
        return ViewModel\ContentHeaderNonArticle::podcast($object->getTitle(), false, 'Episode '.$object->getNumber(), null,
            ViewModel\Meta::withLink(
                new ViewModel\Link(
                    $this->translator->trans('type.podcast-episode'),
                    $this->urlGenerator->generate('podcast')
                ),
                ViewModel\Date::simple($object->getPublishedDate())
            ),
            new ViewModel\BackgroundImage(
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            ),
            $object->getSources()[0]->getUri()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
