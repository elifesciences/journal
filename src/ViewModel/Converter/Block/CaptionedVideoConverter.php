<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;

final class CaptionedVideoConverter implements ViewModelConverter
{
    use CreatesCaptionedAsset;

    private $viewModelConverter;

    public function __construct(ViewModelConverter $viewModelConverter)
    {
        $this->viewModelConverter = $viewModelConverter;
    }

    /**
     * @param Block\Video $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $asset = new ViewModel\Video(
            $object->getImage(),
            array_map(function (Block\VideoSource $source) {
                return new ViewModel\MediaSource($source->getUri(), new ViewModel\MediaType($source->getMediaType()));
            }, $object->getSources())
        );

        $asset = $this->createCaptionedAsset($asset, $object->getTitle(), $object->getCaption(), $object->getDoi(), $object->getSources()[0]->getUri());

        if (empty($object->getLabel())) {
            return $asset;
        }

        if (!empty($context['complete'])) {
            $additionalAssets = array_map(function (Block\File $sourceData) {
                return $this->viewModelConverter->convert($sourceData);
            }, $object->getSourceData());
        } else {
            $additionalAssets = [];
        }

        return AssetViewerInline::primary($object->getId(), $object->getLabel(), $asset, $additionalAssets);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Video && $object->getTitle();
    }
}
