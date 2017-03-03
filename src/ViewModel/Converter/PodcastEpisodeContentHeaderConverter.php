<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

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
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            ),
            $this->downloadLinkUriGenerator->generate(new DownloadLink($object->getSources()[0]->getUri()))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
