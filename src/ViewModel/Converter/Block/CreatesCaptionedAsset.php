<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Patterns\ViewModel;

trait CreatesCaptionedAsset
{
    final private function createCaptionedAsset(ViewModel\IsCaptioned $asset, string $heading, array $captions, string $doi = null, string $download = null) : ViewModel\CaptionedAsset
    {
        if ($doi) {
            $doi = new ViewModel\Doi($doi);
        }
        if ($download) {
            $download = new ViewModel\Link('Download', $download);
        }

        if (empty($captions)) {
            return ViewModel\CaptionedAsset::withOnlyHeading($asset, $heading, $doi, $download);
        }

        return ViewModel\CaptionedAsset::withParagraphs($asset, $heading,
            array_map(function (Block $block) {
                if ($block instanceof Block\MathML) {
                    return $block->getMathML();
                }

                return $block->getText();
            }, $captions), $doi, $download);
    }
}
