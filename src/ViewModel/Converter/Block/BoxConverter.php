<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\HasViewModelConverter;
use eLife\Journal\ViewModel\Converter\CreatesDoi;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class BoxConverter implements ViewModelConverter
{
    use CanConvertContent {
        convertTo as doConvert;
    }
    use CreatesDoi;
    use HasViewModelConverter;

    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Block\Box $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $level = $context['level'] ?? 1;

        return new ViewModel\Box(
            $object->getId(),
            $object->getLabel(),
            $object->getTitle(),
            $level,
            $this->createDoi($object),
            $this->patternRenderer->render(...$this->convertContent($object, $level + 1, $context))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Box;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
