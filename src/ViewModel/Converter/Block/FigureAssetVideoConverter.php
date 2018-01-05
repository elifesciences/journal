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

final class FigureAssetVideoConverter implements ViewModelConverter
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
        /** @var Block\Video $asset */
        $asset = $object->getAsset();

        $assetViewModel = new ViewModel\Video(
            array_map(function (Block\VideoSource $source) {
                return new ViewModel\MediaSource($source->getUri(), new ViewModel\MediaType($source->getMediaType()));
            }, $asset->getSources()),
            $asset->getPlaceholder() ? $this->iiifUri($asset->getPlaceholder(), $asset->getWidth(), $asset->getHeight()) : null,
            $asset->isAutoplay(),
            $asset->isLoop()
        );

        $download = new ViewModel\Link('Download', $this->downloadLinkUriGenerator->generate(DownloadLink::fromUri($asset->getSources()[0]->getUri())));

        return $this->createAssetViewerInline($object, $assetViewModel, $download, null, $context);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\FigureAsset && $object->getAsset() instanceof Block\Video;
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
