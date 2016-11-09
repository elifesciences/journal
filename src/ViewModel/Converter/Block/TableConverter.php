<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Journal\ViewModel\Table;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class TableConverter implements ViewModelConverter
{
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
        if (empty($object->getTitle())) {
            return new Table(
                implode('', $object->getTables()),
                implode('', array_map(function (Block $block) use ($context) {
                    return $this->patternRenderer->render($this->viewModelConverter->convert($block, null, $context));
                }, $object->getFooter()))
            );
        }

        return new ViewModel\Table(...$object->getTables());
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Table;
    }
}
