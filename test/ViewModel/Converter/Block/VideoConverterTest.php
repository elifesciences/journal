<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\VideoConverter;
use eLife\Patterns\ViewModel;

final class VideoConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Video::class;
    protected $viewModelClasses = [ViewModel\Video::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new VideoConverter();
    }

    /**
     * @param Block\Video $block
     */
    protected function includeBlock(Block $block) : bool
    {
        return !$block->getTitle();
    }
}
