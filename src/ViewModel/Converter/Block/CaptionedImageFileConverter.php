<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;

final class CaptionedImageFileConverter implements ViewModelConverter
{
    use CreatesCaptionedAsset;

    private $viewModelConverter;
    private $patternRenderer;
    private $downloadLinkUriGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer, DownloadLinkUriGenerator $downloadLinkUriGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
        $this->downloadLinkUriGenerator = $downloadLinkUriGenerator;
    }

    /**
     * @param Block\ImageFile $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $asset = new ViewModel\Image(str_replace('.tif', '.jpg', $object->getUri()), [], $object->getAltText());

        $asset = $this->createCaptionedAsset($asset, $object, $this->downloadLinkUriGenerator->generate(new DownloadLink($object->getUri())));

        if (empty($object->getLabel())) {
            return $asset;
        }

        if (!empty($context['complete'])) {
            $additionalAssets = array_map(function (File $sourceData) {
                return $this->viewModelConverter->convert($sourceData);
            }, $object->getSourceData());
        } else {
            $additionalAssets = [];
        }

        if (!empty($context['parentId']) && !empty($context['ordinal'])) {
            return AssetViewerInline::supplement($object->getId(), $context['ordinal'], $context['parentId'], $object->getLabel(), $asset, $additionalAssets);
        }

        return AssetViewerInline::primary($object->getId(), $object->getLabel(), $asset, $additionalAssets);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\ImageFile && $object->getTitle();
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
