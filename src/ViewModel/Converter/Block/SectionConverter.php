<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class SectionConverter implements ViewModelConverter
{
    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Block\Section $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $context['level'] = ($context['level'] ?? 1) + 1;

        return ViewModel\ArticleSection::basic(
            $object->getTitle(),
            $context['level'],
            implode('', array_map(function (Block $block) use ($context) {
                return $this->patternRenderer->render($this->viewModelConverter->convert($block, null, $context));
            }, $object->getContent())),
            $object->getId(),
            null,
            $context['isFirst'] ?? false
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Section;
    }
}
