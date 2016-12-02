<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\HasPatternRenderer;
use eLife\Journal\Helper\HasViewModelConverter;
use eLife\Journal\ViewModel\Converter\CreatesDoi;
use eLife\Patterns\ViewModel;

trait CreatesCaptionedAsset
{
    use CreatesDoi;
    use HasPatternRenderer;
    use HasViewModelConverter;

    final private function createCaptionedAsset(ViewModel\IsCaptioned $viewModel, Asset $asset, string $download = null) : ViewModel\CaptionedAsset
    {
        $caption = $asset->getCaption()->map(function (Block $block) {
            return $this->getViewModelConverter()->convert($block);
        });

        $captionText = new ViewModel\CaptionText(
            $asset->getTitle(),
            null,
            $this->getPatternRenderer()->render(...$caption)
        );

        if ($download) {
            $download = new ViewModel\Link('Download', $download);
        }

        return new ViewModel\CaptionedAsset($viewModel, $captionText, $this->createDoi($asset), $download);
    }
}
