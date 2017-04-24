<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\CaptionedTableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AdditionalAsset;
use eLife\Patterns\ViewModel\CaptionText;

final class CaptionedTableConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Table::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class, ViewModel\CaptionedAsset::class];
    protected $context = ['complete' => true];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedTableConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(AdditionalAsset::withoutDoi(
                'some-id',
                new CaptionText('Some asset'),
                null,
                'https://example.com/some-id'
            )));
    }

    /**
     * @param Block\Table $block
     */
    protected function includeBlock(Block $block) : bool
    {
        return !empty($block->getTitle());
    }
}
