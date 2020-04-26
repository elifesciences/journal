<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class YouTubeConverter implements ViewModelConverter
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
     * @param Block\YouTube $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $iframe = new ViewModel\IFrame('https://www.youtube.com/embed/'.$object->getId().'?rel=0', $object->getWidth(), $object->getHeight(), $object->getTitle());

        if (!$object->getTitle() && $object->getCaption()->isEmpty()) {
            return $iframe;
        }

        return $this->createCaptionedAsset($iframe, $object);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\YouTube;
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
