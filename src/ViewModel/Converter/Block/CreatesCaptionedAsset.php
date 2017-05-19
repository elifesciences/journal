<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\HasPatternRenderer;
use eLife\Journal\Helper\HasViewModelConverter;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel;

trait CreatesCaptionedAsset
{
    use HasPatternRenderer;
    use HasViewModelConverter;

    final private function createCaptionedAsset(ViewModel\IsCaptioned $viewModel, Asset $asset, ViewModel\Doi $doi = null) : ViewModel\CaptionedAsset
    {
        $caption = $asset->getCaption()->map(function (Block $block) {
            return $this->getViewModelConverter()->convert($block);
        });

        $caption->append(...$asset->getAttribution()->map(function (string $attribution) {
            return new Paragraph($attribution);
        }));

        $captionText = new ViewModel\CaptionText(
            $asset->getTitle() ?? 'Title',
            null,
            $this->getPatternRenderer()->render(...$caption)
        );

        return new ViewModel\CaptionedAsset($viewModel, $captionText, $doi);
    }

    final private function tryAndCreateCaptionedAsset(ViewModel\IsCaptioned $viewModel, Asset $asset, ViewModel\Doi $doi = null) : ViewModel
    {
        if (!$asset->getTitle() && $asset->getAttribution()->isEmpty() && $asset->getCaption()->isEmpty()) {
            return $viewModel;
        }

        return $this->createCaptionedAsset($viewModel, $asset, $doi);
    }
}
