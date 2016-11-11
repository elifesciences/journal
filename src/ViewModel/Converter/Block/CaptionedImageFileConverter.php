<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;

final class CaptionedImageFileConverter implements ViewModelConverter
{
    use CreatesAdditionalAssetData;
    use CreatesCaptionedAsset;

    /**
     * @param Block\ImageFile $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $asset = new ViewModel\Image($object->getUri(), [], $object->getAltText());

        $asset = $this->createCaptionedAsset($asset, $object->getTitle(), $object->getCaption(), $object->getDoi(), $object->getUri());

        if (empty($object->getLabel())) {
            return $asset;
        }

        if (!empty($context['complete'])) {
            $additionalAssets = array_map(function (Block\File $sourceData) {
                return $this->createAdditionalAssetData($sourceData);
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
}
