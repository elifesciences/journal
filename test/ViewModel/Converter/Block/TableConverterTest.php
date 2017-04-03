<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\TableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class TableConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Table::class;
    protected $viewModelClasses = [ViewModel\Table::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new TableConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }

    /**
     * @param Block\Table $block
     */
    protected function includeBlock(Block $block) : bool
    {
        return !$block->getTitle();
    }
}
