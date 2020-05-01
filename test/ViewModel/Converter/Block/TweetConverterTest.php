<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\TweetConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class TweetConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Tweet::class;
    protected $viewModelClasses = [ViewModel\Tweet::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new TweetConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );
    }
}
