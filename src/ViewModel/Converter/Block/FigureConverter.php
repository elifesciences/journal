<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\FigureAsset;
use eLife\Journal\ViewModel\AssetViewerInlineSet;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class FigureConverter implements ViewModelConverter
{
    private $viewModelConverter;

    public function __construct(ViewModelConverter $viewModelConverter)
    {
        $this->viewModelConverter = $viewModelConverter;
    }

    /**
     * @param Block\Figure $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var FigureAsset $primary */
        $primary = $object->getAssets()[0];

        if (!empty($context['complete'])) {
            $supplements = $object->getAssets()->slice(1);
        } else {
            $supplements = new EmptySequence();
        }

        return new AssetViewerInlineSet(
            $this->viewModelConverter->convert($primary, null, ['supplementsCount' => count($object->getAssets()) - 1] + $context),
            ...$supplements->map(function (FigureAsset $asset, int $i) use ($context, $primary) {
                return $this->viewModelConverter->convert($asset, null, ['parentId' => $primary->getAsset()->getId(), 'ordinal' => $i + 1] + $context);
            })
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Figure;
    }
}
