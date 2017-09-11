<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\ExcerptConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ExcerptConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Excerpt::class;
    protected $viewModelClasses = [ViewModel\Quote::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ExcerptConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }
}
