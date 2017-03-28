<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\CaptionedTableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class CaptionedTableConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Table::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class, ViewModel\CaptionedAsset::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedTableConverter(
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
        return !empty($block->getTitle());
    }
}
