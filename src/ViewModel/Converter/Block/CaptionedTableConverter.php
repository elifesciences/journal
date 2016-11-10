<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;
use eLife\Patterns\ViewModel\CaptionedAsset;

final class CaptionedTableConverter implements ViewModelConverter
{
    /**
     * @param Block\Table $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $asset = new ViewModel\Table(...$object->getTables());

        $doi = $object->getDoi() ? new ViewModel\Doi($object->getDoi()) : null;

        if (empty($object->getCaption())) {
            $asset = CaptionedAsset::withOnlyHeading($asset, $object->getTitle(), $doi);
        } else {
            $asset = CaptionedAsset::withParagraphs($asset, $object->getTitle(),
                array_map(function (Block $block) {
                    if ($block instanceof Block\MathML) {
                        return $block->getMathML();
                    }

                    return $block->getText();
                }, $object->getCaption()), $doi);
        }

        if (empty($object->getLabel())) {
            return $asset;
        }

        return AssetViewerInline::primary($object->getId(), $object->getLabel(), $asset);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Table && $object->getTitle();
    }
}
