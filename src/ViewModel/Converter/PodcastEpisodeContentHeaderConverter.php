<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\CreatesDownloadUri;
use eLife\Patterns\ViewModel;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesDownloadUri;

    private $urlGenerator;
    private $uriSigner;

    public function __construct(UrlGeneratorInterface $urlGenerator, UriSigner $uriSigner)
    {
        $this->urlGenerator = $urlGenerator;
        $this->uriSigner = $uriSigner;
    }

    /**
     * @param PodcastEpisode $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::podcast($object->getTitle(), false, 'Episode '.$object->getNumber(), null,
            ViewModel\Meta::withLink(new ViewModel\Link('Podcast', $this->urlGenerator->generate('podcast')), $this->simpleDate($object, $context)),
            new ViewModel\BackgroundImage(
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            ),
            $this->createDownloadUri($object->getSources()[0]->getUri())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisode && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }

    protected function getUrlGenerator() : UrlGeneratorInterface
    {
        return $this->urlGenerator;
    }

    protected function getUriSigner() : UriSigner
    {
        return $this->uriSigner;
    }
}
