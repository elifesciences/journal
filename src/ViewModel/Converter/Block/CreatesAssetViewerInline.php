<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\CreatesDoi;
use eLife\Patterns\ViewModel;

trait CreatesAssetViewerInline
{
    use CreatesDoi;
    use CreatesCaptionedAsset;

    final private function createAssetViewerInline(Block\FigureAsset $asset, ViewModel\IsCaptioned $assetViewModel, ViewModel\Link $download = null, ViewModel\OpenLink $open = null, array $context = []) : ViewModel\AssetViewerInline
    {
        $assetViewModel = $this->createCaptionedAsset($assetViewModel, $asset->getAsset(), $this->createDoi($asset));

        $additionalAssets = $asset->getSourceData()->map(function (AssetFile $sourceData) {
            return $this->getViewModelConverter()->convert($sourceData);
        })->toArray();

        if (!empty($context['parentId']) && !empty($context['ordinal'])) {
            return ViewModel\AssetViewerInline::supplement($asset->getAsset()->getId(), $context['ordinal'], $context['parentId'], $asset->getLabel(), $assetViewModel, $additionalAssets, $download, $open);
        }

        if (empty($context['complete']) && !empty($context['figuresUri'])) {
            $seeAllLink = explode('#', $context['figuresUri'])[0].'#'.$asset->getAsset()->getId();
        } else {
            $seeAllLink = null;
        }

        return ViewModel\AssetViewerInline::primary($asset->getAsset()->getId(), $asset->getLabel(), $assetViewModel, $additionalAssets, $download, $open, $context['supplementsCount'] ?? 0, $seeAllLink);
    }
}
