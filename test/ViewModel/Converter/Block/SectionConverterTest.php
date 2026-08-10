<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\SectionConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class SectionConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Section::class;
    protected $viewModelClasses = [ViewModel\ArticleSection::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SectionConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->method('render')
            ->willReturn('...');
    }
}
