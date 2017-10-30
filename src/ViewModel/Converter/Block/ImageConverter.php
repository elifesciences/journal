<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ImageConverter implements ViewModelConverter
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
     * @param Block\Image $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $image = $object->getImage();

        $imageViewModel = $this->viewModelConverter->convert($image, null, ['width' => $object->isInline() ? 365 : 538]);

        return $this->createCaptionedAsset($imageViewModel, $object);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Image;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }
}
