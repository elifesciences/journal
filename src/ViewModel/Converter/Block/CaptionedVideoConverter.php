<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;
use eLife\Patterns\ViewModel\CaptionedAsset;

final class CaptionedVideoConverter implements ViewModelConverter
{
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

        $doi = $object->getDoi() ? new ViewModel\Doi($object->getDoi()) : null;
        $download = new ViewModel\Link('Download', $object->getSources()[0]->getUri());

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

        return AssetViewerInline::primary($object->getId(), $object->getLabel(), $asset);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Video && $object->getTitle();
    }
}
