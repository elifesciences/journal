<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\QuoteConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class QuoteConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Quote::class;
    protected $viewModelClasses = [ViewModel\PullQuote::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new QuoteConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }
}
