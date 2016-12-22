<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class SectionConverter implements ViewModelConverter
{
    use CanConvertContent;

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
        $level = ($context['level'] ?? 1) + 1;

        return ViewModel\ArticleSection::basic(
            $object->getTitle(),
            $level,
            $this->patternRenderer->render(...$this->convertContent($object, $level)),
            $object->getId(),
            null,
            $context['isFirst'] ?? false
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Section;
    }

    protected function getViewModelConverter(): ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
