<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\BoxConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class BoxConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Box::class;
    protected $viewModelClasses = [ViewModel\Box::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new BoxConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $patternRenderer
            ->method('render')
            ->willReturn('...');
    }
}
