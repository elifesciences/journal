<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\AssetViewerInlineSet;
use eLife\Journal\ViewModel\Converter\Block\CaptionedImageConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class CaptionedImageConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Image::class;
    protected $viewModelClasses = [AssetViewerInlineSet::class, ViewModel\CaptionedAsset::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedImageConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );
        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new AssetViewerInlineSet()));
    }

    /**
     * @param Block\Image $block
     */
    protected function includeBlock(Block $block) : bool
    {
        return !empty($block->getImage()->getTitle());
    }
}
