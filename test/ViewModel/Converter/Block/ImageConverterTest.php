<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel;
use eLife\Journal\ViewModel\Converter\Block\ImageConverter;

final class ImageConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Image::class;
    protected $viewModelClasses = [ViewModel\CaptionlessImage::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ImageConverter();
    }

    /**
     * @param Block\Image $block
     */
    protected function includeBlock(Block $block) : bool
    {
        return !$block->getImage()->getTitle();
    }
}
