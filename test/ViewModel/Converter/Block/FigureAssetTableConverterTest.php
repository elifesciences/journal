<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\Converter\Block\FigureAssetTableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Traversable;

final class FigureAssetTableConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Figure::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new FigureAssetTableConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }

    protected function explodeBlock(Block $block) : Traversable
    {
        yield from $block->getAssets()->filter(Callback::methodIsInstanceOf('getAsset', Block\Table::class));
    }
}
