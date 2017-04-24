<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesIiifUri;

    private $urlGenerator;
    private $downloadLinkUriGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, DownloadLinkUriGenerator $downloadLinkUriGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->downloadLinkUriGenerator = $downloadLinkUriGenerator;
    }

    /**
     * @param PodcastEpisode $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::podcast($object->getTitle(), false, 'Episode '.$object->getNumber(), null,
            ViewModel\Meta::withLink(new ViewModel\Link('Podcast', $this->urlGenerator->generate('podcast')), $this->simpleDate($object, ['date' => 'published'] + $context)),
            new ViewModel\BackgroundImage(
                $this->iiifUri($object->getBanner(), 900, 450),
                $this->iiifUri($object->getBanner(), 1800, 900)
            ),
            $this->downloadLinkUriGenerator->generate(DownloadLink::fromUri($object->getSources()[0]->getUri()))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
