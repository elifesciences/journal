<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;
use eLife\Patterns\ViewModel\Link;

final class CaptionedImageFileConverter implements ViewModelConverter
{
    use CreatesCaptionedAsset;
    use CreatesIiifUri;

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
        $srcset = [];
        $baseWidth = 538;
        if ($object->getImage()->getWidth() > $baseWidth) {
            $width = $baseWidth * 2;
            if ($width > $object->getImage()->getWidth()) {
                $width = $object->getImage()->getWidth();
            }
            $srcset[$width] = $this->iiifUri($object->getImage(), $width);
        }

        $asset = new ViewModel\Image(
            $this->iiifUri($object->getImage(), $object->getImage()->getWidth() >= $baseWidth ? $baseWidth : null),
            $srcset,
            $object->getImage()->getAltText()
        );

        $asset = $this->createCaptionedAsset($asset, $object);

        if (empty($object->getLabel())) {
            return $asset;
        }

        if (!empty($context['complete'])) {
            $additionalAssets = array_map(function (AssetFile $sourceData) {
                return $this->viewModelConverter->convert($sourceData);
            }, $object->getSourceData());
        } else {
            $additionalAssets = [];
        }

        $download = new Link('Download', $this->downloadLinkUriGenerator->generate(new DownloadLink($object->getImage()->getSource()->getUri(), $object->getImage()->getSource()->getFilename())));
        $open = $this->iiifUri($object->getImage());

        if (!empty($context['parentId']) && !empty($context['ordinal'])) {
            return AssetViewerInline::supplement($object->getId(), $context['ordinal'], $context['parentId'], $object->getLabel(), $asset, $additionalAssets, $download, $open);
        }

        if (empty($context['complete']) && !empty($context['figuresUri'])) {
            $seeAllLink = $context['figuresUri'].'#'.$object->getId();
        } else {
            $seeAllLink = null;
        }

        return AssetViewerInline::primary($object->getId(), $object->getLabel(), $asset, $additionalAssets, $download, $open, $context['supplementsCount'] ?? 0, $seeAllLink);
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
