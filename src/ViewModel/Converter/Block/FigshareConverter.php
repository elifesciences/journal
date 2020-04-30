<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class FigshareConverter implements ViewModelConverter
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
     * @param Block\Figshare $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $iframe = new ViewModel\IFrame('https://widgets.figshare.com/articles/'.$object->getId().'/embed', $object->getWidth(), $object->getHeight(), $object->getTitle());

        if (!$object->getTitle() && $object->getCaption()->isEmpty()) {
            return $iframe;
        }

        return $this->createCaptionedAsset($iframe, $object);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Figshare;
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
