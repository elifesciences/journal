<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;
use eLife\Journal\Helper\CreatesDownloadUri;
use eLife\Journal\Helper\HasPatternRenderer;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FileAdditionalAssetConverter implements ViewModelConverter
{
    use CreatesDownloadUri;
    use HasPatternRenderer;

    private $viewModelConverter;
    private $patternRenderer;
    private $urlGenerator;
    private $uriSigner;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer, UrlGeneratorInterface $urlGenerator, UriSigner $uriSigner)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
        $this->urlGenerator = $urlGenerator;
        $this->uriSigner = $uriSigner;
    }

    /**
     * @param File $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $caption = $object->getCaption()->map(function (Block $block) {
            return $this->viewModelConverter->convert($block);
        });

        $captionText = new ViewModel\CaptionText(
            $object->getLabel() ?? $object->getTitle(),
            $object->getLabel() ? $object->getTitle() : null,
            $this->patternRenderer->render(...$caption)
        );

        $download = ViewModel\DownloadLink::fromLink(
            new ViewModel\Link('Download '.$object->getFilename(), $this->createDownloadUri($object->getUri(), $object->getFilename())),
            $object->getFilename()
        );

        if (!$object->getDoi()) {
            return ViewModel\AdditionalAsset::withoutDoi($object->getId(), $captionText, $download, $object->getUri());
        }

        return ViewModel\AdditionalAsset::withDoi($object->getId(), $captionText, $download, new ViewModel\Doi($object->getDoi()));
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof File;
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
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
