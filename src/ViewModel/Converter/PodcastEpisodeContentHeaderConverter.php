<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class PodcastEpisodeContentHeaderConverter implements ViewModelConverter
{
    private $viewModelConverter;
    private $urlGenerator;
    private $downloadLinkUriGenerator;
    private $contentHeaderImageFactory;

    public function __construct(
        ViewModelConverter $viewModelConverter,
        UrlGeneratorInterface $urlGenerator,
        DownloadLinkUriGenerator $downloadLinkUriGenerator,
        ContentHeaderImageFactory $contentHeaderImageFactory
    ) {
        $this->viewModelConverter = $viewModelConverter;
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
            $this->contentHeaderImageFactory->forImage($object->getBanner()), $object->getImpactStatement(), true, null, [], null, [], [],
            $this->downloadLinkUriGenerator->generate(DownloadLink::fromUri($object->getSources()[0]->getUri())),
            new ViewModel\SocialMediaSharers(
                strip_tags($object->getTitle()),
                $this->urlGenerator->generate('podcast-episode', [$object], UrlGeneratorInterface::ABSOLUTE_URL)
            ),
            null,
            null,
            ViewModel\Meta::withLink(new ViewModel\Link('Podcast', $this->urlGenerator->generate('podcast'))), null, null,
            $this->viewModelConverter->convert($object, ViewModel\AudioPlayer::class)
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\ContentHeader::class === $viewModel;
    }
}
