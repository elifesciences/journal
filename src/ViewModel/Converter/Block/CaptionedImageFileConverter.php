<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;
use eLife\Patterns\ViewModel\CaptionedAsset;

final class CaptionedImageFileConverter implements ViewModelConverter
{
    /**
     * @param Block\ImageFile $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $asset = new ViewModel\Image($object->getUri(), [], $object->getAltText());

        $doi = $object->getDoi() ? new ViewModel\Doi($object->getDoi()) : null;
        $download = new ViewModel\Link('Download', $object->getUri());

        if (empty($object->getCaption())) {
            $asset = CaptionedAsset::withOnlyHeading($asset, $object->getTitle(), $doi, $download);
        } else {
            $asset = CaptionedAsset::withParagraphs($asset, $object->getTitle(),
                array_map(function (Block $block) {
                    if ($block instanceof Block\MathML) {
                        return $block->getMathML();
                    }

                    return $block->getText();
                }, $object->getCaption()), $doi, $download);
        }

        if (empty($object->getLabel())) {
            return $asset;
        }

        if (!empty($context['parentId']) && !empty($context['ordinal'])) {
            return AssetViewerInline::supplement($object->getId(), $context['ordinal'], $context['parentId'], $object->getLabel(), $asset);
        }

        return AssetViewerInline::primary($object->getId(), $object->getLabel(), $asset);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\ImageFile && $object->getTitle();
    }
}
