<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\CanConvert;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class FigureAssetImageConverter implements ViewModelConverter
{
    use CanConvert;
    use CreatesAssetViewerInline;
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
     * @param Block\FigureAsset $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Block\Image $asset */
        $asset = $object->getAsset();

        $srcset = [];
        $baseWidth = 538;
        if ($asset->getImage()->getWidth() > $baseWidth) {
            $width = $baseWidth * 2;
            if ($width > $asset->getImage()->getWidth()) {
                $width = $asset->getImage()->getWidth();
            }
            $srcset[$width] = $this->iiifUri($asset->getImage(), $width);
        }

        $assetViewModel = new ViewModel\Image(
            $this->iiifUri($asset->getImage(), $asset->getImage()->getWidth() >= $baseWidth ? $baseWidth : null),
            $srcset,
            $asset->getImage()->getAltText()
        );

        $download = new ViewModel\Link('Download', $this->downloadLinkUriGenerator->generate(new DownloadLink($asset->getImage()->getSource()->getUri(), $asset->getImage()->getSource()->getFilename())));

        $open = new ViewModel\OpenLink(
            $this->iiifUri($asset->getImage()),
            $asset->getImage()->getWidth(),
            $asset->getImage()->getHeight()
        );

        return $this->createAssetViewerInline($object, $assetViewModel, $download, $open, $context);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\FigureAsset && $object->getAsset() instanceof Block\Image;
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
