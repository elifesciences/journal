<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\TweetConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class TweetConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Tweet::class;
    protected $viewModelClasses = [ViewModel\Tweet::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new TweetConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );
    }
}
