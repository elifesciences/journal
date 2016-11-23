<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\HasPatternRenderer;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class BoxConverter implements ViewModelConverter
{
    use HasPatternRenderer;

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
        $context['level'] = ($context['level'] ?? 1) + 1;

        $content = array_map(function (Block $block) {
            return $this->viewModelConverter->convert($block);
        }, $object->getContent());

        return new ViewModel\Box($object->getId(), $object->getLabel(), $object->getTitle(), $context['level'], $object->getDoi() ? new ViewModel\Doi($object->getDoi()) : null, $this->patternRenderer->render(...$content));
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Box;
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }
}
