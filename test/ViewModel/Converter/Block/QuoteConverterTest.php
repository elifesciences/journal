<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\QuoteConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class QuoteConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Quote::class;
    protected $viewModelClasses = [ViewModel\PullQuote::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new QuoteConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->method('render')
            ->willReturn('...');
    }
}
