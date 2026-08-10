<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\TableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class TableConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Table::class;
    protected $viewModelClasses = [ViewModel\Table::class, ViewModel\CaptionedAsset::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new TableConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $patternRenderer
            ->method('render')
            ->willReturn('...');
    }
}
