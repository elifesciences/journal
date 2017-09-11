<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class QuoteConverter implements ViewModelConverter
{
    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Block\Quote $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\PullQuote(
            implode('', array_map(function (Block $block) use ($context) {
                return $this->patternRenderer->render($this->viewModelConverter->convert($block, null, $context));
            }, $object->getText())),
            $object->getCite()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Quote;
    }
}
