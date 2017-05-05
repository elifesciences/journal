<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\AssetViewerInlineSet;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class CaptionedImageConverter implements ViewModelConverter
{
    private $viewModelConverter;

    public function __construct(ViewModelConverter $viewModelConverter)
    {
        $this->viewModelConverter = $viewModelConverter;
    }

    /**
     * @param Block\Image $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $figure = $this->viewModelConverter->convert($object->getImage(), null, $context + ['supplementsCount' => count($object->getSupplements())]);

        if (empty($context['complete'])) {
            return $figure;
        }

        $assets = [$figure];

        foreach ($object->getSupplements() as $i => $supplement) {
            $assets[] = $this->viewModelConverter->convert($supplement, null, ['complete' => true, 'parentId' => $object->getImage()->getId(), 'ordinal' => $i + 1]);
        }

        return new AssetViewerInlineSet(...$assets);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Image && $object->getImage()->getTitle();
    }
}
