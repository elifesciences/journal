<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeContentHeaderConverter implements ViewModelConverter
{
    private $urlGenerator;
    private $downloadLinkUriGenerator;
    private $contentHeaderImageFactory;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        DownloadLinkUriGenerator $downloadLinkUriGenerator,
        ContentHeaderImageFactory $contentHeaderImageFactory
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->downloadLinkUriGenerator = $downloadLinkUriGenerator;
        $this->contentHeaderImageFactory = $contentHeaderImageFactory;
    }

    /**
     * @param PodcastEpisode $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ContentHeader(
            $object->getTitle(),
            $this->contentHeaderImageFactory->forImage($object->getBanner()), $object->getImpactStatement(), false, [], null, null, null, [], [],
            $this->downloadLinkUriGenerator->generate(DownloadLink::fromUri($object->getSources()[0]->getUri())), null, null,
            ViewModel\Meta::withLink(new ViewModel\Link('Podcast', $this->urlGenerator->generate('podcast')))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\ContentHeader::class === $viewModel;
    }
}
