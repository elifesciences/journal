<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\ViewModel\Converter\HasPatternRenderer;
use eLife\Patterns\ViewModel;

trait CreatesCaptionedAsset
{
    use HasPatternRenderer;

    final private function createCaptionedAsset(ViewModel\IsCaptioned $asset, string $heading, Sequence $captions, string $doi = null, string $download = null) : ViewModel\CaptionedAsset
    {
        $captionText = new ViewModel\CaptionText(
            $heading,
            null,
            $this->getPatternRenderer()->render(...$captions)
        );

        if ($doi) {
            $doi = new ViewModel\Doi($doi);
        }
        if ($download) {
            $download = new ViewModel\Link('Download', $download);
        }

        return new ViewModel\CaptionedAsset($asset, $captionText, $doi, $download);
    }
}
