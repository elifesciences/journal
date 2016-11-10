<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Patterns\ViewModel;
use Puli\UrlGenerator\Api\UrlGenerator as PuliUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeContentHeaderConverter implements ViewModelConverter
{
    private $urlGenerator;
    private $puliUrlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, PuliUrlGenerator $puliUrlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->puliUrlGenerator = $puliUrlGenerator;
    }

    /**
     * @param PodcastEpisode $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::podcast($object->getTitle(), false, 'Episode '.$object->getNumber(), null,
            ViewModel\Meta::withLink(new ViewModel\Link('Podcast', $this->urlGenerator->generate('podcast')),
                new ViewModel\Date($object->getPublishedDate())),
            new ViewModel\BackgroundImage(
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            ),
            new ViewModel\PodcastDownload(
                $object->getSources()[0]->getUri(),
                new ViewModel\Picture(
                    [
                        [
                            'srcset' => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse.svg'),
                            'media' => '(min-width: 35em)',
                            'type' => 'image/svg+xml',
                        ],
                        [
                            'srcset' => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse-1x.png'),
                            'media' => '(min-width: 35em)',
                        ],
                        [
                            'srcset' => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/icons/download-reverse.svg'),
                            'type' => 'image/svg+xml',
                        ],
                    ],
                    new ViewModel\Image(
                        $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse-1x.png'),
                        [
                            88 => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse-2x.png'),
                            44 => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse-1x.png'),
                        ],
                        'Download icon'
                    )
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
