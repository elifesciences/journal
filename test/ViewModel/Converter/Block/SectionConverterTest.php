<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\SectionConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class SectionConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Section::class;
    protected $viewModelClasses = [ViewModel\ArticleSection::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter =  new SectionConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }
}
