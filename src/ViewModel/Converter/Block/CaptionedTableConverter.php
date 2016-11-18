<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;

final class CaptionedTableConverter implements ViewModelConverter
{
    use CreatesCaptionedAsset;

    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Block\Table $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $asset = new ViewModel\Table(...$object->getTables());

        $caption = array_map(function (Block $block) {
            return $this->viewModelConverter->convert($block);
        }, $object->getCaption());

        $asset = $this->createCaptionedAsset($asset, $object->getTitle(), $caption, $object->getDoi());

        if (empty($object->getLabel())) {
            return $asset;
        }

        if (!empty($context['complete'])) {
            $additionalAssets = array_map(function (File $sourceData) {
                return $this->viewModelConverter->convert($sourceData);
            }, $object->getSourceData());
        } else {
            $additionalAssets = [];
        }

        return AssetViewerInline::primary($object->getId(), $object->getLabel(), $asset, $additionalAssets);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Table && $object->getTitle();
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }
}
