<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;
use eLife\Patterns\ViewModel\CaptionedAsset;

final class CaptionedImageConverter implements ViewModelConverter
{
    /**
     * @param Block\Image $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $image = $object->getImage();

        $asset = new ViewModel\Image($image->getUri(), [], $image->getAltText());

        $doi = $image->getDoi() ? new ViewModel\Doi($image->getDoi()) : null;
        $download = new ViewModel\Link('Download', $image->getUri());

        if (empty($image->getCaption())) {
            $asset = CaptionedAsset::withOnlyHeading($asset, $image->getTitle(), $doi, $download);
        } else {
            $asset = CaptionedAsset::withParagraphs($asset, $image->getTitle(),
                array_map(function (Block $block) {
                    if ($block instanceof Block\MathML) {
                        return $block->getMathML();
                    }

                    return $block->getText();
                }, $image->getCaption()), $doi, $download);
        }

        if (empty($image->getLabel())) {
            return $asset;
        }

        return AssetViewerInline::primary($image->getId(), $image->getLabel(), $asset);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Image && $object->getImage()->getTitle();
    }
}
